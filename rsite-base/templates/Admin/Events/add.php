<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var array<int, string> $categories
 */
$this->assign('title', __('Add event'));
$this->set('aiChatFields', [
    'targetField' => 'description',
    'titleField' => 'title',
    'fieldLabel' => 'short event description',
    'htmlTargetField' => 'content',
    'htmlFieldLabel' => 'HTML poster for the event',
]);
?>
<div class="content form-card">
    <?= $this->Form->create($event, ['type' => 'file']) ?>
        <div class="form-grid">
            <?= $this->Form->control('title', ['label' => __('Title'), 'id' => 'title', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('description', ['type' => 'textarea', 'label' => __('Description'), 'id' => 'description', 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('date', ['label' => __('Date')]) ?>
            <?= $this->Form->control('time', ['label' => __('Time'), 'placeholder' => '08:00']) ?>
            <?= $this->Form->control('location', ['label' => __('Location'), 'container' => ['class' => 'form-grid__full']]) ?>
            <?= $this->Form->control('category_id', [
                'type' => 'select',
                'options' => $categories,
                'empty' => __('— none —'),
                'label' => __('Category'),
            ]) ?>
            <?= $this->Form->control('image', ['type' => 'file', 'label' => __('Image (optional)'), 'container' => ['class' => 'form-grid__full']]) ?>

            <div class="form-grid__full">
                <?= $this->Form->control('content', [
                    'type' => 'textarea',
                    'label' => __('Poster (HTML)'),
                    'id' => 'content',
                ]) ?>
                <button
                    type="button"
                    class="button admin-html-preview-toggle"
                    data-html-preview-toggle-for="content"
                    data-html-preview-title="<?= h(__('Poster preview')) ?>"
                ><?= __('Show preview') ?></button>
            </div>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>
<?= $this->Html->script('admin-html-preview') ?>
