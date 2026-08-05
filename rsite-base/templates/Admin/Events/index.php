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
                    <th><?= __('Title') ?></th>
                    <th><?= __('Category') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= h($event->title) ?></td>
                        <td><?= h($event->category->title ?? '') ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $event->id]) ?>
                            <?= $this->Form->postLink(
                                __('Delete'),
                                ['action' => 'delete', $event->id],
                                ['confirm' => __('Are you sure you want to delete "{0}"?', $event->title)],
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>