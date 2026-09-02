<?php
/**
 * Icon-only Edit/Delete actions for an admin listing row — replaces the
 * plain text "Edit"/"Delete" links with pencil/trash icons so listings read
 * as a compact action column instead of two links of varying width.
 *
 * $deleteUrl/$confirmMessage are optional — omit both for a record that
 * can't be deleted (Logos, Pages, Texts: fixed rows, edit-only).
 *
 * @var \App\View\AppView $this
 * @var array<string, mixed> $editUrl
 * @var array<string, mixed>|null $deleteUrl
 * @var string|null $confirmMessage
 */
?>
<?= $this->Html->link(
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
    $editUrl,
    ['escape' => false, 'class' => 'row-action row-action--edit', 'title' => __('Edit')],
) ?>
<?php if (isset($deleteUrl)): ?>
    <?= $this->Form->postLink(
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>',
        $deleteUrl,
        ['escapeTitle' => false, 'confirm' => $confirmMessage ?? null, 'class' => 'row-action row-action--delete', 'title' => __('Delete')],
    ) ?>
<?php endif; ?>
