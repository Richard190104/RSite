<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var array<int, string> $otherPages
 */
$this->assign('title', __('Edit homepage'));
$aboutUsText = $page->content['about_us_text'] ?? '';
$quickAccess = $page->content['quick_access'] ?? [];
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
            <div class="form-grid__full admin-multicheckbox">
                <?= $this->Form->control('content.quick_access', [
                    'type' => 'select',
                    'multiple' => 'checkbox',
                    'label' => __('Quick access (max 6 pages)'),
                    'options' => $otherPages,
                    'value' => $quickAccess,
                ]) ?>
            </div>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>

    <div class="form-card__hints">
        <p class="form-card__hint">
            <?= __('The "about us" feature tiles are managed separately as banners with the "Home — mini banner" location.') ?>
            <?= $this->Html->link(__('Manage banners'), ['prefix' => 'Admin', 'controller' => 'Banners', 'action' => 'index']) ?>
        </p>
        <p class="form-card__hint">
            <?= __('The "fishing grounds and activities" section is also managed separately as banners: the main photo uses the "Home — fishing grounds main image" location, and its cards use the "Home — fishing grounds tile" location.') ?>
            <?= $this->Html->link(__('Manage banners'), ['prefix' => 'Admin', 'controller' => 'Banners', 'action' => 'index']) ?>
        </p>
    </div>
</div>