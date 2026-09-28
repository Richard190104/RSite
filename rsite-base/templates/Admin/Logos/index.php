<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Logo> $logos
 */
$this->assign('title', __('Logos'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add logo'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Logo') ?></th>
                    <th><?= __('Kind') ?></th>
                    <th><?= __('Image') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logos as $logo): ?>
                    <tr>
                        <td><?= h($logo->name) ?></td>
                        <td><?= ($logo->options['type'] ?? '') === 'main' ? __('Main') : __('Partner') ?></td>
                        <td>
                            <?php if (!empty($logo->path)): ?>
                                <?= $this->Html->image('/img/logos/' . $logo->path, ['alt' => $logo->name, 'width' => 120]) ?>
                            <?php else: ?>
                                <?= __('No image yet') ?>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['action' => 'edit', $logo->id],
                                'deleteUrl' => ['action' => 'delete', $logo->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $logo->name),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
