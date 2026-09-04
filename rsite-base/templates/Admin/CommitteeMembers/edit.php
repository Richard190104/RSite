<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CommitteeMember $committeeMember
 */
$this->assign('title', __('Edit committee member'));
?>
<div class="content form-card">
    <?php if ($committeeMember->photo): ?>
        <div class="form-card__preview">
            <?= $this->Html->image('/img/committee/' . $committeeMember->photo, ['alt' => $committeeMember->name, 'width' => 160]) ?>
        </div>
    <?php endif; ?>
    <?= $this->Form->create($committeeMember, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('name', ['label' => __('Name'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('role', ['label' => __('Role (optional)'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('section', [
                'label' => __('Section'),
                'placeholder' => __('e.g. Committee members, Audit committee'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
            <?= $this->Form->control('phone', ['label' => __('Phone (optional)')]) ?>
            <?= $this->Form->control('email', ['label' => __('Email (optional)')]) ?>
            <?= $this->Form->control('photo', [
                'type' => 'file',
                'label' => $committeeMember->photo ? __('Replace photo (optional)') : __('Photo (optional)'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
