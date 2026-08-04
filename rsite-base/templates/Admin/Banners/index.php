<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Banner> $banners
 */
$this->assign('title', __('Banners'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add banner'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <table class="table-responsive">
        <thead>
            <tr>
                <th><?= __('Title') ?></th>
                <th><?= __('Location') ?></th>
                <th><?= __('Background') ?></th>
                <th><?= __('Status') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($banners as $banner): ?>
                <tr>
                    <td><?= h($banner->title) ?></td>
                    <td><?= h($banner->location) ?></td>
                    <td><?= $this->Html->image('/img/banners/' . $banner->background, ['alt' => $banner->title, 'width' => 120]) ?></td>
                    <td><?= $banner->is_enabled ? __('Shown') : __('Hidden') ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $banner->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $banner->id],
                            ['confirm' => __('Are you sure you want to delete "{0}"?', $banner->title)],
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>