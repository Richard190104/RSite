<?php
/**
 * @var \App\View\AppView $this
 * @var string $extraClass Component-specific class alongside .modal-close-btn (for scoping/overrides only — the shared class carries all the actual styling).
 */
$extraClass ??= '';
?>
<button type="button" class="modal-close-btn<?= $extraClass !== '' ? ' ' . h($extraClass) : '' ?>" aria-label="<?= __('Close') ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
</button>
