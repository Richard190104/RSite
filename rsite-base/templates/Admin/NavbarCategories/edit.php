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
$pageOptions = [];
foreach ($allPages as $page) {
    $pageOptions[$page->id] = $page->title;
}
?>
<div class="content form-card">
    <?= $this->Form->create($category) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>

            <?= $this->Form->control('page_ids', [
                'type' => 'select',
                'multiple' => 'checkbox',
                'label' => __('Pages in this category'),
                'options' => $pageOptions,
                'value' => $selectedPageIds,
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>