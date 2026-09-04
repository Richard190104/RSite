<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\NavbarCategory $category
 * @var iterable<\App\Model\Entity\Page> $allPages
 * @var array<int> $selectedPageIds
 */
$this->assign('title', __('Edit category'));
$this->set('aiChatFields', [
    'targetField' => 'title',
    'titleField' => 'title',
    'fieldLabel' => 'short navbar category title',
]);
?>
<div class="content form-card">
    <?= $this->Form->create($category) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title']) ?>

            <div class="admin-page-picker-field">
                <label><?= __('Pages in this category') ?></label>
                <p class="admin-drag-hint"><?= __('Toggle a page on to include it into category, drag by the handle to reorder') ?></p>
                <ul class="js-drag-reorder-list admin-page-picker" data-page-picker-field="page_ids">
                    <?php foreach ($allPages as $page): ?>
                        <li class="js-drag-reorder-item admin-page-picker__item" draggable="true" data-id="<?= $page->id ?>">
                            <span class="js-drag-reorder-handle" title="<?= h(__('Drag to reorder')) ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                                    <circle cx="8" cy="6" r="1.2" fill="currentColor" stroke="none"/>
                                    <circle cx="8" cy="12" r="1.2" fill="currentColor" stroke="none"/>
                                    <circle cx="8" cy="18" r="1.2" fill="currentColor" stroke="none"/>
                                    <circle cx="16" cy="6" r="1.2" fill="currentColor" stroke="none"/>
                                    <circle cx="16" cy="12" r="1.2" fill="currentColor" stroke="none"/>
                                    <circle cx="16" cy="18" r="1.2" fill="currentColor" stroke="none"/>
                                </svg>
                            </span>
                            <span class="admin-page-picker__title"><?= h($page->title) ?></span>
                            <input
                                type="checkbox"
                                name="page_ids[]"
                                value="<?= $page->id ?>"
                                <?= in_array($page->id, $selectedPageIds, true) ? 'checked' : '' ?>
                            >
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
<?= $this->Html->script('admin-drag-reorder') ?>
<?= $this->Html->script('admin-page-picker') ?>