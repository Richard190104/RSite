<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Logo> $logos
 */
$this->assign('title', __('Logos'));
?>
<div class="content">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Logo') ?></th>
                    <th><?= __('Image') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logos as $logo): ?>
                    <tr>
                        <td><?= h(__($logo->name)) ?></td>
                        <td>
                            <?php if (!empty($logo->path)): ?>
                                <?= $this->Html->image('/img/logos/' . $logo->path, ['alt' => $logo->name, 'width' => 120]) ?>
                            <?php else: ?>
                                <?= __('No image yet') ?>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $logo->id]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
