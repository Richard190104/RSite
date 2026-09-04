<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 */
$this->assign('title', __('Edit "About us" page'));
$aboutUsText = $page->content['about_us_text'] ?? '';
$description = $page->content['description'] ?? '';
?>
<div class="content form-card">
    <?= $this->Form->create($page) ?>
        <div class="form-grid">
            <?= $this->Form->control('content.about_us_text', [
                'type' => 'textarea',
                'label' => __('About us text'),
                'value' => $aboutUsText,
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
            <?= $this->Form->control('content.description', [
                'type' => 'textarea',
                'rows' => 3,
                'label' => __('Description'),
                'value' => $description,
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>

    <div class="form-card__hints">
        <p class="form-card__hint">
            <?= __('The image shown next to the "about us" text is managed separately as a banner with the "About us — minibanner" location.') ?>
            <?= $this->Html->link(__('Manage banners'), ['prefix' => 'Admin', 'controller' => 'Banners', 'action' => 'index']) ?>
        </p>
    </div>
</div>
