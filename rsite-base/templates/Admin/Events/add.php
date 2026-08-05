<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var array<int, string> $categories
 */
$this->assign('title', __('Add event'));
?>
<div class="content">
    <?= $this->Form->create($event) ?>
        <?= $this->Form->control('title', ['label' => __('Title')]) ?>
        <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description')]) ?>
        <?= $this->Form->control('date', ['label' => __('Date')]) ?>
        <?= $this->Form->control('category_id', [
            'type' => 'select',
            'options' => $categories,
            'empty' => __('— none —'),
            'label' => __('Category'),
        ]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>