<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\News> $news
 */
$this->assign('title', __('News'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add article'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Image') ?></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Category') ?></th>
                    <th><?= __('Date') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($news as $article): ?>
                    <tr>
                        <td><?= $this->Html->image('/img/news/' . $article->image, ['alt' => $article->title, 'width' => 80]) ?></td>
                        <td><?= h($article->title) ?></td>
                        <td><?= h($article->category->title ?? '') ?></td>
                        <td><?= h($article->date->format('d.m.Y')) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $article->id]) ?>
                            <?= $this->Form->postLink(
                                __('Delete'),
                                ['action' => 'delete', $article->id],
                                ['confirm' => __('Are you sure you want to delete "{0}"?', $article->title)],
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>