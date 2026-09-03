#!/usr/bin/env node
/**
 * One-shot translation workflow: extract new __() strings, translate them
 * with Gemini, let you review/edit the result, then commit + push + deploy
 * them — a single run instead of the separate translate-locales.mjs /
 * manual commit / deploy-translations.mjs steps.
 *
 * Flow:
 *   1. Check out `main` (only — never your own branch/working tree) into a
 *      throwaway git worktree, and run bin/cake i18n extract THERE. This
 *      means only __() strings already on `main` get translated — work in
 *      progress on a feature branch is picked up once it's merged, not
 *      before. (Extracting from your own checkout instead would translate
 *      not-yet-merged strings early, and could commit them onto `main`
 *      ahead of the code that uses them — see git history for why this
 *      changed.)
 *   2. Translate every string missing from sk_SK/default.po there (same
 *      logic as translate-locales.mjs — only ADDS new msgid/msgstr pairs,
 *      never touches existing ones).
 *   3. Print what changed and pause. Edit the worktree's
 *      resources/locales/sk_SK/default.po by hand right now (path printed
 *      at the prompt) if any translation needs fixing — the file on disk
 *      there is what gets committed and deployed, not what's printed above.
 *   4. On Enter: git add + commit + push resources/locales/ from the
 *      worktree to `main` (skipped if there's nothing to commit — e.g. you
 *      only hand-edited something already committed, or step 2 found
 *      nothing new). Your own checkout/branch is never touched.
 *   5. Upload resources/locales/ to production over FTP, from that same
 *      freshly-pushed worktree state.
 *   6. Clear the production cache — CakePHP's translation cache would
 *      otherwise keep serving the old .po content until it naturally
 *      expires. Uploads webroot/clear-cache.php with a freshly generated
 *      one-off token, prints the URL for you to open yourself (this host's
 *      anti-bot challenge blocks a plain Node request — see
 *      clearRemoteCache() below), waits for Enter, then deletes it from
 *      the server. Always runs (unlike deploy.mjs's --clear-cache, which
 *      is opt-in) — this workflow's whole point is getting a translation
 *      change live, so skipping the cache-bust would defeat it.
 *
 * Usage:
 *   FTP_SERVER=... FTP_USERNAME=... FTP_PASSWORD=... node bin/translate-and-deploy.mjs
 *
 * The Gemini key comes from config/app_local.php's Ai.geminiApiKey — see
 * readGeminiApiKey() below. FTP credentials are read from the environment
 * only — never hardcoded, this file is committed to git.
 *
 * Plain FTP, not FTPS — see deploy.mjs's header comment for why.
 */
import { Client } from 'basic-ftp';
import { execFileSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { appendFileSync, existsSync, readdirSync, readFileSync, rmSync, symlinkSync } from 'node:fs';
import { join, relative, sep } from 'node:path';
import readline from 'node:readline/promises';
import { stdin, stdout } from 'node:process';
import { Readable } from 'node:stream';

const ROOT = new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const REPO_ROOT = join(ROOT, '..');
const ROOT_REL = relative(REPO_ROOT, ROOT).split(sep).join('/');

// Separate from deploy.mjs's .deploy-build and deploy-translations.mjs's
// .deploy-translations-build — none of these scripts should be able to
// clobber another's export if run back to back.
const WORKTREE_DIR = join(ROOT, '.translate-and-deploy-worktree');

// The production site — used only to build the clear-cache.php URL printed
// for you to open. Overridable via SITE_URL in case this ever needs to
// point elsewhere (a staging copy, a domain change).
const SITE_URL = process.env.SITE_URL || 'https://rsite.great-site.net';

// The "lite" flash variant — plain gemini-flash-latest returned persistent
// 503 "high demand" errors even with retries when this script was tested;
// "lite" is plenty capable for short UI-string translation and was the
// more reliable choice historically for the same reason in
// Admin\AssistantController.
const GEMINI_MODEL = 'gemini-flash-lite-latest';
const GEMINI_URL_TEMPLATE = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s';
const GEMINI_MAX_RETRIES = 4;
const GEMINI_RETRY_BASE_DELAY_MS = 2000;

function run(command, args, cwd = ROOT) {
    console.log(`\n$ ${command} ${args.join(' ')}`);
    execFileSync(command, args, { cwd, stdio: 'inherit', shell: true });
}

// Same as run(), but WITHOUT shell:true — for git invocations whose
// arguments can contain characters a shell would reinterpret (a commit
// message with `<`/`>`/spaces, e.g. "...<noreply@anthropic.com>", gets
// mangled into shell redirection when execFileSync's args array is joined
// and handed to a shell). run()'s shell:true exists for Windows .cmd/.bat
// shims (npx, composer); git itself needs no shell, so this is safe.
function runGit(args, cwd = ROOT) {
    console.log(`\n$ git ${args.join(' ')}`);
    execFileSync('git', args, { cwd, stdio: 'inherit' });
}

function capture(command, args, cwd = ROOT) {
    return execFileSync(command, args, { cwd, encoding: 'utf8' }).trim();
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

// Boots CakePHP just enough to resolve Configure::read('Ai.geminiApiKey')
// — the same value AssistantController uses — without pre-requiring
// config/paths.php ourselves first (config/bootstrap.php does that
// internally; requiring it twice throws harmless but noisy "constant
// already defined" warnings on every ROOT/APP/... constant).
function readGeminiApiKey() {
    const php = [
        "chdir(__DIR__);",
        "require 'vendor/autoload.php';",
        "require 'config/bootstrap.php';",
        "echo \\Cake\\Core\\Configure::read('Ai.geminiApiKey');",
    ].join(' ');

    const key = execFileSync('php', ['-r', php], { cwd: ROOT, encoding: 'utf8' }).trim();
    return key || null;
}

// Checks out `main` into a throwaway worktree — a second, independent
// checkout in its own directory, sharing this repo's .git — so extraction
// can run against exactly what's on `main` without ever touching this
// checkout's branch or working tree (see this file's header comment for
// why that matters). Any leftover worktree from an interrupted previous
// run is torn down first, through git (not a raw directory delete) so its
// .git/worktrees/ entry doesn't dangle.
//
// vendor/ and config/app_local.php are both gitignored, so the fresh
// worktree has neither — bin/cake.php can't even boot without vendor/, and
// the Gemini key lives in app_local.php. Both are symlinked in from this
// checkout rather than copied/reinstalled: cheap, and always exactly what
// this machine already has configured.
function setUpMainWorktree() {
    if (existsSync(WORKTREE_DIR)) {
        try {
            runGit(['worktree', 'remove', '--force', WORKTREE_DIR], REPO_ROOT);
        } catch {
            rmSync(WORKTREE_DIR, { recursive: true, force: true });
        }
    }

    runGit(['worktree', 'add', '--detach', WORKTREE_DIR, 'main'], REPO_ROOT);

    const worktreeRoot = join(WORKTREE_DIR, ROOT_REL);
    symlinkSync(join(ROOT, 'vendor'), join(worktreeRoot, 'vendor'));
    symlinkSync(join(ROOT, 'config', 'app_local.php'), join(worktreeRoot, 'config', 'app_local.php'));

    return worktreeRoot;
}

function tearDownMainWorktree() {
    runGit(['worktree', 'remove', '--force', WORKTREE_DIR], REPO_ROOT);
}

// Runs inside worktreeRoot (main's checkout, see setUpMainWorktree()), not
// this script's own cwd — so only __() strings already on `main` are ever
// extracted/translated. worktreeRoot needs its own vendor/ to run
// bin/cake.php at all (vendor/ is gitignored, so a fresh worktree doesn't
// have one) — setUpMainWorktree() symlinks it in from this checkout before
// this runs.
function extractMessages(worktreeRoot) {
    console.log('=== Extracting messages (bin/cake i18n extract, from main) ===');
    // Deliberately no --marker-error: it flags every dynamic __($variable)
    // call (e.g. __($page->title)) as an "invalid marker" — those are
    // legitimate and expected in this app (see the README's i18n section),
    // just not something the extractor can pull a static string from. The
    // flag would only add noise here, not catch a real problem.
    run('php', [
        'bin/cake.php', 'i18n', 'extract',
        '--paths=src,templates',
        '--output=resources/locales',
        '--merge=yes',
        '--overwrite',
        '--extract-core=no',
    ], worktreeRoot);
}

// Both default.pot and default.po share the same simple shape here: every
// entry is `msgid "..."` (single line — the app's __() calls never
// produce the multi-line `msgid ""` gettext form) optionally preceded by
// `#:` reference comments, followed by `msgstr "..."` in the .po file.
function parsePoMsgids(content) {
    const ids = new Set();
    for (const match of content.matchAll(/^msgid "((?:[^"\\]|\\.)*)"$/gm)) {
        ids.add(unescapePo(match[1]));
    }
    return ids;
}

function unescapePo(text) {
    return text.replace(/\\(.)/g, (_, ch) => (ch === 'n' ? '\n' : ch === 't' ? '\t' : ch));
}

function escapePo(text) {
    return text.replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\t/g, '\\t');
}

// Extracts the msgid text plus every #: reference-comment line printed
// directly above it, in file order — the reference comments end up in the
// appended .po entries too (same convention every existing entry follows).
function parsePotEntries(content) {
    const entries = [];
    const lines = content.split('\n');
    let pendingRefs = [];

    for (const line of lines) {
        if (line.startsWith('#:')) {
            pendingRefs.push(line);
            continue;
        }
        const match = line.match(/^msgid "((?:[^"\\]|\\.)*)"$/);
        if (match) {
            entries.push({ msgid: unescapePo(match[1]), refs: pendingRefs });
            pendingRefs = [];
            continue;
        }
        if (line.startsWith('msgstr') || line === '') {
            continue;
        }
        pendingRefs = [];
    }

    return entries;
}

// The model occasionally returns 503 "currently experiencing high demand"
// under real-world load (same behavior Admin\AssistantController's comment
// documents for this model) — a batch translation run isn't latency
// sensitive the way an interactive chat reply is, so retrying with backoff
// here is the right trade-off rather than failing the whole run on a
// transient overload.
async function translateBatch(apiKey, texts) {
    const url = GEMINI_URL_TEMPLATE.replace('%s', GEMINI_MODEL).replace('%s', apiKey);

    const systemPrompt = [
        'You are translating short UI strings for the admin panel and public website of a local fishing',
        'association (MO SRZ) in Slovakia, from English to Slovak.',
        'Translate naturally and concisely, matching the tone of existing UI copy (plain, direct, no marketing',
        'fluff). Preserve any placeholders exactly as they appear, e.g. {0}, {1} — never translate or remove them.',
        'Preserve capitalization style appropriate to Slovak UI text (usually sentence case, not Title Case).',
        'Return a JSON array of strings, one translation per input string, in the exact same order — no other text.',
    ].join(' ');

    let lastError;
    for (let attempt = 1; attempt <= GEMINI_MAX_RETRIES; attempt++) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                system_instruction: { parts: [{ text: systemPrompt }] },
                contents: [{ role: 'user', parts: [{ text: JSON.stringify(texts) }] }],
                generationConfig: {
                    responseMimeType: 'application/json',
                    responseSchema: { type: 'ARRAY', items: { type: 'STRING' } },
                },
            }),
        });

        if (response.ok) {
            return parseTranslateResponse(await response.json(), texts.length);
        }

        const bodyText = await response.text();
        lastError = new Error(`Gemini API request failed: ${response.status} ${bodyText}`);

        const retryable = response.status === 503 || response.status === 429;
        if (!retryable || attempt === GEMINI_MAX_RETRIES) {
            throw lastError;
        }

        const delay = GEMINI_RETRY_BASE_DELAY_MS * 2 ** (attempt - 1);
        console.log(`  Gemini returned ${response.status} (attempt ${attempt}/${GEMINI_MAX_RETRIES}) — retrying in ${delay}ms...`);
        await sleep(delay);
    }

    throw lastError;
}

function parseTranslateResponse(data, expectedCount) {
    const text = data?.candidates?.[0]?.content?.parts?.[0]?.text;
    if (typeof text !== 'string') {
        throw new Error(`Unexpected Gemini response shape: ${JSON.stringify(data)}`);
    }

    const translations = JSON.parse(text);
    if (!Array.isArray(translations) || translations.length !== expectedCount) {
        throw new Error(`Gemini returned ${translations?.length ?? 'non-array'} translations for ${expectedCount} inputs.`);
    }

    return translations;
}

// Runs extraction + translation inside worktreeRoot (main's checkout),
// appending any newly-translated strings to its sk_SK/default.po. Returns
// the list of what it added (empty if nothing was missing) — purely
// informational, the actual source of truth from here on is the file on
// disk (in the worktree), which the operator may still hand-edit before
// the commit step.
async function translateNewStrings(worktreeRoot, apiKey) {
    extractMessages(worktreeRoot);

    const potPath = join(worktreeRoot, 'resources', 'locales', 'default.pot');
    const poPath = join(worktreeRoot, 'resources', 'locales', 'sk_SK', 'default.po');

    const potContent = readFileSync(potPath, 'utf8');
    const poContent = readFileSync(poPath, 'utf8');

    const alreadyTranslated = parsePoMsgids(poContent);
    const potEntries = parsePotEntries(potContent);
    const missing = potEntries.filter((entry) => entry.msgid !== '' && !alreadyTranslated.has(entry.msgid));

    if (missing.length === 0) {
        console.log('\nNothing new to translate — every extracted string already has a Slovak entry.');
        return [];
    }

    console.log(`\n=== Translating ${missing.length} new string(s) with Gemini (${GEMINI_MODEL}) ===`);

    // Batches rather than one string per request (slow, wasteful of quota)
    // or the whole set at once (risks one oversized/timeout-prone call
    // derailing the entire run).
    const BATCH_SIZE = 20;
    const translated = [];
    for (let i = 0; i < missing.length; i += BATCH_SIZE) {
        const batch = missing.slice(i, i + BATCH_SIZE);
        console.log(`  translating ${i + 1}-${i + batch.length} of ${missing.length}...`);
        const results = await translateBatch(apiKey, batch.map((entry) => entry.msgid));
        batch.forEach((entry, index) => translated.push({ ...entry, msgstr: results[index] }));
    }

    const appended = translated
        .map((entry) => {
            const refLines = entry.refs.length ? entry.refs.join('\n') + '\n' : '';
            return `\n${refLines}msgid "${escapePo(entry.msgid)}"\nmsgstr "${escapePo(entry.msgstr)}"\n`;
        })
        .join('');

    appendFileSync(poPath, appended);

    return translated;
}

// Same fast-forward-only sync deploy.mjs/deploy-translations.mjs use —
// refuses rather than guessing if history has diverged. Run BEFORE the
// translate step (not just before commit) so the diff shown for review is
// relative to what's actually on origin, not a stale local main.
function syncMainWithOrigin() {
    console.log('\nFetching origin/main...');
    runGit(['fetch', 'origin', 'main'], REPO_ROOT);

    const localMain = capture('git', ['rev-parse', 'main'], REPO_ROOT);
    const remoteMain = capture('git', ['rev-parse', 'origin/main'], REPO_ROOT);
    if (localMain === remoteMain) {
        return;
    }

    const mergeBase = capture('git', ['merge-base', 'main', 'origin/main'], REPO_ROOT);
    if (mergeBase !== localMain) {
        console.error(
            `Local 'main' (${localMain.slice(0, 7)}) has commits origin/main (${remoteMain.slice(0, 7)}) doesn't — ` +
            "these have diverged. Resolve manually (rebase/merge/push) before running this."
        );
        process.exit(1);
    }

    console.log(`Fast-forwarding local 'main': ${localMain.slice(0, 7)} -> ${remoteMain.slice(0, 7)}`);
    runGit(['fetch', 'origin', 'main:main'], REPO_ROOT);
}

// Commits + pushes resources/locales/ from the worktree to main, if (and
// only if) there's something to commit — covers both "translate step
// added nothing and the operator didn't hand-edit anything" and
// "everything here was already committed on a previous run". Runs
// entirely inside the worktree, so this never touches your own
// checkout/branch.
function commitAndPushLocales(worktreeRoot) {
    const status = capture('git', ['status', '--porcelain', '--', 'resources/locales'], worktreeRoot);
    if (!status) {
        console.log('\nresources/locales/ has no changes to commit — skipping commit/push.');
        return false;
    }

    console.log('\n=== Committing resources/locales/ to main ===');
    runGit(['add', '--', 'resources/locales'], worktreeRoot);
    // Two separate -m flags (git joins them with a blank line) rather than
    // one string containing literal newlines — run()/runGit() may execute
    // through a shell (needed for Windows .cmd/.bat shims elsewhere in
    // these scripts), which mangles an embedded-newline argument into
    // multiple shell tokens instead of passing it through as one.
    runGit([
        'commit',
        '-m', 'Update translations',
        '-m', 'Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>',
    ], worktreeRoot);
    runGit(['push', 'origin', 'HEAD:main'], worktreeRoot);

    // Update the caller's own local `main` ref to match what was just
    // pushed, so a later run's syncMainWithOrigin() sees it as already up
    // to date. `git fetch origin main:main` refuses outright when `main`
    // is the branch currently checked out in REPO_ROOT (as opposed to the
    // worktree) — happens whenever you run this script from your own
    // `main` checkout rather than a feature branch. Detect that case and
    // fast-forward-merge instead, which works on a checked-out branch;
    // fall back to the plain ref fetch otherwise (works from any other
    // branch, no checkout side effect).
    const currentBranch = capture('git', ['branch', '--show-current'], REPO_ROOT);
    if (currentBranch === 'main') {
        runGit(['merge', '--ff-only', 'origin/main'], REPO_ROOT);
    } else {
        runGit(['fetch', 'origin', 'main:main'], REPO_ROOT);
    }

    return true;
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

// Uploads resources/locales/ straight from the worktree — it's already
// main's just-pushed HEAD, so no separate git-archive export step is
// needed the way deploy.mjs/deploy-translations.mjs do it standalone.
// Leaves `client` connected — the caller reuses it for clearRemoteCache().
async function deployLocalesOverFtp(client, worktreeRoot) {
    console.log('\n=== Uploading over FTP ===');
    const localesDir = join(worktreeRoot, 'resources', 'locales');

    let uploaded = 0;
    for (const { full, rel } of walkDir(localesDir, localesDir)) {
        const remotePath = '/htdocs/resources/locales/' + rel;
        await client.ensureDir('/htdocs/resources/locales/' + rel.split('/').slice(0, -1).join('/'));
        await client.uploadFrom(full, remotePath);
        uploaded += 1;
        console.log(`  uploaded resources/locales/${rel}`);
    }

    console.log(`\nDone. ${uploaded} file(s) uploaded.`);
}

// Reads webroot/clear-cache.php from the worktree (so this always reflects
// what main actually has) and substitutes its token placeholder with a
// freshly generated one-off secret — in memory only, never written back to
// the file on disk or committed.
function clearCacheScriptWithToken(worktreeRoot, token) {
    const placeholder = '__CACHE_TOKEN__';
    const source = readFileSync(join(worktreeRoot, 'webroot', 'clear-cache.php'), 'utf8');
    if (!source.includes(placeholder)) {
        throw new Error(`Couldn't find the ${placeholder} placeholder in webroot/clear-cache.php`);
    }
    return source.replace(placeholder, token);
}

// Uploads webroot/clear-cache.php with a one-off random token, then waits
// for you to open the URL yourself and confirm it ran, before deleting it
// from the server — same pattern as deploy.mjs's runOneOffScript(), see
// that function's comment for why this needs a real browser (this host's
// anti-bot challenge blocks a plain Node request) and why the file must
// not be left sitting on the server.
async function clearRemoteCache(client, worktreeRoot) {
    const token = randomBytes(16).toString('hex');
    const remotePath = '/htdocs/webroot/clear-cache.php';

    console.log('\n=== Clearing the production cache ===');
    await client.uploadFrom(Readable.from([clearCacheScriptWithToken(worktreeRoot, token)]), remotePath);

    const url = `${SITE_URL.replace(/\/$/, '')}/clear-cache.php?token=${token}`;
    console.log(`\nOpen this URL in your browser and confirm the output looks right:\n  ${url}`);

    const rl = readline.createInterface({ input: stdin, output: stdout });
    await rl.question('\nPress Enter once you\'ve checked it (this deletes clear-cache.php from the server)... ');
    rl.close();

    await client.remove(remotePath);
    console.log('clear-cache.php removed from the server.');
}

async function main() {
    const { FTP_SERVER, FTP_USERNAME, FTP_PASSWORD } = process.env;
    if (!FTP_SERVER || !FTP_USERNAME || !FTP_PASSWORD) {
        console.error('Missing FTP_SERVER / FTP_USERNAME / FTP_PASSWORD in the environment.');
        process.exit(1);
    }

    const apiKey = readGeminiApiKey();
    if (!apiKey) {
        console.error("Ai.geminiApiKey is not set in config/app_local.php (or GEMINI_API_KEY in its .env fallback).");
        process.exit(1);
    }

    syncMainWithOrigin();

    console.log("\n=== Checking out main into a throwaway worktree ===");
    const worktreeRoot = setUpMainWorktree();

    try {
        const translated = await translateNewStrings(worktreeRoot, apiKey);

        if (translated.length) {
            console.log(`\nAppended ${translated.length} translation(s) to resources/locales/sk_SK/default.po:`);
            for (const entry of translated) {
                console.log(`  "${entry.msgid}" -> "${entry.msgstr}"`);
            }
        }

        console.log(
            `\nReview and edit by hand now if needed: ${join(worktreeRoot, 'resources', 'locales', 'sk_SK', 'default.po')}` +
            '\nWhatever is on disk there when you press Enter is what gets committed and deployed.',
        );
        const rl = readline.createInterface({ input: stdin, output: stdout });
        await rl.question('\nPress Enter to commit, push, and deploy... ');
        rl.close();

        const committed = commitAndPushLocales(worktreeRoot);
        if (!committed && translated.length === 0) {
            console.log('\nNothing changed and nothing to deploy. Done.');
            return;
        }

        const client = new Client();
        client.ftp.verbose = false;
        try {
            await client.access({ host: FTP_SERVER, user: FTP_USERNAME, password: FTP_PASSWORD, secure: false });
            await deployLocalesOverFtp(client, worktreeRoot);
            await clearRemoteCache(client, worktreeRoot);
        } finally {
            client.close();
        }
    } finally {
        tearDownMainWorktree();
    }
}

main().catch((error) => {
    console.error('\nFailed:', error.message);
    process.exit(1);
});
