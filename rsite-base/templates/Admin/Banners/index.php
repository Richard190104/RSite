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
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Location') ?></th>
                    <th><?= __('Background') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banners as $banner): ?>
                    <tr>
                        <td>
                            <?= $this->Form->create(null, [
                                'url' => ['action' => 'toggleEnabled', $banner->id],
                                'class' => 'banner-toggle-form',
                            ]) ?>
                                <?= $this->Form->checkbox('is_enabled', [
                                    'checked' => $banner->is_enabled,
                                    'class' => 'js-toggle-checkbox',
                                    'title' => __('Show on the page'),
                                ]) ?>
                            <?= $this->Form->end() ?>
                        </td>
                        <td><?= h($banner->title) ?></td>
                        <td><?= h($banner->location) ?></td>
                        <td><?= $this->Html->image('/img/banners/' . $banner->background, ['alt' => $banner->title, 'width' => 120]) ?></td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['action' => 'edit', $banner->id],
                                'deleteUrl' => ['action' => 'delete', $banner->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $banner->title),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>