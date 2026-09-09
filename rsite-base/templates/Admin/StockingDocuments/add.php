<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\StockingDocument $stockingDocument
 */
$this->assign('title', __('Add stocking document'));
?>
<div class="content form-card">
    <?= $this->Form->create($stockingDocument, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('file', [
                'type' => 'file',
                'label' => __('PDF file'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
