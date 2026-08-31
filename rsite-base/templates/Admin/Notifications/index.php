<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Notification> $notifications
 */
$this->assign('title', __('Notifications'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add notification'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Image') ?></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Valid from') ?></th>
                    <th><?= __('Valid to') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td><?= $this->Html->image('/img/notifications/' . $notification->image, ['alt' => $notification->title, 'width' => 80]) ?></td>
                        <td><?= h($notification->title) ?></td>
                        <td><?= h($notification->valid_from->format('d.m.Y')) ?></td>
                        <td><?= h($notification->valid_to->format('d.m.Y')) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $notification->id]) ?>
                            <?= $this->Form->postLink(
                                __('Delete'),
                                ['action' => 'delete', $notification->id],
                                ['confirm' => __('Are you sure you want to delete "{0}"?', $notification->title)],
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
