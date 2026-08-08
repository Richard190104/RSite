<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
$this->assign('title', h(__($page->title)));
$description = $page->content['description'] ?? '';
?>
<div class="content">
    <h1><?= h(__($page->title)) ?></h1>
    <?= $this->Form->create($page) ?>
        <?= $this->Form->control('content.description', [
            'type' => 'textarea',
            'rows' => 3,
            'label' => __('Description'),
            'value' => $description,
        ]) ?>

        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>

    <p><?= __('Shown under the page name in the homepage quick access cards.') ?></p>
</div>