<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Gallery> $photos
 */
$this->assign('title', __('Galleries'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add photo'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Image') ?></th>
                    <th><?= __('Category') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($photos as $photo): ?>
                    <tr>
                        <td><?= $this->Html->image('/img/galleries/' . $photo->image, ['alt' => '', 'width' => 80]) ?></td>
                        <td><?= h($photo->category->title ?? '') ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $photo->id]) ?>
                            <?= $this->Form->postLink(
                                __('Delete'),
                                ['action' => 'delete', $photo->id],
                                ['confirm' => __('Are you sure you want to delete this photo?')],
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>