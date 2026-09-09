<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Fee $fee
 * @var array<string, string> $categoryOptions
 */
$this->assign('title', __('Edit fee'));
?>
<div class="content form-card">
    <?= $this->Form->create($fee) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('price', ['label' => __('Price')]) ?>
            <?= $this->Form->control('category', [
                'type' => 'select',
                'options' => $categoryOptions,
                'empty' => __('— select —'),
                'label' => __('Category'),
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
