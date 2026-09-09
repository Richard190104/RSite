<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CatchDocument $catchDocument
 */
$this->assign('title', __('Edit catch document'));
?>
<div class="content form-card">
    <?php if ($catchDocument->file): ?>
        <div class="form-card__preview">
            <?= $this->Html->link(__('Current PDF file'), '/files/catch-documents/' . $catchDocument->file, ['target' => '_blank']) ?>
        </div>
    <?php endif; ?>
    <?= $this->Form->create($catchDocument, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('file', [
                'type' => 'file',
                'label' => __('Replace PDF file (optional)'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
