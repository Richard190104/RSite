<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Category $category
 * @var array<int, string> $parentOptions
 */
$this->assign('title', __('Add category'));
?>
<div class="content">
    <?= $this->Form->create($category) ?>
        <?= $this->Form->control('title', ['label' => __('Title')]) ?>
        <?= $this->Form->control('parent_id', [
            'type' => 'select',
            'options' => $parentOptions,
            'empty' => __('— none (top-level) —'),
            'label' => __('Parent category'),
        ]) ?>
        <?= $this->Form->control('show_in_gallery', ['label' => __('Show in gallery'), 'checked' => true]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>