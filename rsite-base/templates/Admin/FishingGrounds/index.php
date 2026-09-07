<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\FishingGround> $fishingGrounds
 */
$this->assign('title', __('Revíry'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add fishing ground'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Image') ?></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Registration number') ?></th>
                    <th><?= __('Type') ?></th>
                    <th><?= __('Location') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fishingGrounds as $fishingGround): ?>
                    <tr>
                        <td>
                            <?php if ($fishingGround->image): ?>
                                <?= $this->Html->image('/img/fishing-grounds/' . $fishingGround->image, ['alt' => $fishingGround->title, 'width' => 80]) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= h($fishingGround->title) ?></td>
                        <td><?= h($fishingGround->registration_number ?? '') ?></td>
                        <td><?= h($fishingGround->type ?? '') ?></td>
                        <td><?= h($fishingGround->location ?? '') ?></td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['action' => 'edit', $fishingGround->id],
                                'deleteUrl' => ['action' => 'delete', $fishingGround->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $fishingGround->title),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
