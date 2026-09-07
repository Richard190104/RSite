<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FishingGround $fishingGround
 */
$this->assign('title', __('Add fishing ground'));
?>
<div class="content form-card">
    <?= $this->Form->create($fishingGround, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('registration_number', ['label' => __('Registration number'), 'placeholder' => 'č. 2-2840-1-1']) ?>
            <?= $this->Form->control('type', ['label' => __('Type'), 'placeholder' => 'Vodná nádrž · Kaprový revír']) ?>
            <?= $this->Form->control('description', [
                'type' => 'textarea',
                'label' => __('Description'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
            <?= $this->Form->control('location', ['label' => __('Location'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('position_x', [
                'type' => 'number',
                'label' => __('Map position — X (%)'),
                'min' => 0,
                'max' => 100,
                'step' => 'any',
                'help' => __('0 = left edge, 100 = right edge of the fishing grounds map.'),
            ]) ?>
            <?= $this->Form->control('position_y', [
                'type' => 'number',
                'label' => __('Map position — Y (%)'),
                'min' => 0,
                'max' => 100,
                'step' => 'any',
                'help' => __('0 = top edge, 100 = bottom edge of the fishing grounds map.'),
            ]) ?>
            <?= $this->Form->control('image', [
                'type' => 'file',
                'label' => __('Image'),
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
