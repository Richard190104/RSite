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
 *
 * Credentials are read from the environment only — never hardcode them
 * here, this file is committed to git.
 */
import { Client } from 'basic-ftp';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { readdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join, relative, sep } from 'node:path';

const ROOT = new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');

// Tracks the composer.lock hash from the last deploy that uploaded vendor/,
// so a deploy with no dependency changes can skip re-uploading it — vendor/
// is large and almost never changes between deploys. Gitignored: this is a
// local marker of what THIS machine last pushed, not project state.
const VENDOR_MARKER = join(ROOT, '.vendor-deployed-hash');

// Same exclusions as the GitHub Actions workflow (.github/workflows/deploy.yml)
// — config/app_local.php and config/.env hold live DB credentials and are
// gitignored for the same reason; run-migrations.php is uploaded by hand,
// only when actually running a migration, then deleted from the server.
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

function run(command, args) {
    console.log(`\n$ ${command} ${args.join(' ')}`);
    execFileSync(command, args, { cwd: ROOT, stdio: 'inherit', shell: true });
}

function* walk(dir, { skipVendor }) {
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
        const full = join(dir, entry.name);
        const rel = relative(ROOT, full);
        if (isExcluded(rel)) {
            continue;
        }
        if (skipVendor && rel === 'vendor') {
            continue;
        }
        if (entry.isDirectory()) {
            yield* walk(full, { skipVendor });
        } else {
            yield { full, rel };
        }
    }
}

function lockHash() {
    return createHash('sha256').update(readFileSync(join(ROOT, 'composer.lock'))).digest('hex');
}

async function main() {
    const { FTP_SERVER, FTP_USERNAME, FTP_PASSWORD } = process.env;
    if (!FTP_SERVER || !FTP_USERNAME || !FTP_PASSWORD) {
        console.error('Missing FTP_SERVER / FTP_USERNAME / FTP_PASSWORD in the environment.');
        process.exit(1);
    }

    console.log('=== Building ===');
    run('composer', ['install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress']);
    run('npm', ['run', 'sass:build']);

    const currentHash = lockHash();
    let previousHash = null;
    try {
        previousHash = readFileSync(VENDOR_MARKER, 'utf8').trim();
    } catch {
        // No marker yet — first deploy from this machine, so vendor/ must go up.
    }
    const skipVendor = currentHash === previousHash;
    const forceVendor = process.argv.includes('--force-vendor');

    if (skipVendor && !forceVendor) {
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
        for (const { full, rel } of walk(ROOT, { skipVendor: skipVendor && !forceVendor })) {
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
    } finally {
        client.close();
    }
}

main().catch((error) => {
    console.error('\nDeploy failed:', error.message);
    process.exit(1);
});