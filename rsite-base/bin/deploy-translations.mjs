#!/usr/bin/env node
/**
 * Lightweight deploy: uploads ONLY resources/locales/ to production — no
 * composer install, no sass build, no vendor/. For when only translations
 * changed and a full deploy.mjs run would be needless overhead.
 *
 * Plain FTP, not FTPS — see deploy.mjs's header comment for why.
 *
 * Usage:
 *   FTP_SERVER=... FTP_USERNAME=... FTP_PASSWORD=... node bin/deploy-translations.mjs
 *
 * Deploys ONLY what's committed on `main` — never the working tree. main's
 * HEAD is exported with `git archive` into a clean throwaway directory
 * first, so uncommitted edits (staged or not, on any branch) never reach
 * the server. See deploy.mjs for the full rationale (this duplicates that
 * logic on purpose, rather than sharing a module — kept deliberately
 * independent so a change to one script can't silently affect the other).
 *
 * The translation cache on the server (Cake's _cake_core_ / file cache)
 * isn't cleared by this — CakePHP checks the .po file's mtime against its
 * own cache, so a re-upload with a fresh mtime is picked up automatically
 * on the next request. If it somehow isn't, log into the host's file
 * manager and clear tmp/cache/persistent/ by hand.
 *
 * Credentials are read from the environment only — never hardcode them
 * here, this file is committed to git.
 */
import { Client } from 'basic-ftp';
import { execFileSync } from 'node:child_process';
import { existsSync, mkdirSync, readdirSync, rmSync } from 'node:fs';
import { join, relative, sep } from 'node:path';
import * as tar from 'tar';

const ROOT = new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const REPO_ROOT = join(ROOT, '..');

// Separate from deploy.mjs's .deploy-build — this script and a full deploy
// could otherwise clobber each other's export if run back to back.
const SOURCE_DIR = join(ROOT, '.deploy-translations-build');

function run(command, args, cwd = ROOT) {
    console.log(`\n$ ${command} ${args.join(' ')}`);
    execFileSync(command, args, { cwd, stdio: 'inherit', shell: true });
}

function capture(command, args, cwd = ROOT) {
    return execFileSync(command, args, { cwd, encoding: 'utf8' }).trim();
}

// Same fast-forward-only sync as deploy.mjs — see its header comment for
// the full rationale. Refuses rather than guessing if history has diverged.
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

// Exports only resources/locales/ from main's HEAD — same git-archive
// approach as deploy.mjs (immune to uncommitted edits and wrong-branch
// checkouts), just scoped to one subtree instead of the whole app.
async function exportLocalesToSourceDir() {
    syncMainWithOrigin();

    if (existsSync(SOURCE_DIR)) {
        rmSync(SOURCE_DIR, { recursive: true, force: true });
    }
    mkdirSync(SOURCE_DIR, { recursive: true });

    const localesDir = relative(REPO_ROOT, join(ROOT, 'resources', 'locales')).split(sep).join('/');
    const tarPath = join(ROOT, '.deploy-translations-archive.tar');
    execFileSync('git', ['archive', '--format=tar', '-o', tarPath, 'main', '--', localesDir], { cwd: REPO_ROOT });

    await tar.x({
        file: tarPath,
        cwd: SOURCE_DIR,
        strip: localesDir.split('/').length,
    });
    rmSync(tarPath);
}

function* walkDir(dir, base) {
    for (const entry of readdirSync(dir, { withFileTypes: true })) {
        const full = join(dir, entry.name);
        const rel = relative(base, full).split(sep).join('/');
        if (entry.isDirectory()) {
            yield* walkDir(full, base);
        } else {
            yield { full, rel };
        }
    }
}

async function main() {
    const { FTP_SERVER, FTP_USERNAME, FTP_PASSWORD } = process.env;
    if (!FTP_SERVER || !FTP_USERNAME || !FTP_PASSWORD) {
        console.error('Missing FTP_SERVER / FTP_USERNAME / FTP_PASSWORD in the environment.');
        process.exit(1);
    }

    console.log('=== Exporting resources/locales/ from main ===');
    await exportLocalesToSourceDir();

    console.log('\n=== Uploading over FTP ===');
    const client = new Client();
    client.ftp.verbose = false;

    try {
        await client.access({
            host: FTP_SERVER,
            user: FTP_USERNAME,
            password: FTP_PASSWORD,
            secure: false,
        });

        let uploaded = 0;
        for (const { full, rel } of walkDir(SOURCE_DIR, SOURCE_DIR)) {
            const remotePath = '/htdocs/resources/locales/' + rel;
            await client.ensureDir('/htdocs/resources/locales/' + rel.split('/').slice(0, -1).join('/'));
            await client.uploadFrom(full, remotePath);
            uploaded += 1;
            console.log(`  uploaded resources/locales/${rel}`);
        }

        console.log(`\nDone. ${uploaded} file(s) uploaded.`);
    } finally {
        client.close();
    }
}

main().catch((error) => {
    console.error('\nDeploy failed:', error.message);
    process.exit(1);
});
