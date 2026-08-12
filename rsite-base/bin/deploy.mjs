#!/usr/bin/env node
/**
 * Manual deploy: builds the app locally and uploads it over FTP to
 * production. Stand-in for the GitHub Actions workflow while that's
 * blocked on a billing issue — same steps, run from this machine instead.
 *
 * Plain FTP, not FTPS: this host's data-connection TLS fails outright
 * (SSL alert 50 / decode error) even though FileZilla's "explicit TLS if
 * available" mode connects fine — it's silently falling back to plain FTP
 * too, just without saying so in the UI.
 *
 * Usage:
 *   FTP_SERVER=... FTP_USERNAME=... FTP_PASSWORD=... node bin/deploy.mjs
 *   FTP_SERVER=... FTP_USERNAME=... FTP_PASSWORD=... MIGRATION_TOKEN=... node bin/deploy.mjs --migrate https://rsite.great-site.net
 *
 * --migrate <site-url> additionally uploads webroot/run-migrations.php
 * (with its token placeholder swapped for MIGRATION_TOKEN), hits it once
 * over HTTPS to run pending migrations, then deletes it from the server —
 * opt-in, since a routine deploy shouldn't touch the database without
 * being asked to.
 *
 * Credentials are read from the environment only — never hardcode them
 * here, this file is committed to git.
 */
import { Client } from 'basic-ftp';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { cpSync, existsSync, mkdirSync, readdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { join, relative, sep } from 'node:path';
import https from 'node:https';
import { Readable } from 'node:stream';

const ROOT = new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');

// composer install --no-dev is run in this throwaway copy, never in ROOT's
// own vendor/ — running it there would strip dev-only packages (DebugKit,
// PHPUnit...) from the vendor/ this machine also uses for local
// development, breaking `bin/cake server` until you re-run `composer
// install` yourself. Deleted and recreated fresh on every deploy.
const BUILD_DIR = join(ROOT, '.deploy-build');

// Tracks the composer.lock hash from the last deploy that uploaded vendor/,
// so a deploy with no dependency changes can skip re-uploading it — vendor/
// is large and almost never changes between deploys. Gitignored: this is a
// local marker of what THIS machine last pushed, not project state.
const VENDOR_MARKER = join(ROOT, '.vendor-deployed-hash');

// Same exclusions as the GitHub Actions workflow (.github/workflows/deploy.yml)
// — config/app_local.php and config/.env hold live DB credentials and are
// gitignored for the same reason. run-migrations.php is excluded from this
// regular upload on purpose: it only ever goes up as part of runMigrations()
// below (--migrate), which deletes it again right after — it must never be
// left sitting on the server between deploys.
const EXCLUDE = [
    /^\.git($|[\\/])/,
    /^node_modules($|[\\/])/,
    /^config[\\/]app_local\.php$/,
    /^config[\\/]\.env$/,
    /^logs($|[\\/])/,
    /^tmp($|[\\/])/,
    /^webroot[\\/]run-migrations\.php$/,
];

function isExcluded(relPath) {
    const normalized = relPath.split(sep).join('/');
    return EXCLUDE.some((pattern) => pattern.test(normalized));
}

function run(command, args, cwd = ROOT) {
    console.log(`\n$ ${command} ${args.join(' ')}`);
    execFileSync(command, args, { cwd, stdio: 'inherit', shell: true });
}

// Yields every {full, rel} under `dir`, with `rel` always relative to
// `base` (so vendor/ from BUILD_DIR still reports paths as "vendor/...",
// matching where it belongs on the server, not where it sits on disk).
function* walkDir(dir, base, { exclude = () => false } = {}) {
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
        const full = join(dir, entry.name);
        const rel = relative(base, full).split(sep).join('/');
        if (exclude(rel)) {
            continue;
        }
        if (entry.isDirectory()) {
            yield* walkDir(full, base, { exclude });
        } else {
            yield { full, rel };
        }
    }
}

// Everything from ROOT (excluding vendor/, which is never uploaded from
// here — see BUILD_DIR above) plus, unless skipVendor, vendor/ from
// vendorDir (BUILD_DIR/vendor, built with --no-dev).
function* walk(vendorDir, { skipVendor }) {
    yield* walkDir(ROOT, ROOT, {
        exclude: (rel) => isExcluded(rel) || rel === 'vendor',
    });

    if (!skipVendor) {
        for (const { full, rel } of walkDir(vendorDir, vendorDir)) {
            yield { full, rel: 'vendor/' + rel };
        }
    }
}

function lockHash() {
    return createHash('sha256').update(readFileSync(join(ROOT, 'composer.lock'))).digest('hex');
}

// Reads webroot/run-migrations.php and substitutes its token placeholder
// with the real value — in memory only. The file on disk (and in git) always
// keeps the placeholder; the real token lives only in the MIGRATION_TOKEN
// environment variable, same as the FTP password.
function migrationScriptWithToken(token) {
    const source = readFileSync(join(ROOT, 'webroot', 'run-migrations.php'), 'utf8');
    if (!source.includes('__MIGRATION_TOKEN__')) {
        throw new Error("Couldn't find the __MIGRATION_TOKEN__ placeholder in webroot/run-migrations.php");
    }
    return source.replace('__MIGRATION_TOKEN__', token);
}

function httpsGet(url) {
    return new Promise((resolve, reject) => {
        https.get(url, (res) => {
            let body = '';
            res.on('data', (chunk) => (body += chunk));
            res.on('end', () => resolve({ status: res.statusCode, body }));
        }).on('error', reject);
    });
}

// Uploads run-migrations.php, hits it once to run pending migrations, then
// deletes it from the server — so the window where that URL is live (with a
// working token, on a host with no login) is only as long as this one
// request, not however long someone remembers to delete it by hand.
async function runMigrations(client, migrationSiteUrl, token) {
    const remotePath = '/htdocs/webroot/run-migrations.php';

    console.log('\n=== Running migrations ===');
    await client.uploadFrom(Readable.from([migrationScriptWithToken(token)]), remotePath);

    const url = `${migrationSiteUrl.replace(/\/$/, '')}/run-migrations.php?token=${token}`;
    console.log(`GET ${url}`);
    const { status, body } = await httpsGet(url);
    console.log(body);

    await client.remove(remotePath);
    console.log('run-migrations.php removed from the server.');

    if (status !== 200) {
        throw new Error(`Migration request returned HTTP ${status}`);
    }
}

async function main() {
    const { FTP_SERVER, FTP_USERNAME, FTP_PASSWORD } = process.env;
    if (!FTP_SERVER || !FTP_USERNAME || !FTP_PASSWORD) {
        console.error('Missing FTP_SERVER / FTP_USERNAME / FTP_PASSWORD in the environment.');
        process.exit(1);
    }

    // Migrations are opt-in per run: --migrate <https://site> uploads
    // run-migrations.php, hits it once, and deletes it — but only when
    // asked, since running migrations isn't something a routine CSS/text
    // deploy should trigger by accident.
    const migrateFlagIndex = process.argv.indexOf('--migrate');
    const migrationSiteUrl = migrateFlagIndex !== -1 ? process.argv[migrateFlagIndex + 1] : null;
    if (migrateFlagIndex !== -1 && !migrationSiteUrl) {
        console.error('--migrate requires a URL, e.g. --migrate https://rsite.great-site.net');
        process.exit(1);
    }
    if (migrationSiteUrl && !process.env.MIGRATION_TOKEN) {
        console.error('--migrate also requires MIGRATION_TOKEN in the environment (must match the value in webroot/run-migrations.php once deployed).');
        process.exit(1);
    }

    const currentHash = lockHash();
    let previousHash = null;
    try {
        previousHash = readFileSync(VENDOR_MARKER, 'utf8').trim();
    } catch {
        // No marker yet — first deploy from this machine, so vendor/ must go up.
    }
    const skipVendor = currentHash === previousHash;
    const forceVendor = process.argv.includes('--force-vendor');
    const buildVendor = !skipVendor || forceVendor;

    console.log('=== Building ===');
    run('npm', ['run', 'sass:build']);

    if (buildVendor) {
        // Built in a throwaway copy, never in ROOT's own vendor/ — see
        // BUILD_DIR's definition above for why.
        if (existsSync(BUILD_DIR)) {
            rmSync(BUILD_DIR, { recursive: true, force: true });
        }
        mkdirSync(BUILD_DIR, { recursive: true });
        cpSync(join(ROOT, 'composer.json'), join(BUILD_DIR, 'composer.json'));
        cpSync(join(ROOT, 'composer.lock'), join(BUILD_DIR, 'composer.lock'));
        // --no-scripts: composer.json's post-install-cmd calls
        // App\Console\Installer::postInstall, which BUILD_DIR (composer.json
        // + composer.lock only, no src/) can't resolve. That hook is
        // first-scaffold-only logic (copying .env.example, generating a
        // salt) — nothing a deploy build needs.
        run('composer', ['install', '--no-dev', '--no-scripts', '--optimize-autoloader', '--no-interaction', '--no-progress'], BUILD_DIR);
    } else {
        console.log('\ncomposer.lock unchanged since last deploy — skipping vendor/ upload.');
        console.log('(pass --force-vendor to upload it anyway, e.g. if the server copy was ever wiped)');
    }

    console.log('\n=== Uploading over FTP ===');
    const client = new Client();
    client.ftp.verbose = false;

    try {
        // Plain FTP, not FTPS: this host's TLS on the data connection fails
        // with a decode error (SSL alert 50) even though the control
        // connection negotiates fine — FileZilla silently falls back to
        // plain FTP here too ("if available" mode), it just doesn't surface
        // that in the UI. Matches what's actually working, not what's ideal.
        await client.access({
            host: FTP_SERVER,
            user: FTP_USERNAME,
            password: FTP_PASSWORD,
            secure: false,
        });

        let uploaded = 0;
        for (const { full, rel } of walk(join(BUILD_DIR, 'vendor'), { skipVendor: !buildVendor })) {
            const remotePath = '/htdocs/' + rel.split(sep).join('/');
            await client.ensureDir('/htdocs/' + rel.split(sep).slice(0, -1).join('/') || '/htdocs');
            await client.uploadFrom(full, remotePath);
            uploaded += 1;
            if (uploaded % 50 === 0) {
                console.log(`  ${uploaded} files uploaded...`);
            }
        }

        writeFileSync(VENDOR_MARKER, currentHash);
        console.log(`\nDone. ${uploaded} files uploaded.`);

        if (migrationSiteUrl) {
            await runMigrations(client, migrationSiteUrl, process.env.MIGRATION_TOKEN);
        }
    } finally {
        client.close();
    }
}

main().catch((error) => {
    console.error('\nDeploy failed:', error.message);
    process.exit(1);
});