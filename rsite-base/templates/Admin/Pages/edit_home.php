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
<div class="content">
    <?= $this->Form->create($page) ?>
        <?= $this->Form->control('content.about_us_text', [
            'type' => 'textarea',
            'label' => __('About us text'),
            'value' => $aboutUsText,
        ]) ?>

        <?= $this->Form->control('content.quick_access', [
            'type' => 'select',
            'multiple' => 'checkbox',
            'label' => __('Quick access (max 6 pages)'),
            'options' => $otherPages,
            'value' => $quickAccess,
        ]) ?>

        <?= $this->Form->button(__('Save')) ?>
    <?= $this->Form->end() ?>

    <p>
        <?= __('The "about us" feature tiles are managed separately as banners with the "Home — mini banner" location.') ?>
        <?= $this->Html->link(__('Manage banners'), ['prefix' => 'Admin', 'controller' => 'Banners', 'action' => 'index']) ?>
    </p>

    <p>
        <?= __('The "fishing grounds and activities" section is also managed separately as banners: the main photo uses the "Home — fishing grounds main image" location, and its cards use the "Home — fishing grounds tile" location.') ?>
        <?= $this->Html->link(__('Manage banners'), ['prefix' => 'Admin', 'controller' => 'Banners', 'action' => 'index']) ?>
    </p>
</div>