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
                    <th></th>
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
                        <td>
                            <?= $this->Form->create(null, [
                                'url' => ['action' => 'toggleActive', $notification->id],
                                'class' => 'notification-toggle-form',
                            ]) ?>
                                <?= $this->Form->checkbox('is_active', [
                                    'checked' => (bool)($notification->settings['is_active'] ?? true),
                                    'class' => 'js-toggle-checkbox',
                                    'title' => __('Is active'),
                                ]) ?>
                            <?= $this->Form->end() ?>
                        </td>
                        <td><?= $this->Html->image('/img/notifications/' . $notification->image, ['alt' => $notification->title, 'width' => 80]) ?></td>
                        <td><?= h($notification->title) ?></td>
                        <td><?= h($notification->valid_from->format('d.m.Y')) ?></td>
                        <td><?= h($notification->valid_to->format('d.m.Y')) ?></td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['action' => 'edit', $notification->id],
                                'deleteUrl' => ['action' => 'delete', $notification->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $notification->title),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
