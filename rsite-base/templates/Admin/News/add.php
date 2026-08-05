<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\News $article
 * @var array<int, string> $categories
 */
$this->assign('title', __('Add article'));
?>
<div class="content">
    <?= $this->Form->create($article, ['type' => 'file']) ?>
        <?= $this->Form->control('title', ['label' => __('Title')]) ?>
        <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description')]) ?>
        <?= $this->Form->control('date', ['label' => __('Date')]) ?>
        <?= $this->Form->control('category_id', [
            'type' => 'select',
            'options' => $categories,
            'empty' => __('— none —'),
            'label' => __('Category'),
        ]) ?>
        <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Image')]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>