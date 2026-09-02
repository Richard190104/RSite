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
<div class="content form-card">
    <h1><?= h(__($page->title)) ?></h1>
    <input type="hidden" id="page-title" value="<?= h(__($page->title)) ?>">
    <?= $this->Form->create($page) ?>
        <div class="form-grid">
            <?= $this->Form->control('content.description', [
                'type' => 'textarea',
                'rows' => 3,
                'label' => __('Description'),
                'value' => $description,
                'id' => 'content-description',
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>

    <p><?= __('Shown under the page name in the homepage quick access cards.') ?></p>
</div>