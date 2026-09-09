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
        $mode = in_array($requestedMode, ['html', 'nav', 'palette'], true) ? $requestedMode : 'text';
        $messages = (array)$this->request->getData('messages');

        $history = $this->sanitizeHistory($messages);
        if (!$history) {
            $this->set(['error' => __('Say something first.')]);

            return null;
        }

        $organisationName = (string)TableRegistry::getTableLocator()->get('Texts')->value('Organisation Name');
        $colors = TableRegistry::getTableLocator()->get('Colors')->allAsSlugMap();

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
                $colors,
            );

        try {
            $reply = $this->callGemini($apiKey, $systemPrompt, $history, $mode === 'nav');
        } catch (\RuntimeException $e) {
            $this->set(['error' => $e->getMessage()]);

            return null;
        }

        // Not run through sanitizeHtml(): that sanitizer's HTMLPurifier
        // backend predates flexbox/grid and silently strips any style=""
        // using them (see Admin\PagesController::editPoplatky()'s docblock
        // for the full story) — display:flex/grid layouts are exactly what
        // the 'html' mode prompt asks the model for (see
        // buildSystemPrompt()), so sanitizing here was breaking every
        // poster's layout before the admin ever saw it. The suggestion
        // still lands in a WYSIWYG textarea.js-wysiwyg field (News/Events/
        // Poplatky 'content'/'notice') the admin explicitly chooses to
        // save, same trust boundary as anything else typed into that field.
        $suggestion = $reply['suggestion'];

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
            'You are the AI assistant built into the admin panel of a CakePHP website for a local fishing'
                . ' association (MO SRZ). You have two jobs on this particular page (it has no specific field open'
                . ' for editing right now):',
            '1. Help the admin find where to do something in this admin panel (navigation).',
            '2. General-purpose help with any other request — answer questions, write or rewrite text, shorten or'
                . ' expand a passage the admin pastes in, translate something, brainstorm, explain something, etc.'
                . ' Treat this like a normal helpful assistant chat for anything that is not about editing a specific'
                . ' record\'s field on this site (for that, the admin should open the relevant add/edit page, which'
                . ' has its own field-aware assistant).',
            'Reply in Slovak, plain text, no markdown.',
            'You must always respond with the three fields in the response schema:',
            '- "message": your natural reply — this is the ONLY field actually shown to the admin here (there is no'
                . ' field to apply a separate suggestion to on this page), so it must contain your FULL answer,'
                . ' including any requested text itself. For a navigation question, point the admin to the right'
                . ' sidebar section and the specific action (e.g. "add" to create a new one, "edit" to change an'
                . ' existing one) — be concise and concrete, naming the exact sidebar section (in Slovak, using its'
                . ' label below) and what to click there. For a general request (rewrite/shorten/translate/draft/'
                . ' explain/etc.), put the complete result directly in "message" — never say the result is ready'
                . ' elsewhere or refer to a "suggestion" the admin has to look for.',
            '- "suggestion": always leave this null on this page — there is no field here for a suggestion to be'
                . ' applied to, so it would never be shown anyway.',
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

    /**
     * @param array<string, string> $colors The site's current live palette
     *   (Admin\ColorsController, slug => hex) — always the actual values an
     *   admin has set, never a fixed default, so the assistant's output
     *   stays on-brand even after someone changes the palette.
     */
    private function buildSystemPrompt(
        string $fieldLabel,
        string $title,
        string $existingText,
        string $descriptionContext,
        string $imageUrl,
        string $organisationName,
        string $mode,
        array $colors = []
    ): string {
        $lines = [
            'You are helping write short website copy in Slovak for a local fishing association (MO SRZ) website.',
            "You are chatting with an admin editing a {$fieldLabel} field.",
            'Reply in Slovak.',
            'Write Slovak diacritics (á, ä, č, ď, é, í, ĺ, ľ, ň, ó, ô, ŕ, š, ť, ú, ý, ž and their'
                . ' uppercase forms) as literal UTF-8 characters, never as HTML entities (&aacute; etc.) or numeric'
                . ' character references (&#225; etc.) — this applies in both "message" and "suggestion".',
            'You must always respond with the two fields in the response schema:',
            '- "message": your natural chat reply — an answer, clarification, or short comment. Always present, plain text, no markdown.',
        ];

        if ($mode === 'html') {
            $orgName = $organisationName !== '' ? $organisationName : 'ORGANISATION NAME HERE';
            $palette = 'primary ' . ($colors['primary'] ?? '#001a3b')
                . ', secondary ' . ($colors['secondary'] ?? '#ec2828')
                . ', background ' . ($colors['bg'] ?? '#fff')
                . ', alternate background ' . ($colors['bg_alt'] ?? '#f5f7fa');
            $lines[] = '- "suggestion": a ready-to-use HTML mini content page about the subject below, for a local fishing'
                . ' association website — shown to visitors in a popup. Design it however you think looks best: you have'
                . ' complete creative freedom over structure, layout, and visual style — nothing here is a template to'
                . ' follow, only the actual technical constraints of where and how this gets shown, listed below.'
                . " This is the site's current color palette (admin-configurable, so treat it as live, not fixed):"
                . " {$palette}. You can include those colors or variations of them, but you can come up with different"
                . ' colors also.'
                . " If you show the organisation name, use the exact name given to you (\"{$orgName}\") — never invent or"
                . ' guess one, and never leave a generic placeholder if a real name is provided below.'
                . ' Write the actual heading/section/detail text based on the title and description given below — mention'
                . ' the concrete subject and any concrete facts (location, dates, reference numbers) present in the'
                . ' description — do not write a vague placeholder that could apply to any article, and do not copy the'
                . ' description verbatim. If no title/description is given, ask the admin what it should be about instead'
                . ' of inventing generic filler content.'
                . ' This renders inside a popup around 900px wide on desktop, but the popup itself is responsive and'
                . ' shrinks on smaller screens — never assume a fixed pixel width, size for "narrow-ish card", not'
                . ' "wide desktop page". The outermost element should not set its own max-width/width — leave that'
                . ' unset so it fills the popup. If you lay content out in side-by-side columns (flexbox/grid),'
                . ' keep each column\'s min-width modest (150-200px, not 280px+) and always include flex-wrap: wrap'
                . ' (or grid\'s equivalent, minmax()) as a fallback — the columns need to actually fit side by side at'
                . ' that ~900px width with room for gaps, and gracefully stack on an even narrower viewport instead of'
                . ' silently collapsing to one column at the intended width too.'
                . ' Style everything with inline style="..." attributes ONLY — flexbox, grid, gap, border-radius,'
                . ' gradients, box-shadow, all of it works fine inline, so use whatever layout/visual approach fits'
                . ' best. Do NOT write a <style> block or any class-based CSS: this gets pasted into a WYSIWYG editor'
                . ' that renders it live as you write it, and that editor treats a <style> tag as its own chrome, not'
                . ' page content — it silently disappears (along with everything that depended on it, since classes'
                . ' with no matching rule left do nothing) the moment it lands in the field, so a <style> block would'
                . ' leave the poster unstyled instead of styled. class="..." attributes are pointless for the same'
                . ' reason and should be left out too. No @media queries, :hover, or other pseudo-classes either —'
                . ' this is a static poster, not an interactive page, so there is nothing for those to respond to.'
                . ($imageUrl !== ''
                    ? " The article has an uploaded image ({$imageUrl}) — use it however (or however much) you think"
                        . ' improves the result, including not at all.'
                    : '')
                . ' Allowed tags ONLY: p, br, strong, b, em, i, u, s, a (with href), ul, ol, li, h1, h2, h3, h4, blockquote,'
                . ' img (with src/alt), span, div — each may carry an inline style attribute (no class attribute — see'
                . ' above). No <script>, no event handler attributes (onclick etc.), no external stylesheet/font <link>'
                . ' tags, no other tags.'
                . ' If the admin is instead just'
                . ' asking a question or the reply is not meant to be dropped into the editor, leave "suggestion" as null.';
        } elseif ($mode === 'palette') {
            $paletteSlugs = ['primary', 'secondary', 'bg', 'bg_alt', 'text_muted', 'heading', 'link', 'link_hover'];
            $currentPalette = json_encode(
                array_intersect_key($colors, array_flip($paletteSlugs)),
                JSON_PRETTY_PRINT,
            );
            $lines[] = 'You are proposing a new color palette for the whole public website (not just one field) —'
                . ' the admin is describing what look/mood/season/theme they want, and you respond with a complete'
                . ' replacement palette.'
                . " The site's current palette is:\n{$currentPalette}"
                . ' - "suggestion": ONLY when the admin is actually asking for a new palette (or a change to the'
                . ' current one), a single JSON string (it goes inside the normal JSON "suggestion" string field, so'
                . ' escape it like any other string value) encoding an object with EXACTLY these 8 keys, each a'
                . ' 6-digit hex color starting with #: "primary", "secondary", "bg", "bg_alt", "text_muted",'
                . ' "heading", "link", "link_hover". Keep any key the admin\'s request does not call for unchanged'
                . ' from the current palette above rather than inventing a new value for it.'
                . ' These are real, functional UI colors, not just a mood board — keep the result actually usable:'
                . ' "bg" and "bg_alt" are page/card backgrounds (must stay light enough, or consistently dark if you'
                . ' intentionally go for a dark theme, for "text_muted"/"heading" to read clearly on top of them);'
                . ' "primary" is the main brand color (navbar, headings, buttons); "secondary" is the accent color'
                . ' (badges, highlights) and should contrast against "primary", not blend into it; "link"/"link_hover"'
                . ' are plain text link colors, distinct enough from body text to read as clickable.'
                . ' If the admin is instead just asking a question, or the reply is not meant to be applied as a new'
                . ' palette, leave "suggestion" as null.';
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
            $lines[] = "This article's uploaded image URL (use it as described above, at your discretion):"
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
