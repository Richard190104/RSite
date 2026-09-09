<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var array<string, \App\Model\Entity\Fee[]> $feesByCategory Fee rows keyed by FeesTable::CATEGORIES key, one entry per category (possibly empty) in that fixed order.
 *
 * "Poplatky" is managed entirely from here rather than as its own sidebar
 * entry — Fees still has a full CRUD controller (Admin\FeesController),
 * this page just lists the fees (grouped by the fixed FeesTable::CATEGORIES,
 * same as the public page) and links into that controller's add/edit/delete
 * actions. Each category's rows are drag-to-reorder (see
 * webroot/js/admin-drag-reorder.js), scoped to that category so dragging
 * never moves a fee into a different category's order.
 */
$this->assign('title', __('Edit "Poplatky" page'));
$description = $page->content['description'] ?? '';
$notice = $page->content['notice'] ?? '';
$categoryLabels = \App\Model\Table\FeesTable::CATEGORIES;
?>
<div class="content form-card">
    <?= $this->Form->create($page) ?>
        <div class="form-grid">
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
</div>

<div class="content form-card">
    <?= $this->Form->create($page) ?>
        <div>
            <?= $this->Form->control('content.notice', [
                'type' => 'textarea',
                'label' => __('Additional information (shown below the fee tables)'),
                'value' => $notice,
                'class' => 'js-wysiwyg c-card__wysiwyg',
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>

<div class="content" style="margin-top: 2rem;">
    <p>
        <?= $this->Html->link(
            __('Add fee'),
            ['prefix' => 'Admin', 'controller' => 'Fees', 'action' => 'add'],
            ['class' => 'button'],
        ) ?>
    </p>
    <p class="admin-drag-hint"><?= __('Drag rows by the handle to reorder — this is the order fees appear within their category on the public page.') ?></p>

    <?php foreach ($feesByCategory as $categoryKey => $fees): ?>
        <h2 class="admin-subsection-title"><?= h($categoryLabels[$categoryKey] ?? $categoryKey) ?></h2>
        <?php if (!$fees): ?>
            <p><?= __('No fees in this category yet.') ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th><?= __('Title') ?></th>
                            <th><?= __('Price') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody
                        class="js-drag-reorder-list"
                        data-drag-reorder-url="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Fees', 'action' => 'reorder']) ?>"
                        data-drag-reorder-parent-id="<?= h($categoryKey) ?>"
                    >
                        <?php foreach ($fees as $fee): ?>
                            <tr class="js-drag-reorder-item" draggable="true" data-id="<?= $fee->id ?>">
                                <td class="js-drag-reorder-handle" title="<?= h(__('Drag to reorder')) ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                                        <circle cx="8" cy="6" r="1.2" fill="currentColor" stroke="none"/>
                                        <circle cx="8" cy="12" r="1.2" fill="currentColor" stroke="none"/>
                                        <circle cx="8" cy="18" r="1.2" fill="currentColor" stroke="none"/>
                                        <circle cx="16" cy="6" r="1.2" fill="currentColor" stroke="none"/>
                                        <circle cx="16" cy="12" r="1.2" fill="currentColor" stroke="none"/>
                                        <circle cx="16" cy="18" r="1.2" fill="currentColor" stroke="none"/>
                                    </svg>
                                </td>
                                <td><?= h($fee->title) ?></td>
                                <td><?= h($fee->price) ?></td>
                                <td class="actions">
                                    <?= $this->element('Admin/rowActions', [
                                        'editUrl' => ['prefix' => 'Admin', 'controller' => 'Fees', 'action' => 'edit', $fee->id],
                                        'deleteUrl' => ['prefix' => 'Admin', 'controller' => 'Fees', 'action' => 'delete', $fee->id],
                                        'confirmMessage' => __('Are you sure you want to delete "{0}"?', $fee->title),
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?= $this->Html->script('admin-drag-reorder') ?>
<?= $this->Html->script('vendor/tinymce/tinymce.min') ?>
<?= $this->Html->script('admin-wysiwyg') ?>
