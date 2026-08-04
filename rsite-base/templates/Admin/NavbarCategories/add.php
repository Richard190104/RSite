<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\NavbarCategory $category
 */
$this->assign('title', __('Add category'));
?>
<div class="content">
    <?= $this->Form->create($category) ?>
        <?= $this->Form->control('title', ['label' => __('Title')]) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>