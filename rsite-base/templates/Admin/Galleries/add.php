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
            <?= $this->Form->control('image', [
                'type' => 'file',
                'multiple' => true,
                'name' => 'image[]',
                'label' => __('Images'),
            ]) ?>
            <p><?= __('Select multiple files to add several photos at once — they will all be saved under the category picked below.') ?></p>
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