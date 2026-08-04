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
    <table class="table-responsive">
        <thead>
            <tr>
                <th><?= __('Title') ?></th>
                <th><?= __('Pages') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= h($category->title) ?></td>
                    <td><?= count($category->pages) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $category->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $category->id],
                            ['confirm' => __('Are you sure you want to delete "{0}"?', $category->title)],
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>