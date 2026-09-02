<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Gallery $photo
 * @var array<int, string> $categories
 */
$this->assign('title', __('Add photo'));
?>
<div class="content form-card">
    <?= $this->Form->create($photo, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Image')]) ?>
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