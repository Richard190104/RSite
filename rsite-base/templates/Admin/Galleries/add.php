<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Gallery $photo
 * @var array<int, string> $categories
 */
$this->assign('title', __('Add photo'));
?>
<div class="content">
    <?= $this->Form->create($photo, ['type' => 'file']) ?>
        <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Image')]) ?>
        <?= $this->Form->control('category_id', [
            'type' => 'select',
            'options' => $categories,
            'empty' => __('— none —'),
            'label' => __('Category'),
        ]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>