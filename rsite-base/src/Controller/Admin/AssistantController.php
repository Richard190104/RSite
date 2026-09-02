<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * Floating chat assistant for admin forms (News/Notifications description
 * so far) — a chat bubble that knows the current record's title/existing
 * text as context, and lets the admin ask for edits/drafts conversationally.
 *
 * Every reply carries two parts: a `message` (always shown as the chat
 * bubble text) and an optional `suggestion` — a ready-to-use draft for the
 * target field. The "Use this" button in the UI (admin-assistant-chat.js)
 * only appears when `suggestion` is present, so a reply that's just an
 * answer or clarification (e.g. "what tone should I use?") never gets
 * mistaken for something meant to overwrite the field. The model itself
 * decides which replies count as a suggestion vs. plain conversation — see
 * the response schema in callGemini().
 *
 * Deliberately stateless server-side: the full message history is sent by
 * the client on every request (see webroot/js/admin-assistant-chat.js) and
 * nothing is persisted — closing/reloading the page drops the
 * conversation, same as the chat never happened.
 *
 * Named "Assistant" (not "Ai") on purpose — this host's WAF (InfinityFree /
 * Cloudflare) returns a 403 for any URL path containing "ai", which broke
 * both this controller's route and the admin-ai-chat.js asset under their
 * old names. Keep "ai" out of any new public-facing path under this
 * feature (URLs, filenames) to avoid re-triggering the same block.
 */
class AssistantController extends AppController
{
    use HtmlSanitizeTrait;

    // The "-latest" alias is used instead of a pinned version (e.g.
    // gemini-2.0-flash) so this keeps working as Google retires specific
    // model versions — pinned versions have gone stale within months.
    // The "lite" variant specifically: for short copy-suggestion prompts
    // it's plenty capable, and sees far less traffic than plain
    // gemini-flash-latest, which was timing out/503-ing under load.
    private const GEMINI_MODEL = 'gemini-flash-lite-latest';
    private const GEMINI_URL_TEMPLATE = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s';

    // How many of the most recent News articles buildNavigationPrompt()
    // includes so an admin can be pointed straight at one by content — kept
    // small and bounded so the prompt's token cost doesn't grow with the
    // site's article count.
    private const NEWS_CONTEXT_LIMIT = 20;
    private const MAX_HISTORY_MESSAGES = 20;

    public function chat()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');

        $this->viewBuilder()->setOption('serialize', ['error', 'message', 'suggestion', 'link']);

        $apiKey = Configure::read('Ai.geminiApiKey');
        if (!$apiKey) {
            $this->set(['error' => __('The AI assistant is not configured (missing Ai.geminiApiKey in app_local.php).')]);

            return null;
        }

        $title = (string)$this->request->getData('title');
        $existingText = (string)$this->request->getData('existing_text');
        $descriptionContext = (string)$this->request->getData('description_context');
        $imageUrl = (string)$this->request->getData('image_url');
        $fieldLabel = (string)$this->request->getData('field_label', __('description'));
        $requestedMode = (string)$this->request->getData('mode');
        $mode = in_array($requestedMode, ['html', 'nav'], true) ? $requestedMode : 'text';
        $messages = (array)$this->request->getData('messages');

        $history = $this->sanitizeHistory($messages);
        if (!$history) {
            $this->set(['error' => __('Say something first.')]);

            return null;
        }

        $organisationName = (string)TableRegistry::getTableLocator()->get('Texts')->value('Organisation Name');

        $systemPrompt = $mode === 'nav'
            ? $this->buildNavigationPrompt()
            : $this->buildSystemPrompt(
                $fieldLabel,
                $title,
                $existingText,
                $descriptionContext,
                $imageUrl,
                $organisationName,
                $mode,
            );

        try {
            $reply = $this->callGemini($apiKey, $systemPrompt, $history, $mode === 'nav');
        } catch (\RuntimeException $e) {
            $this->set(['error' => $e->getMessage()]);

            return null;
        }

        $suggestion = $reply['suggestion'];
        if ($suggestion !== null && $mode === 'html') {
            $suggestion = $this->sanitizeHtml($suggestion);
        }

        $link = $mode === 'nav' ? $this->resolveNavigationLink($reply['target']) : null;

        $this->set([
            'message' => $reply['message'],
            'suggestion' => $suggestion,
            'link' => $link,
        ]);

        return null;
    }

    /**
     * @param array<mixed> $messages
     * @return array<int, array{role: string, text: string}>
     */
    private function sanitizeHistory(array $messages): array
    {
        $history = [];
        foreach (array_slice($messages, -self::MAX_HISTORY_MESSAGES) as $message) {
            if (!is_array($message)) {
                continue;
            }
            $role = $message['role'] ?? null;
            $text = trim((string)($message['text'] ?? ''));
            if ($text === '' || !in_array($role, ['user', 'assistant'], true)) {
                continue;
            }
            $history[] = ['role' => $role, 'text' => $text];
        }

        return $history;
    }

    /**
     * System prompt for the navigation-helper mode used on admin pages
     * that aren't an add/edit form (the widget has no field to draft text
     * for there) — answers "where do I do X" questions using
     * AppController::adminCategories() as the single source of truth for
     * what sections/actions actually exist, so this never drifts out of
     * sync with the real sidebar.
     *
     * Also tells the model about every Texts row (id + slug) and every News
     * article (id + title + description) — these are the two sections where
     * an admin plausibly asks about one specific existing item by name/
     * content ("where do I change the organisation name", "where's the
     * article about the lake cleanup") rather than just the section as a
     * whole. Other sections (Events, Galleries...) only ever resolve to the
     * section itself — see the "target" field below.
     */
    private function buildNavigationPrompt(): string
    {
        $sectionLines = [];
        foreach (AppController::adminCategories() as $controller => $category) {
            $actionsList = implode(', ', $category['actions']);
            $sectionLines[] = "- {$category['label']} (controller: {$controller}, actions: {$actionsList}):"
                . " {$category['description']}";
        }

        $textRows = TableRegistry::getTableLocator()->get('Texts')
            ->find()
            ->select(['id', 'slug'])
            ->orderBy(['slug' => 'ASC'])
            ->all();
        $textLines = [];
        foreach ($textRows as $text) {
            $textLines[] = "- text:{$text->id} — \"{$text->slug}\"";
        }

        // Capped to the most recent NEWS_CONTEXT_LIMIT articles — without a
        // limit this list (and the prompt's token cost) would grow forever
        // as the site accumulates articles. An admin asking about an older
        // article the bot doesn't have here just gets an honest "couldn't
        // find it" instead of a wrong answer (see the final instruction
        // below), which is an acceptable trade-off for keeping every
        // nav-mode message's cost bounded.
        $newsRows = TableRegistry::getTableLocator()->get('News')
            ->find()
            ->select(['id', 'title', 'description'])
            ->orderBy(['date' => 'DESC'])
            ->limit(self::NEWS_CONTEXT_LIMIT)
            ->all();
        $newsLines = [];
        foreach ($newsRows as $article) {
            $newsLines[] = "- news:{$article->id} — \"{$article->title}\": {$article->description}";
        }

        return implode("\n", [
            'You are a navigation helper built into the admin panel of a CakePHP website for a local fishing'
                . ' association (MO SRZ). An admin is asking where to find something or how to do something in this'
                . ' admin panel — you are NOT drafting or editing any field content right now.',
            'Reply in Slovak, plain text, no markdown.',
            'You must always respond with the three fields in the response schema:',
            '- "message": your answer — point the admin to the right sidebar section and the specific action (e.g.'
                . ' "add" to create a new one, "edit" to change an existing one). Be concise and concrete: name the'
                . ' exact sidebar section (in Slovak, using its label below) and what to click there.',
            '- "suggestion": always leave this as null. This mode never drafts or fills in a field — it only gives'
                . ' directions. Even if the admin asks you to write something, explain that you can only do that from'
                . ' the assistant inside the relevant add/edit page, and tell them how to get there.',
            '- "target": a machine-readable pointer to where the message sends the admin, so the UI can render an'
                . ' actual clickable link — one of these exact shapes, or null:'
                . "\n  1. \"text:<id>\" — ONLY when the admin is asking about one specific named item from the Texts"
                . ' list below (e.g. organisation name, city, email) and you can identify exactly which row it is.'
                . "\n  2. \"news:<id>\" — ONLY when the admin is asking about one specific existing News article (by"
                . ' title or by something mentioned in its description) and you can identify exactly which one from'
                . ' the News list below.'
                . "\n  3. \"<controller>:add\" — when the admin is asking how to CREATE a new item in a section that"
                . ' supports "add" (per its actions list below) — e.g. "how do I add a news article", "where do I'
                . ' add an event".'
                . "\n  4. \"<controller>\" — the bare controller name, when pointing at a section's listing page in"
                . ' general (not creating, not one specific item) — e.g. "where are the settings for the navbar".'
                . "\n  5. null — when the question isn't about a specific findable section (e.g. a general question,"
                . ' or something not covered by any section below).'
                . "\n  For shapes 1-4, copy the id/controller verbatim from the lists below — never invent or guess"
                . ' one. Only use "<controller>:add" when "add" is actually listed in that section\'s actions.',
            'Here is the complete, authoritative list of admin sections, what each one is for, and which actions'
                . ' they support:',
            implode("\n", $sectionLines),
            'Here is the complete list of existing Texts rows (id and slug) — use these ids for "target" when the'
                . ' question is about one of these specific values:',
            implode("\n", $textLines),
            'Here is a list of the ' . self::NEWS_CONTEXT_LIMIT . ' most recent News articles (id, title,'
                . ' description) — NOT the complete list, older articles may exist that aren\'t shown here. Use'
                . ' these ids for "target" when the question is about one of these specific articles:',
            implode("\n", $newsLines),
            'If a question is about a News article you can\'t find in that list, it may simply be older than what\'s'
                . ' shown — say so honestly (e.g. suggest checking the News section\'s full list) instead of'
                . ' guessing an id or claiming the article doesn\'t exist at all.',
            'If a question is about something not covered by any of these sections, say so honestly instead of'
                . ' guessing or inventing a section that doesn\'t exist.',
        ]);
    }

    private function buildSystemPrompt(
        string $fieldLabel,
        string $title,
        string $existingText,
        string $descriptionContext,
        string $imageUrl,
        string $organisationName,
        string $mode
    ): string {
        $lines = [
            'You are helping write short website copy in Slovak for a local fishing association (MO SRZ) website.',
            "You are chatting with an admin editing a {$fieldLabel} field.",
            'Reply in Slovak.',
            'You must always respond with the two fields in the response schema:',
            '- "message": your natural chat reply — an answer, clarification, or short comment. Always present, plain text, no markdown.',
        ];

        if ($mode === 'html') {
            $letterhead = $organisationName !== '' ? $organisationName : 'ORGANISATION NAME HERE';
            $lines[] = '- "suggestion": a ready-to-use HTML notice/announcement poster for a rich-text editor — modeled after a'
                . ' traditional official fishing-association notice board announcement (organisation letterhead, a short'
                . ' "OZNAMUJE"/"VYHLASUJE"-style label as plain accent-colored text with no background of its own, an optional'
                . ' legal reference line, one large emphasized central statement, and detail lines below), but rendered in a'
                . ' clean modern style using this site\'s own palette instead of the old plain black-text-on-white-paper look.'
                . ' Follow this exact HTML structure/skeleton — copy the tag nesting and inline-style approach precisely, only'
                . ' changing text content and the size/padding values as described further below:'
                . "\n\n<div style=\"background-color:#fff;color:#143a6b;padding:2.5rem;border-radius:0.8rem;text-align:center;"
                . "border:1px solid #f5f7fa;min-height:500px;max-width:70rem;margin:0 auto;\">"
                . "\n<p style=\"font-weight:700;letter-spacing:0.05em;text-transform:uppercase;font-size:1rem;margin:0;\">"
                . "{$letterhead}</p>"
                . "\n<p style=\"font-size:1.1rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;margin-top:0.6rem;"
                . "margin-bottom:0;color:#f9d71c;\">OZNAMUJE / VYHLASUJE</p>"
                . "\n<p style=\"font-size:0.9rem;color:#143a6b;margin-top:1rem;margin-bottom:0;\">Optional legal reference line"
                . ' (law/decree number), only if relevant to the subject.</p>'
                . "\n<h1 style=\"font-size:2.6rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:#143a6b;"
                . "margin-top:1.4rem;margin-bottom:0;line-height:1.2;\">MAIN STATEMENT HERE (short, 2-5 words, the single most"
                . ' important fact)</h1>'
                . "\n<div style=\"width:60px;height:4px;background-color:#f9d71c;margin:1.2rem auto;\"></div>"
                . "\n<p style=\"font-size:1.1rem;line-height:1.7;color:#143a6b;\">Detail lines here — location/revír number,"
                . ' date range, or other concrete specifics relevant to the subject, plus one or two sentences of explanation if'
                . ' useful.</p>'
                . "\n</div>"
                . "\n\nUse the exact organisation name given to you as the letterhead text — never invent or guess one, and never"
                . ' leave a generic "MO SRZ" or placeholder if a real name is provided below.'
                . ' The "OZNAMUJE"/"VYHLASUJE" label is plain colored text with NO background pill/box behind it — do not add'
                . ' background-color to that element.'
                . ' The outer div must always keep max-width:70rem;margin:0 auto; exactly as in the skeleton — this fixes the'
                . ' poster to a consistent, correctly-proportioned width instead of stretching full-width in whatever container it'
                . ' ends up in. Never remove or change these two values.'
                . " \n\nADAPT THE OVERALL SIZE AND SPACING TO HOW MUCH TEXT THERE ACTUALLY IS — this is important, do not always use"
                . ' the same fixed sizes from the skeleton above:'
                . " \n- The outer div must always keep min-height:500px (more, e.g. 600px+, only if a long detail paragraph"
                . ' genuinely needs it — never shrink below 500px). Critically, the CONTENT itself — not empty padding — must'
                . ' visually fill that height. Do not just add a big padding number and call it done; actually make each element'
                . ' bigger and more spaced out so the poster looks intentionally full and well-composed at that size, never like a'
                . ' small cluster of text floating in a mostly-empty white box.'
                . " \n- If the detail paragraph ends up short (roughly one sentence or less), fill the space by scaling up EVERY"
                . ' element and the gaps between them, not just the outer padding: a noticeably larger main statement heading'
                . ' (e.g. 3.5-4.5rem), a larger detail paragraph (e.g. 1.3-1.5rem), a taller/wider divider bar, and generous'
                . ' margin-top on each element (e.g. 2-2.5rem between the label and the heading, between the heading and the'
                . ' divider, and between the divider and the detail paragraph) so the whole 500px+ is used by clearly-spaced'
                . ' content, not by one small cluster of normal-sized text sitting in a large blank area.'
                . " \n- If the detail paragraph is long (multiple sentences covering several facts), the text itself naturally"
                . ' fills the space, so keep sizes closer to the skeleton defaults or slightly smaller (heading ~2-2.4rem, detail'
                . ' paragraph ~1-1.1rem) and tighten the margins between elements — a text-heavy notice should stay compact and'
                . ' readable, relying on the amount of text rather than oversized elements to reach the minimum height.'
                . " \n- IMPORTANT: do NOT use display:flex, justify-content, align-items, or flex-direction to center/fill content —"
                . ' this sanitizer does not support flexbox properties at all and will silently strip them, leaving the poster'
                . ' looking broken/uncentered. Achieve the "filled" look ONLY through padding, margin/margin-top, and font-size on'
                . ' the elements themselves, exactly as instructed above — never rely on flexbox or any positioning property.'
                . ' Always keep the overall structure (letterhead line, oznamuje/vyhlasuje label, optional legal reference, one'
                . ' large main statement, divider, detail paragraph) even as the exact wording, sizes, and whether the'
                . ' legal-reference line is included vary by subject and length.'
                . ' Never output just a <div> with highlighted <span> lines of plain paragraph text — that is NOT an acceptable'
                . ' result. Only ever use these exact colors, never invent a different hue (e.g. no red for warnings — even for a'
                . ' strict/serious announcement, stick to this palette): navy #143a6b, yellow #f9d71c, white #fff, light gray'
                . ' #f5f7fa.'
                . ' Write the actual label/statement/detail text based on the title and description given below — mention the'
                . ' concrete subject (what is being announced, and any concrete facts like location, dates, reference numbers'
                . ' present in the description) — do not write a vague "Dôležité oznámenie, prosíme prečítajte si usmernenia"'
                . ' placeholder that could apply to any article, and do not copy the description verbatim — distill it into the'
                . ' notice-board format above. If no title/description is given, ask the admin what the notice should be about'
                . ' instead of inventing generic filler content.'
                . ($imageUrl !== ''
                    ? ' The article has an uploaded image (URL given below) — when asked to use it, or by default if it makes'
                        . ' sense, use it as a full hero background instead of the plain white/light-gray card. Do this with TWO'
                        . ' nested divs — the CSS sanitizer here does not support linear-gradient(), so a gradient overlay trick'
                        . ' will not work; use a solid semi-transparent overlay div instead:'
                        . "\n<div style=\"background-image:url('{$imageUrl}');background-size:cover;background-position:center;"
                        . "border-radius:0.8rem;max-width:70rem;margin:0 auto;\">"
                        . "\n<div style=\"background-color:rgba(20,58,107,0.72);padding:2.5rem;border-radius:0.8rem;color:#fff;"
                        . "text-align:center;\">"
                        . "\n... the same letterhead/label/statement/divider/detail structure goes here ..."
                        . "\n</div></div>"
                        . ' The inner div\'s background-color (navy at ~0.7 opacity) sits on top of the photo and darkens it enough'
                        . ' for text to stay legible — switch all text colors inside to white #fff instead of navy in this photo'
                        . ' version (the "OZNAMUJE" label keeps its yellow text color, that stays readable against the dark'
                        . ' overlay). Keep the same overall structure (letterhead, label, optional legal line, main statement,'
                        . ' divider, detail paragraph) — only the background/text-color scheme changes to this photo+overlay version.'
                    : '')
                . ' Allowed tags ONLY: p, br, strong, b, em, i, u, s, a (with href), ul, ol, li, h1, h2, h3, h4, blockquote,'
                . ' img (with src/alt), span/div — all of these may carry an inline style limited to: text-align, color,'
                . ' background-color, background, background-image, background-size, background-position, padding, margin,'
                . ' margin-top, margin-right, margin-bottom, margin-left, border-radius, border, font-size, font-weight,'
                . ' line-height, width, max-width, height, min-height, display, text-decoration, letter-spacing, text-transform.'
                . ' No <script>,'
                . ' no <style> blocks, no event handler attributes, no other tags or CSS properties. If the admin is instead just'
                . ' asking a question or the reply is not meant to be dropped into the editor, leave "suggestion" as null.';
        } else {
            $lines[] = '- "suggestion": ONLY when the admin is asking you to draft or rewrite the actual field text, put the ready-to-use'
                . " text here, plain text. This is a \"{$fieldLabel}\" field — if that's a title/name/heading field, keep the"
                . ' suggestion very short (a few words, one short phrase, no ending punctuation); for a longer field like a'
                . ' description, write a normal full-length description covering the subject properly — do not artificially'
                . ' shorten it to a sentence or two, a few well-developed paragraphs is fine when the subject calls for it.'
                . ' If the admin is instead asking a question, asking for clarification,'
                . ' or the reply is not meant to be dropped straight into the field, leave "suggestion" as null — do not put a draft'
                . ' there just because the conversation is about the field.';
        }

        if ($title !== '') {
            $lines[] = "Title/subject of this record: {$title}";
        }
        if ($descriptionContext !== '') {
            $lines[] = "The article's own description text — use this as the concrete subject matter:\n{$descriptionContext}";
        }
        if ($existingText !== '' && $existingText !== $descriptionContext) {
            $lines[] = "Current text already in the field being edited:\n{$existingText}";
        }
        if ($mode === 'html' && $imageUrl !== '') {
            $lines[] = "This article's uploaded image URL (use it as described above if the admin wants a photo background):"
                . " {$imageUrl}";
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<int, array{role: string, text: string}> $history
     * @return array{message: string, suggestion: string|null, target: string|null}
     */
    private function callGemini(string $apiKey, string $systemPrompt, array $history, bool $withTarget = false): array
    {
        $client = new Client();
        $url = sprintf(self::GEMINI_URL_TEMPLATE, self::GEMINI_MODEL, $apiKey);

        $contents = array_map(
            fn (array $message) => [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['text']]],
            ],
            $history,
        );

        $properties = [
            'message' => ['type' => 'STRING'],
            'suggestion' => ['type' => 'STRING', 'nullable' => true],
        ];
        $required = ['message', 'suggestion'];
        if ($withTarget) {
            $properties['target'] = ['type' => 'STRING', 'nullable' => true];
            $required[] = 'target';
        }

        $response = $client->post($url, [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => $contents,
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => $properties,
                    'required' => $required,
                ],
            ],
        ], ['type' => 'json', 'timeout' => 15]);

        if ($response->getStatusCode() === 503) {
            throw new \RuntimeException(__('The AI model is overloaded right now — try again in a moment.'));
        }

        if (!$response->isOk()) {
            throw new \RuntimeException(__('The AI assistant could not be reached right now.'));
        }

        $data = $response->getJson();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!is_string($text) || trim($text) === '') {
            throw new \RuntimeException(__('The AI assistant returned an empty suggestion.'));
        }

        $parsed = json_decode($text, true);
        $message = is_string($parsed['message'] ?? null) ? trim($parsed['message']) : trim($text);
        $suggestion = is_string($parsed['suggestion'] ?? null) ? trim($parsed['suggestion']) : null;
        $target = is_string($parsed['target'] ?? null) ? trim($parsed['target']) : null;

        if ($message === '') {
            throw new \RuntimeException(__('The AI assistant returned an empty suggestion.'));
        }

        return [
            'message' => $message,
            'suggestion' => $suggestion !== '' ? $suggestion : null,
            'target' => $target !== '' ? $target : null,
        ];
    }

    /**
     * Turns the model's "target" pointer (see buildNavigationPrompt()) into
     * an actual admin URL — built here from real routes/data, never taken
     * from the model directly, so a hallucinated id, controller name, or
     * unsupported action can't produce a broken or unintended link.
     */
    private function resolveNavigationLink(?string $target): ?string
    {
        if ($target === null) {
            return null;
        }

        if (str_starts_with($target, 'text:')) {
            return $this->resolveRowLink($target, 'text:', 'Texts');
        }

        if (str_starts_with($target, 'news:')) {
            return $this->resolveRowLink($target, 'news:', 'News');
        }

        $categories = AppController::adminCategories();

        if (str_ends_with($target, ':add')) {
            $controller = substr($target, 0, -strlen(':add'));
            $supportsAdd = isset($categories[$controller]) && in_array('add', $categories[$controller]['actions'], true);

            return $supportsAdd ? Router::url(['prefix' => 'Admin', 'controller' => $controller, 'action' => 'add']) : null;
        }

        if (!array_key_exists($target, $categories)) {
            return null;
        }

        return Router::url(['prefix' => 'Admin', 'controller' => $target, 'action' => 'index']);
    }

    /**
     * Shared "id:<n> for table <Controller>" resolution used by both the
     * text: and news: target shapes — checks the row actually exists before
     * building the edit link, same reasoning as resolveNavigationLink()'s
     * class comment.
     */
    private function resolveRowLink(string $target, string $prefix, string $tableAndController): ?string
    {
        $id = substr($target, strlen($prefix));
        if (!ctype_digit($id)) {
            return null;
        }

        $exists = TableRegistry::getTableLocator()->get($tableAndController)->exists(['id' => (int)$id]);

        return $exists
            ? Router::url(['prefix' => 'Admin', 'controller' => $tableAndController, 'action' => 'edit', $id])
            : null;
    }
}
