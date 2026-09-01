<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Notification $notification
 */
$this->assign('title', __('Edit notification'));
$this->set('aiChatFields', [
    'targetField' => 'description',
    'titleField' => 'title',
    'fieldLabel' => 'short site notification message',
]);
?>
<div class="content">
    <?= $this->Html->image('/img/notifications/' . $notification->image, ['alt' => $notification->title, 'width' => 240]) ?>
    <?= $this->Form->create($notification, ['type' => 'file']) ?>
        <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title']) ?>
        <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description'), 'id' => 'description']) ?>
        <?= $this->Form->control('valid_from', ['label' => __('Valid from')]) ?>
        <?= $this->Form->control('valid_to', ['label' => __('Valid to')]) ?>
        <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Replace image (optional)')]) ?>
        <?= $this->Form->control('settings.is_active', [
            'type' => 'checkbox',
            'label' => __('Is active'),
            'checked' => (bool)($notification->settings['is_active'] ?? true),
        ]) ?>
        <?= $this->Form->control('settings.show_as_popup', [
            'type' => 'checkbox',
            'label' => __('Show as popup'),
            'checked' => (bool)($notification->settings['show_as_popup'] ?? false),
        ]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>