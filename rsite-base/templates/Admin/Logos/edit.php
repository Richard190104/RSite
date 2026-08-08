<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Logo $logo
 */
$this->assign('title', __($logo->name));
?>
<div class="content">
    <?php if (!empty($logo->path)): ?>
        <?= $this->Html->image('/img/logos/' . $logo->path, ['alt' => $logo->name, 'width' => 240]) ?>
    <?php endif; ?>
    <?= $this->Form->create($logo, ['type' => 'file']) ?>
        <?= $this->Form->control('path', [
            'type' => 'file',
            'label' => empty($logo->path) ? __('Upload image') : __('Replace image'),
        ]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>