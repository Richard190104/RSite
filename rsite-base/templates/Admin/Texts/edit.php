<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Text $text
 */
$this->assign('title', __(ucfirst($text->slug)));
?>
<div class="content form-card">
    <?= $this->Form->create($text) ?>
        <div class="form-grid">
            <?= $this->Form->control('value', ['label' => __('Value'), 'container' => ['class' => 'form-grid__full']]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>