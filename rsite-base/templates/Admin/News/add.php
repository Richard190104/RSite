<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\News $article
 * @var array<int, string> $categories
 */
$this->assign('title', __('Add article'));
$this->set('aiChatFields', [
    'targetField' => 'description',
    'titleField' => 'title',
    'fieldLabel' => 'short news article summary',
    'htmlTargetField' => 'content',
    'htmlFieldLabel' => 'HTML poster for the news article',
]);
?>
<div class="content form-card">
    <?= $this->Form->create($article, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description'), 'id' => 'description', 'container' => ['class' => 'form-grid__full']]) ?>

            <?= $this->Form->control('content', [
                'type' => 'textarea',
                'label' => __('Poster (HTML)'),
                'id' => 'content',
                'class' => 'js-wysiwyg',
                'container' => ['class' => 'form-grid__full'],
            ]) ?>

            <?= $this->Form->control('date', ['label' => __('Date')]) ?>
            <?= $this->Form->control('category_id', [
                'type' => 'select',
                'options' => $categories,
                'empty' => __('— none —'),
                'label' => __('Category'),
            ]) ?>
            <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Image (optional)'), 'container' => ['class' => 'form-grid__full']]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
<?= $this->Html->script('vendor/tinymce/tinymce.min') ?>
<?= $this->Html->script('admin-wysiwyg') ?>