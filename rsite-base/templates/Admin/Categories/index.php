<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Category> $categories
 */
$this->assign('title', __('Categories'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add category'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Parent category') ?></th>
                    <th><?= __('Show in gallery') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= h($category->title) ?></td>
                        <td><?= h($category->parent_category->title ?? '') ?></td>
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
</div>