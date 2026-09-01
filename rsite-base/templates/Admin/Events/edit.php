<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var array<int, string> $categories
 */
$this->assign('title', __('Edit event'));
$this->set('aiChatFields', [
    'targetField' => 'description',
    'titleField' => 'title',
    'fieldLabel' => 'short event description',
]);
?>
<div class="content">
    <?= $this->Form->create($event) ?>
        <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title']) ?>
        <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description'), 'id' => 'description']) ?>
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