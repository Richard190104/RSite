<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
$this->assign('title', h(__($page->title)));
$description = $page->content['description'] ?? '';
$this->set('aiChatFields', [
    'targetField' => 'content-description',
    'titleField' => 'page-title',
    'fieldLabel' => 'short page teaser description',
]);
?>
<div class="content">
    <h1><?= h(__($page->title)) ?></h1>
    <input type="hidden" id="page-title" value="<?= h(__($page->title)) ?>">
    <?= $this->Form->create($page) ?>
        <?= $this->Form->control('content.description', [
            'type' => 'textarea',
            'rows' => 3,
            'label' => __('Description'),
            'value' => $description,
            'id' => 'content-description',
        ]) ?>

        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>

    <p><?= __('Shown under the page name in the homepage quick access cards.') ?></p>
</div>