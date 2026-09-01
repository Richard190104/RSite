<?php
/**
 * @var \App\View\AppView $this
 * @var string|null $targetField Optional: DOM id of the plain-text "description"-style field the "Use this" button
 *   writes into. When omitted (together with $titleField), the widget has no fields to draft for and instead acts
 *   as a pure navigation helper — answering "where do I do X" questions using AppController::adminCategories() as
 *   its source of truth (see Admin\AssistantController::buildNavigationPrompt()). This is the default on every admin page
 *   the layout adds the widget to; add/edit pages opt into field mode by calling $this->set() with these vars
 *   before the layout renders (see templates/Admin/News/edit.php for an example).
 * @var string|null $titleField Optional: DOM id of the field used as the record's title/context. Required together
 *   with $targetField to enable field mode.
 * @var string|null $fieldLabel Human label sent to the AI describing what kind of text $targetField is (e.g. "news
 *   article summary"). Required together with $targetField to enable field mode.
 * @var string|null $htmlTargetField Optional: DOM id of a plain textarea holding raw poster HTML, with a preview
 *   modal bound to it (see data-html-preview-toggle-for in admin-html-preview.js). Only meaningful in field mode.
 * @var string|null $htmlFieldLabel Optional: human label sent to the AI describing the poster field (e.g. "HTML
 *   poster"), used only while the HTML mode button is selected. Defaults to "HTML poster" if omitted.
 * @var string|null $descriptionField Optional: DOM id of a plain-text field (usually $targetField itself) whose
 *   value is always sent as extra context, regardless of which mode is selected — without this, a poster generated
 *   from an empty/still-being-written rich-text field has nothing concrete to work from and falls back to generic
 *   copy.
 * @var string|null $imageUrl Optional: the public URL of an already-uploaded image for this record (e.g. News::image).
 *   When set, Admin\AssistantController::chat() can offer to use it as a full photo background (with a dark overlay) for
 *   an HTML-mode poster instead of the plain white/light-gray card.
 *
 * Floating chat bubble for the AI writing assistant — see
 * Admin\AssistantController::chat() and webroot/js/admin-assistant-chat.js for the
 * actual request/reply wiring. Included once, globally, from the admin
 * layout (templates/layout/admin.php) so it's available on every admin
 * page; individual add/edit pages opt into field-drafting mode by
 * setting the view vars above before the layout renders.
 *
 * In field mode, a row of mode buttons (Title / Description / HTML
 * poster, the last one only when $htmlTargetField is given) selects
 * which field is currently being edited — exactly one is active at a
 * time, switching modes never sends anything on its own, and every "Use
 * this" button always writes into whichever field the currently-selected
 * mode points at. This is deliberately explicit rather than guessed from
 * the conversation, so a later plain-text request never silently ends up
 * overwriting a previously-generated poster (or vice versa) just because
 * a mode was left selected from an earlier message.
 */
$targetField ??= null;
$titleField ??= null;
$fieldLabel ??= null;
$htmlTargetField ??= null;
$htmlFieldLabel ??= __('HTML poster');
$descriptionField ??= $targetField;
$imageUrl ??= null;

$hasFieldMode = $targetField !== null && $titleField !== null && $fieldLabel !== null;
?>
<div
    class="admin-ai-chat"
    data-ai-chat-url="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Assistant', 'action' => 'chat']) ?>"
    data-ai-chat-title-from="<?= h((string)$titleField) ?>"
    data-ai-chat-description-from="<?= h((string)$descriptionField) ?>"
    data-ai-chat-image-url="<?= h((string)$imageUrl) ?>"
    data-ai-chat-use-label="<?= h(__('Use this')) ?>"
>
    <button type="button" class="admin-ai-chat__toggle" aria-label="<?= __('Open Rybárik, the AI assistant') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3C7.02944 3 3 6.58172 3 11c0 2.38 1.19 4.5 3.06 5.94-.1.98-.45 2.2-1.29 3.2-.15.18-.02.46.22.44 1.6-.13 3.24-.72 4.32-1.34.85.2 1.75.31 2.69.31 4.9706 0 9-3.5817 9-8s-4.0294-8-9-8Z"/>
        </svg>
    </button>

    <div class="admin-ai-chat__panel">
        <div class="admin-ai-chat__header">
            <span class="admin-ai-chat__header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3C7.02944 3 3 6.58172 3 11c0 2.38 1.19 4.5 3.06 5.94-.1.98-.45 2.2-1.29 3.2-.15.18-.02.46.22.44 1.6-.13 3.24-.72 4.32-1.34.85.2 1.75.31 2.69.31 4.9706 0 9-3.5817 9-8s-4.0294-8-9-8Z"/>
                </svg>
            </span>
            <span class="admin-ai-chat__header-text">
                <strong><?= __('Rybárik') ?></strong>
                <span><?= $hasFieldMode
                    ? __('Ask me to draft or rewrite the text on this page — pick a field above and I\'ll write a ready-to-use suggestion for it.')
                    : __('Ask me where to find something or how to do it in this admin panel, and I\'ll point you to the right place.') ?></span>
            </span>
            <button type="button" class="admin-ai-chat__close" aria-label="<?= __('Close') ?>">&times;</button>
        </div>

        <?php if ($hasFieldMode): ?>
            <div class="admin-ai-chat__mode-row">
                <button
                    type="button"
                    class="admin-ai-chat__mode"
                    data-mode-target="<?= h($titleField) ?>"
                    data-mode-field-label="<?= h(__('title')) ?>"
                    data-mode-kind="text"
                ><?= __('Title') ?></button>
                <?php if ($targetField !== $titleField): ?>
                    <button
                        type="button"
                        class="admin-ai-chat__mode"
                        data-mode-target="<?= h($targetField) ?>"
                        data-mode-field-label="<?= h($fieldLabel) ?>"
                        data-mode-kind="text"
                    ><?= __('Description') ?></button>
                <?php endif; ?>
                <?php if ($htmlTargetField !== null): ?>
                    <button
                        type="button"
                        class="admin-ai-chat__mode"
                        data-mode-target="<?= h($htmlTargetField) ?>"
                        data-mode-field-label="<?= h($htmlFieldLabel) ?>"
                        data-mode-kind="html"
                    ><?= __('HTML poster') ?></button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="admin-ai-chat__messages"></div>

        <form class="admin-ai-chat__form">
            <textarea
                class="admin-ai-chat__input"
                rows="2"
                placeholder="<?= $hasFieldMode
                    ? h(__('Ask the assistant to draft or edit this text…'))
                    : h(__('Ask where to find or how to do something…')) ?>"
            ></textarea>
            <button type="submit" class="admin-ai-chat__send"><?= __('Send') ?></button>
        </form>
    </div>
</div>
