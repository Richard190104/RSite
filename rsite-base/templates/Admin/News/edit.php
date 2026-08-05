<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\News $article
 * @var array<int, string> $categories
 */
$this->assign('title', __('Edit article'));
?>
<div class="content">
    <?= $this->Html->image('/img/news/' . $article->image, ['alt' => $article->title, 'width' => 240]) ?>
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
        <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Replace image (optional)')]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>