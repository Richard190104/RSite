<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Banner $banner
 * @var array<string, string> $locations
 */
$this->assign('title', __('Add banner'));
?>
<div class="content">
    <?= $this->Form->create($banner, ['type' => 'file']) ?>
        <?= $this->Form->control('title', ['label' => __('Title')]) ?>
        <?= $this->Form->control('settings.subtitle', ['label' => __('Subtitle')]) ?>
        <?= $this->Form->control('location', ['type' => 'select', 'options' => $locations, 'label' => __('Location')]) ?>
        <?= $this->Form->control('background', ['type' => 'file', 'label' => __('Background image')]) ?>
        <?= $this->Form->control('is_enabled', ['label' => __('Show on the page'), 'checked' => true]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>