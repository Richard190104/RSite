<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Text> $texts
 */
$this->assign('title', __('Texts'));
?>
<div class="content">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Text') ?></th>
                    <th><?= __('Value') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($texts as $text): ?>
                    <tr>
                        <td><?= h(__(ucfirst($text->slug))) ?></td>
                        <td><?= h($text->value) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $text->id]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>