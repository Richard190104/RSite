#!/usr/bin/env node
/**
 * Finds every __() string in the app that doesn't yet have a Slovak
 * translation, asks Gemini to translate them, and appends the results to
 * resources/locales/sk_SK/default.po — automating the manual step the
 * README describes (bin/cake i18n extract, then diff .pot against .po by
 * hand). Only ADDS new msgid/msgstr pairs; it never touches or reorders
 * existing entries, so this is safe to run repeatedly.
 *
 * Does NOT cover dynamic __($variable) calls (e.g. __($page->title)) —
 * those aren't in default.pot either (see the README's i18n section) and
 * still need to be added to the .po file by hand.
 *
 * This only updates the .po file locally — it does not commit, push, or
 * deploy anything. Review the diff, then commit and run
 * `node bin/deploy-translations.mjs` (or a full deploy) yourself.
 *
 * Usage:
 *   node bin/translate-locales.mjs
 *
 * The Gemini key comes from config/app_local.php's Ai.geminiApiKey (same
 * config Admin\AssistantController reads) — no separate env var to manage.
 * Read via a `php -r` one-liner rather than parsing the PHP file's syntax
 * in JS, so it stays exactly in sync with whatever CakePHP itself resolves
 * (including its own env('GEMINI_API_KEY') fallback).
 */
import { execFileSync } from 'node:child_process';
import { appendFileSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

const ROOT = new URL('..', import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1');
const POT_PATH = join(ROOT, 'resources', 'locales', 'default.pot');
const PO_PATH = join(ROOT, 'resources', 'locales', 'sk_SK', 'default.po');

// The "lite" flash variant — plain gemini-flash-latest returned persistent
// 503 "high demand" errors even with retries (see translateBatch()) when
// this script was tested; "lite" is plenty capable for short UI-string
// translation and was the more reliable choice historically for this same
// reason in Admin\AssistantController.
const GEMINI_MODEL = 'gemini-flash-lite-latest';
const GEMINI_URL_TEMPLATE = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s';

function run(command, args, cwd = ROOT) {
    console.log(`\n$ ${command} ${args.join(' ')}`);
    execFileSync(command, args, { cwd, stdio: 'inherit', shell: true });
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

// bin/cake.php lives at ROOT/bin — running it via `php bin/cake.php` from
// ROOT keeps this independent of whatever cwd the script is invoked from.
function extractMessages() {
    console.log('=== Extracting messages (bin/cake i18n extract) ===');
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
    ]);
}

// Both default.pot and default.po share the same simple shape here: every
// entry is `msgid "..."` (single line — see this script's header comment,
// the app's __() calls never produce the multi-line `msgid ""` gettext
// form) optionally preceded by `#:` reference comments, followed by
// `msgstr "..."` in the .po file. Parsing pulls out just the msgid ->
// msgstr map; comments/headers are otherwise ignored.
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
// directly above it, in file order — used only for the "not yet
// translated" set, since the reference comments end up in the appended
// .po entries too (same convention every existing entry already follows).
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
        // Any other line (msgstr continuation, blank separators, the file
        // header block) resets the pending comment run so unrelated `#:`
        // blocks never attach to the wrong msgid.
        pendingRefs = [];
    }

    return entries;
}

const GEMINI_MAX_RETRIES = 4;
const GEMINI_RETRY_BASE_DELAY_MS = 2000;

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
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

async function main() {
    const apiKey = readGeminiApiKey();
    if (!apiKey) {
        console.error("Ai.geminiApiKey is not set in config/app_local.php (or GEMINI_API_KEY in its .env fallback).");
        process.exit(1);
    }

    extractMessages();

    const potContent = readFileSync(POT_PATH, 'utf8');
    const poContent = readFileSync(PO_PATH, 'utf8');

    const alreadyTranslated = parsePoMsgids(poContent);
    const potEntries = parsePotEntries(potContent);
    const missing = potEntries.filter((entry) => entry.msgid !== '' && !alreadyTranslated.has(entry.msgid));

    if (missing.length === 0) {
        console.log('\nNothing new to translate — every extracted string already has a Slovak entry.');
        return;
    }

    console.log(`\n=== Translating ${missing.length} new string(s) with Gemini (${GEMINI_MODEL}) ===`);

    // Gemini calls happen in small batches rather than one string per
    // request (slow, wasteful of quota) or the whole set at once (risks
    // one oversized/timeout-prone call derailing the entire run).
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

    appendFileSync(PO_PATH, appended);

    console.log(`\nAppended ${translated.length} translation(s) to ${PO_PATH.replace(ROOT, '')}:`);
    for (const entry of translated) {
        console.log(`  "${entry.msgid}" -> "${entry.msgstr}"`);
    }
    console.log('\nReview the diff, then commit and run bin/deploy-translations.mjs (or a full deploy) yourself.');
}

main().catch((error) => {
    console.error('\nTranslation failed:', error.message);
    process.exit(1);
});
