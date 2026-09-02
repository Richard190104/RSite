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
<div class="content form-card">
    <div class="form-card__preview">
        <?= $this->Html->image('/img/notifications/' . $notification->image, ['alt' => $notification->title, 'width' => 240]) ?>
    </div>
    <?= $this->Form->create($notification, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description'), 'id' => 'description', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('valid_from', ['label' => __('Valid from')]) ?>
            <?= $this->Form->control('valid_to', ['label' => __('Valid to')]) ?>
            <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Replace image (optional)'), 'container' => ['class' => 'form-grid__full']]) ?>
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
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>