<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\News $article
 * @var array<int, string> $categories
 */
$this->assign('title', __('Edit article'));
$this->set('aiChatFields', [
    'targetField' => 'description',
    'titleField' => 'title',
    'fieldLabel' => 'short news article summary',
    'htmlTargetField' => 'content',
    'htmlFieldLabel' => 'HTML poster for the news article',
    'imageUrl' => $this->Url->build('/img/news/' . $article->image, ['fullBase' => true]),
]);
?>
<div class="content">
    <?= $this->Html->image('/img/news/' . $article->image, ['alt' => $article->title, 'width' => 240]) ?>
    <?= $this->Form->create($article, ['type' => 'file']) ?>
        <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title']) ?>
        <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description'), 'id' => 'description']) ?>

        <?= $this->Form->control('content', [
            'type' => 'textarea',
            'label' => __('Poster (HTML)'),
            'id' => 'content',
        ]) ?>
        <button
            type="button"
            class="button admin-html-preview-toggle"
            data-html-preview-toggle-for="content"
            data-html-preview-title="<?= h(__('Poster preview')) ?>"
        ><?= __('Show preview') ?></button>

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
<?= $this->Html->script('admin-html-preview') ?>