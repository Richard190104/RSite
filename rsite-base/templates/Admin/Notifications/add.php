<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Notification $notification
 */
$this->assign('title', __('Add notification'));
?>
<div class="content">
    <?= $this->Form->create($notification, ['type' => 'file']) ?>
        <?= $this->Form->control('title', ['label' => __('Title')]) ?>
        <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description')]) ?>
        <?= $this->Form->control('valid_from', ['label' => __('Valid from')]) ?>
        <?= $this->Form->control('valid_to', ['label' => __('Valid to')]) ?>
        <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Image')]) ?>
        <?= $this->Form->control('settings.is_active', ['type' => 'checkbox', 'label' => __('Is active'), 'checked' => true]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>
