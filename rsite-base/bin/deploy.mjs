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
 *   FTP_SERVER=... FTP_USERNAME=... FTP_PASSWORD=... MIGRATION_TOKEN=... CACHE_TOKEN=... \
 *     node bin/deploy.mjs
 *
 * Every run also runs pending migrations and clears the cache, right after
 * the FTP upload — see runOneOffScriptAuto() for how. This host serves a
 * one-time JS/cookie anti-bot challenge to any client before letting a
 * request through, which a plain HTTP request would normally just bounce
 * off (only a real browser, having already run the challenge's JS, gets
 * through) — solveAntiBotChallenge() below solves it without a browser: the
 * challenge is a fixed AES-128-CBC decrypt (this host's own /aes.js, a
 * bundled slowAES implementation) of server-supplied values, verified
 * byte-for-byte against Node's built-in crypto module to decrypt
 * identically. If the host ever changes that challenge, this will start
 * failing loudly (a thrown error, not a silent bad deploy) rather than
 * quietly stop working.
 *
 * Deploys ONLY what's committed on `main` — never the working tree. main's
 * HEAD is exported with `git archive` into a clean throwaway directory
 * first, so uncommitted edits (staged or not, on any branch) never reach
 * the server, and neither does anything from a feature branch that hasn't
 * been merged yet.
 *
 * Credentials are read from the environment only — never hardcode them
 * here, this file is committed to git.
 */
import { Client } from 'basic-ftp';
import { execFileSync } from 'node:child_process';
import { createDecipheriv, createHash } from 'node:crypto';
import { existsSync, mkdirSync, readdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { join, relative, sep } from 'node:path';
import { Readable } from 'node:stream';
import * as tar from 'tar';

// ROOT is the working copy this script lives in — used only to locate the
// git repo and to persist local state (VENDOR_MARKER). Everything that
// actually gets uploaded is read from SOURCE_DIR instead (see below).
const ROOT = new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const REPO_ROOT = join(ROOT, '..');

// Fresh export of main's HEAD (via `git archive`) plus a --no-dev vendor/
// built inside it — recreated from scratch on every deploy, so it can
// never end up containing a leftover uncommitted edit from a previous run.
// This is what actually gets uploaded; ROOT's own working tree never is.
const SOURCE_DIR = join(ROOT, '.deploy-build');

// Tracks the composer.lock hash from the last deploy that uploaded vendor/,
// so a deploy with no dependency changes can skip re-uploading it — vendor/
// is large and almost never changes between deploys.
//
// Tracked in git, NOT gitignored — with two people deploying from
// different machines, a local-only marker would just be wrong half the
// time (whichever machine didn't do the last vendor upload has a stale or
// missing hash, and would re-upload vendor/ unnecessarily on its next
// deploy). Committing it means main always reflects what's actually on
// the server. This script only ever writes the file locally after a
// successful upload — it does NOT commit or push it for you. Whoever
// deploys after a composer.lock change (i.e. after this script decides
// needsVendor) is responsible for committing and pushing the updated
// .vendor-deployed-hash afterwards, same as any other change to main.
const VENDOR_MARKER = join(ROOT, '.vendor-deployed-hash');

// config/app_local.php and config/.env hold live DB credentials and are
// gitignored, so git archive never includes them regardless — listed here
// too as a second line of defense. run-migrations.php and clear-cache.php
// are excluded from this regular upload on purpose: they only ever go up
// as part of runOneOffScriptAuto() below, which deletes them again right
// after a successful run — neither must ever be left sitting on the
// server between deploys. .vendor-deployed-hash is tracked in git (see
// above) so it travels with main between machines, but it's a deploy-tool
// bookkeeping file, not something the server needs — excluded here so it
// never actually gets uploaded.
const EXCLUDE = [
    /^config[\\/]app_local\.php$/,
    /^config[\\/]\.env$/,
    /^webroot[\\/]run-migrations\.php$/,
    /^webroot[\\/]clear-cache\.php$/,
    /^\.vendor-deployed-hash$/,
];

function isExcluded(relPath) {
    const normalized = relPath.split(sep).join('/');
    return EXCLUDE.some((pattern) => pattern.test(normalized));
}

function run(command, args, cwd = ROOT) {
    console.log(`\n$ ${command} ${args.join(' ')}`);
    execFileSync(command, args, { cwd, stdio: 'inherit', shell: true });
}

function capture(command, args, cwd = ROOT) {
    return execFileSync(command, args, { cwd, encoding: 'utf8' }).trim();
}

// Fetches origin and fast-forwards the local `main` ref to match
// origin/main — without this, a local `main` that's behind (you forgot to
// pull) would deploy stale code and never say so. Fast-forward only: if
// local main has commits origin/main doesn't (diverged history), this
// refuses rather than guessing which side is "right". Uses a ref update
// (git fetch origin main:main), not a checkout — works from any branch,
// never touches the working tree.
function syncMainWithOrigin() {
    console.log('\nFetching origin/main...');
    run('git', ['fetch', 'origin', 'main'], REPO_ROOT);

    const localMain = capture('git', ['rev-parse', 'main'], REPO_ROOT);
    const remoteMain = capture('git', ['rev-parse', 'origin/main'], REPO_ROOT);
    if (localMain === remoteMain) {
        return;
    }

    const mergeBase = capture('git', ['merge-base', 'main', 'origin/main'], REPO_ROOT);
    if (mergeBase !== localMain) {
        console.error(
            `Local 'main' (${localMain.slice(0, 7)}) has commits origin/main (${remoteMain.slice(0, 7)}) doesn't — ` +
            "these have diverged. Resolve manually (rebase/merge/push) before deploying."
        );
        process.exit(1);
    }

    console.log(`Fast-forwarding local 'main': ${localMain.slice(0, 7)} -> ${remoteMain.slice(0, 7)}`);
    run('git', ['fetch', 'origin', 'main:main'], REPO_ROOT);
}

// Exports main's HEAD — as actually committed, ignoring whatever's in the
// working tree or index right now — into SOURCE_DIR. `git archive` reads
// straight from the object database, so this is immune to uncommitted
// edits, staged-but-uncommitted changes, and being on the wrong branch, all
// at once; there's no "clean working tree" precondition to get wrong.
//
// Goes through the `tar` npm package rather than piping to a shell `tar`
// binary — cross-platform without relying on the parent shell's own
// path/quoting rules for the target directory (Windows paths with
// backslashes through Git Bash's tar broke this the naive way).
async function exportMainToSourceDir() {
    syncMainWithOrigin();

    const mainRef = capture('git', ['rev-parse', 'main'], REPO_ROOT);
    const currentRef = capture('git', ['rev-parse', 'HEAD'], REPO_ROOT);
    if (mainRef !== currentRef) {
        console.log(`\nDeploying main@${mainRef.slice(0, 7)} (current checkout is at ${currentRef.slice(0, 7)}) — working tree is not touched.`);
    }

    if (existsSync(SOURCE_DIR)) {
        rmSync(SOURCE_DIR, { recursive: true, force: true });
    }
    mkdirSync(SOURCE_DIR, { recursive: true });

    const archiveDir = relative(REPO_ROOT, ROOT).split(sep).join('/');
    const tarPath = join(ROOT, '.deploy-archive.tar');
    execFileSync('git', ['archive', '--format=tar', '-o', tarPath, 'main', '--', archiveDir], { cwd: REPO_ROOT });

    await tar.x({
        file: tarPath,
        cwd: SOURCE_DIR,
        strip: archiveDir.split('/').length,
    });
    rmSync(tarPath);
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

// Everything from SOURCE_DIR (main's exported HEAD; excludes its own
// vendor/, which doesn't exist until buildVendor() runs, plus whatever's
// in EXCLUDE) plus, unless skipVendor, vendor/ itself.
function* walk({ skipVendor }) {
    yield* walkDir(SOURCE_DIR, SOURCE_DIR, {
        exclude: (rel) => isExcluded(rel) || rel === 'vendor',
    });

    if (!skipVendor) {
        yield* walkDir(join(SOURCE_DIR, 'vendor'), SOURCE_DIR);
    }
}

function lockHash() {
    return createHash('sha256').update(readFileSync(join(SOURCE_DIR, 'composer.lock'))).digest('hex');
}

// Reads a one-off script from SOURCE_DIR (so this always reflects what
// main actually has) and substitutes its token placeholder with the real
// value — in memory only. The file on disk (and in git) always keeps the
// placeholder; the real token lives only in an environment variable, same
// as the FTP password.
function oneOffScriptWithToken(filename, placeholder, token) {
    const source = readFileSync(join(SOURCE_DIR, 'webroot', filename), 'utf8');
    if (!source.includes(placeholder)) {
        throw new Error(`Couldn't find the ${placeholder} placeholder in webroot/${filename}`);
    }
    return source.replace(placeholder, token);
}

// composer install --no-dev, run directly inside SOURCE_DIR (main's
// exported HEAD) — never in ROOT's own working-tree vendor/, so this
// machine's local dev vendor/ (DebugKit, PHPUnit...) is never touched.
//
// --no-scripts is required here, not just an optimization: composer.json's
// post-install-cmd runs Installer::postInstall, which — finding no
// config/app_local.php in this fresh export (git archive never includes
// it) — would create one from the placeholder template, with a dummy DB
// password and salt. That file would then get uploaded and silently
// overwrite the real production config.
function buildVendor() {
    run('composer', ['install', '--no-dev', '--no-scripts', '--optimize-autoloader', '--no-interaction', '--no-progress'], SOURCE_DIR);
}

// GETs `url`; if the response is the host's anti-bot challenge page (an
// inline <script> computing a cookie value via AES-128-CBC and redirecting
// to the same URL with &i=1), solves it without a browser and re-requests
// — otherwise returns the response as-is. Handles at most one challenge
// hop (this host has never been seen chaining more than one), and throws
// with the response body included if the challenge shape doesn't match
// what solveAntiBotChallenge() expects, so a host-side change to the
// challenge fails loudly here rather than silently returning a blocked
// page to the caller.
async function fetchThroughAntiBotChallenge(url) {
    const first = await fetch(url, { headers: { 'User-Agent': 'Mozilla/5.0 (compatible; deploy-script)' } });
    const firstBody = await first.text();

    const challenge = firstBody.match(
        /toNumbers\("([a-f0-9]+)"\),b=toNumbers\("([a-f0-9]+)"\),c=toNumbers\("([a-f0-9]+)"\).*?location\.href="([^"]+)"/s,
    );
    if (!challenge) {
        return { status: first.status, body: firstBody };
    }

    const [, keyHex, ivHex, ciphertextHex, redirectUrl] = challenge;
    // The challenge's own AES implementation (this host's /aes.js, a
    // bundled slowAES) and Node's built-in aes-128-cbc decrypt with
    // padding turned off were verified to produce byte-identical output
    // for the same key/iv/ciphertext — no need to carry a copy of that
    // third-party JS here.
    const decipher = createDecipheriv('aes-128-cbc', Buffer.from(keyHex, 'hex'), Buffer.from(ivHex, 'hex'));
    decipher.setAutoPadding(false);
    const cookieValue = Buffer.concat([decipher.update(Buffer.from(ciphertextHex, 'hex')), decipher.final()]).toString('hex');

    const second = await fetch(redirectUrl, {
        headers: {
            'User-Agent': 'Mozilla/5.0 (compatible; deploy-script)',
            Cookie: `__test=${cookieValue}`,
        },
    });
    return { status: second.status, body: await second.text() };
}

// Uploads a one-off script (run-migrations.php or clear-cache.php), hits
// it through the anti-bot challenge, checks the output for successMarker,
// and only deletes the script from the server if that marker was found —
// a script that failed (or whose output didn't match what was expected)
// is deliberately left in place rather than cleaned up, so it's still
// there to inspect instead of silently disappearing after a bad run.
async function runOneOffScriptAuto(client, { label, filename, placeholder, token, siteUrl, successMarker }) {
    const remotePath = `/htdocs/webroot/${filename}`;

    console.log(`\n=== Running ${label} ===`);
    await client.uploadFrom(Readable.from([oneOffScriptWithToken(filename, placeholder, token)]), remotePath);

    const url = `${siteUrl.replace(/\/$/, '')}/${filename}?token=${token}`;
    const { status, body } = await fetchThroughAntiBotChallenge(url);

    console.log(body.trim());

    if (status !== 200 || !body.includes(successMarker)) {
        throw new Error(
            `${label} did not report success (HTTP ${status}, expected "${successMarker}" in the output) — ` +
            `leaving ${filename} on the server for inspection. Check the output above, fix the issue, then delete ` +
            `webroot/${filename} from the server by hand once resolved.`,
        );
    }

    await client.remove(remotePath);
    console.log(`\n${filename} removed from the server.`);
}

// The production site — used only to build the run-migrations.php /
// clear-cache.php URLs. Overridable via SITE_URL in case this ever needs
// to point elsewhere (a staging copy, a domain change).
const SITE_URL = process.env.SITE_URL || 'https://rsite.great-site.net';

async function main() {
    const { FTP_SERVER, FTP_USERNAME, FTP_PASSWORD, MIGRATION_TOKEN, CACHE_TOKEN } = process.env;
    if (!FTP_SERVER || !FTP_USERNAME || !FTP_PASSWORD) {
        console.error('Missing FTP_SERVER / FTP_USERNAME / FTP_PASSWORD in the environment.');
        process.exit(1);
    }
    if (!MIGRATION_TOKEN) {
        console.error('Missing MIGRATION_TOKEN in the environment (must match the value in webroot/run-migrations.php once deployed).');
        process.exit(1);
    }
    if (!CACHE_TOKEN) {
        console.error('Missing CACHE_TOKEN in the environment (must match the value in webroot/clear-cache.php once deployed).');
        process.exit(1);
    }

    console.log('=== Exporting main ===');
    await exportMainToSourceDir();

    const currentHash = lockHash();
    let previousHash = null;
    try {
        previousHash = readFileSync(VENDOR_MARKER, 'utf8').trim();
    } catch {
        // No marker yet — first deploy from this machine, so vendor/ must go up.
    }
    const skipVendor = currentHash === previousHash;
    const forceVendor = process.argv.includes('--force-vendor');
    const needsVendor = !skipVendor || forceVendor;

    console.log('\n=== Building ===');
    // sass:build writes into SOURCE_DIR's webroot/css, using ROOT's own
    // node_modules (sass is only a devDependency, so it's never installed
    // into SOURCE_DIR, which is deliberately --no-dev).
    run('npx', ['sass', `${relative(ROOT, join(SOURCE_DIR, 'resources', 'scss'))}:${relative(ROOT, join(SOURCE_DIR, 'webroot', 'css'))}`, '--style=compressed', '--no-source-map']);

    if (needsVendor) {
        buildVendor();
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
        for (const { full, rel } of walk({ skipVendor: !needsVendor })) {
            const parts = rel.split(sep).join('/').split('/');
            const filename = parts.pop();
            // ensureDir() changes the connection's cwd to the given
            // directory as a side effect — uploadFrom() is then given just
            // the filename, relative to that cwd, rather than a full
            // absolute path. This host's FTP server was returning
            // "553 Can't open that file" on the absolute-path form; some
            // shared-hosting FTP implementations only resolve STOR
            // correctly relative to cwd.
            await client.ensureDir('/htdocs/' + parts.join('/'));
            try {
                await client.uploadFrom(full, filename);
            } catch (error) {
                error.message = `Failed uploading ${rel}: ${error.message}`;
                throw error;
            }
            uploaded += 1;
            if (uploaded % 50 === 0) {
                console.log(`  ${uploaded} files uploaded...`);
            }
        }

        writeFileSync(VENDOR_MARKER, currentHash);
        console.log(`\nDone. ${uploaded} files uploaded.`);

        await runOneOffScriptAuto(client, {
            label: 'migrations',
            filename: 'run-migrations.php',
            placeholder: '__MIGRATION_TOKEN__',
            token: MIGRATION_TOKEN,
            siteUrl: SITE_URL,
            successMarker: 'OK: migrate() succeeded.',
        });

        await runOneOffScriptAuto(client, {
            label: 'cache clear',
            filename: 'clear-cache.php',
            placeholder: '__CACHE_TOKEN__',
            token: CACHE_TOKEN,
            siteUrl: SITE_URL,
            successMarker: '=== Clearing all ===',
        });
    } finally {
        client.close();
    }
}

main().catch((error) => {
    console.error('\nDeploy failed:', error.message);
    process.exit(1);
});