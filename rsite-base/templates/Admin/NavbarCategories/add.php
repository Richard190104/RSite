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
<div class="content form-card">
    <?= $this->Form->create($category) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>