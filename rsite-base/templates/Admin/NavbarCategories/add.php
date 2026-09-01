<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\NavbarCategory $category
 */
$this->assign('title', __('Add category'));
$this->set('aiChatFields', [
    'targetField' => 'title',
    'titleField' => 'title',
    'fieldLabel' => 'short navbar category title',
]);
?>
<div class="content">
    <?= $this->Form->create($category) ?>
        <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title']) ?>
        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>
</div>