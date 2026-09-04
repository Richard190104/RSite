<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CommitteeMember> $committeeMembers
 */
$this->assign('title', __('Committee'));
?>
<div class="content">
    <p>
        <?= $this->Html->link(__('Add committee member'), ['action' => 'add'], ['class' => 'button']) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Photo') ?></th>
                    <th><?= __('Name') ?></th>
                    <th><?= __('Role') ?></th>
                    <th><?= __('Section') ?></th>
                    <th><?= __('Phone') ?></th>
                    <th><?= __('Email') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($committeeMembers as $committeeMember): ?>
                    <tr>
                        <td>
                            <?php if ($committeeMember->photo): ?>
                                <?= $this->Html->image('/img/committee/' . $committeeMember->photo, ['alt' => $committeeMember->name, 'width' => 60]) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= h($committeeMember->name) ?></td>
                        <td><?= h($committeeMember->role ?? '') ?></td>
                        <td><?= h($committeeMember->section ?? '') ?></td>
                        <td><?= h($committeeMember->phone ?? '') ?></td>
                        <td><?= h($committeeMember->email ?? '') ?></td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['action' => 'edit', $committeeMember->id],
                                'deleteUrl' => ['action' => 'delete', $committeeMember->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $committeeMember->name),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
