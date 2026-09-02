<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Notification $notification
 */
$this->assign('title', __('Add notification'));
$this->set('aiChatFields', [
    'targetField' => 'description',
    'titleField' => 'title',
    'fieldLabel' => 'short site notification message',
]);
?>
<div class="content form-card">
    <?= $this->Form->create($notification, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description'), 'id' => 'description', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('valid_from', ['label' => __('Valid from')]) ?>
            <?= $this->Form->control('valid_to', ['label' => __('Valid to')]) ?>
            <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Image'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('settings.is_active', ['type' => 'checkbox', 'label' => __('Is active'), 'checked' => true]) ?>
            <?= $this->Form->control('settings.show_as_popup', ['type' => 'checkbox', 'label' => __('Show as popup')]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>