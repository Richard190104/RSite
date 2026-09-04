<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Category> $categories Top-level categories, each with `child_categories` eager-loaded (both ordered by position).
 */
$this->assign('title', __('Categories'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add category'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <p class="admin-drag-hint"><?= __('Drag rows by the handle to reorder — this is the order categories (and their subcategories) appear in the public gallery.') ?></p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Show in gallery') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody
                class="js-drag-reorder-list"
                data-drag-reorder-url="<?= $this->Url->build(['action' => 'reorder']) ?>"
                data-drag-reorder-parent-id=""
            >
                <?php foreach ($categories as $category): ?>
                    <tr class="js-drag-reorder-item" draggable="true" data-id="<?= $category->id ?>">
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
                        <td><?= h($category->title) ?></td>
                        <td><?= $category->show_in_gallery ? __('Yes') : __('No') ?></td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['action' => 'edit', $category->id],
                                'deleteUrl' => ['action' => 'delete', $category->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $category->title),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($categories as $category): ?>
        <?php if (!empty($category->child_categories)): ?>
            <h2 class="admin-subsection-title"><?= h(__('Subcategories of "{0}"', $category->title)) ?></h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th><?= __('Title') ?></th>
                            <th><?= __('Show in gallery') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody
                        class="js-drag-reorder-list"
                        data-drag-reorder-url="<?= $this->Url->build(['action' => 'reorder']) ?>"
                        data-drag-reorder-parent-id="<?= $category->id ?>"
                    >
                        <?php foreach ($category->child_categories as $child): ?>
                            <tr class="js-drag-reorder-item" draggable="true" data-id="<?= $child->id ?>">
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
                                <td><?= h($child->title) ?></td>
                                <td><?= $child->show_in_gallery ? __('Yes') : __('No') ?></td>
                                <td class="actions">
                                    <?= $this->element('Admin/rowActions', [
                                        'editUrl' => ['action' => 'edit', $child->id],
                                        'deleteUrl' => ['action' => 'delete', $child->id],
                                        'confirmMessage' => __('Are you sure you want to delete "{0}"?', $child->title),
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
