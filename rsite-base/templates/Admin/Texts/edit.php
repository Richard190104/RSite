<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Text $text
 */
$this->assign('title', __(ucfirst($text->slug)));
?>
<div class="content">
    <?= $this->Form->create($text) ?>
        <?= $this->Form->control('value', ['label' => __('Value')]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>