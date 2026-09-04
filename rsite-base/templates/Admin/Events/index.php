<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Event> $events
 */
$this->assign('title', __('Events'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add event'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Image') ?></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Date') ?></th>
                    <th><?= __('Location') ?></th>
                    <th><?= __('Category') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td>
                            <?php if ($event->image): ?>
                                <?= $this->Html->image('/img/events/' . $event->image, ['alt' => $event->title, 'width' => 80]) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= h($event->title) ?></td>
                        <td><?= $event->date !== null ? h($event->date->format('d.m.Y')) : '' ?></td>
                        <td><?= h($event->location ?? '') ?></td>
                        <td><?= h($event->category->title ?? '') ?></td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['action' => 'edit', $event->id],
                                'deleteUrl' => ['action' => 'delete', $event->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $event->title),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>