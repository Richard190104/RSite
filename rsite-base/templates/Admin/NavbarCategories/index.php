<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\NavbarCategory> $categories
 */
$this->assign('title', __('Navbar categories'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add category'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <p class="admin-drag-hint"><?= __('Drag rows by the handle to reorder — this is the order categories appear in the site navigation.') ?></p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Pages') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody
                class="js-drag-reorder-list"
                data-drag-reorder-url="<?= $this->Url->build(['action' => 'reorder']) ?>"
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
                        <td><?= count($category->pages) ?></td>
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
</div>
<?= $this->Html->script('admin-drag-reorder') ?>