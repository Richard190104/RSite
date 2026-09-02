<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Category $category
 * @var array<int, string> $parentOptions
 */
$this->assign('title', __('Edit category'));
$this->set('aiChatFields', [
    'targetField' => 'title',
    'titleField' => 'title',
    'fieldLabel' => 'short category title',
]);
?>
<div class="content form-card">
    <?= $this->Form->create($category) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('parent_id', [
                'type' => 'select',
                'options' => $parentOptions,
                'empty' => __('— none (top-level) —'),
                'label' => __('Parent category'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
            <?= $this->Form->control('show_in_gallery', ['label' => __('Show in gallery'), 'container' => ['class' => 'form-grid__full']]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>