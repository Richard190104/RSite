<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Logo $logo
 */
$this->assign('title', __($logo->name));
?>
<div class="content form-card">
    <?php if (!empty($logo->path)): ?>
        <div class="form-card__preview">
            <?= $this->Html->image('/img/logos/' . $logo->path, ['alt' => $logo->name, 'width' => 240]) ?>
        </div>
    <?php endif; ?>
    <?= $this->Form->create($logo, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('path', [
                'type' => 'file',
                'label' => empty($logo->path) ? __('Upload image') : __('Replace image'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>