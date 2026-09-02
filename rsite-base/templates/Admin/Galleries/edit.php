<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Gallery $photo
 * @var array<int, string> $categories
 */
$this->assign('title', __('Edit photo'));
?>
<div class="content form-card">
    <div class="form-card__preview">
        <?= $this->Html->image($photo->image, ['alt' => '', 'width' => 240]) ?>
    </div>
    <?= $this->Form->create($photo, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Replace image (optional)')]) ?>
            <?= $this->Form->control('text', [
                'label' => __('Caption'),
                'maxlength' => 80,
                'placeholder' => __('Shown instead of the category name on the public gallery'),
            ]) ?>
            <?= $this->Form->control('category_id', [
                'type' => 'select',
                'options' => $categories,
                'empty' => __('— none —'),
                'label' => __('Category'),
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>