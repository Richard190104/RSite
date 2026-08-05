<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Gallery $photo
 * @var array<int, string> $categories
 */
$this->assign('title', __('Edit photo'));
?>
<div class="content">
    <?= $this->Html->image('/img/galleries/' . $photo->image, ['alt' => '', 'width' => 240]) ?>
    <?= $this->Form->create($photo, ['type' => 'file']) ?>
        <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Replace image (optional)')]) ?>
        <?= $this->Form->control('category_id', [
            'type' => 'select',
            'options' => $categories,
            'empty' => __('— none —'),
            'label' => __('Category'),
        ]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>