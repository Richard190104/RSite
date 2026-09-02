<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Banner $banner
 * @var array<string, string> $locations
 */
$this->assign('title', __('Add banner'));
$this->set('aiChatFields', [
    'targetField' => 'settings-subtitle',
    'titleField' => 'title',
    'fieldLabel' => 'short banner subtitle',
]);
?>
<div class="content form-card">
    <?= $this->Form->create($banner, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('settings.subtitle', ['label' => __('Subtitle'), 'id' => 'settings-subtitle', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('location', ['type' => 'select', 'options' => $locations, 'label' => __('Location')]) ?>
            <?= $this->Form->control('background', ['type' => 'file', 'label' => __('Background image')]) ?>
            <?= $this->Form->control('is_enabled', ['label' => __('Show on the page'), 'checked' => true, 'container' => ['class' => 'form-grid__full']]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>