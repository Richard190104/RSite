<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var iterable<\App\Model\Entity\StockingDocument> $stockingDocuments
 * @var iterable<\App\Model\Entity\CatchDocument> $catchDocuments
 *
 * "Zarybnenie a úlovky" is managed entirely from here rather than as its
 * own sidebar entry — StockingDocuments/CatchDocuments still have full CRUD
 * controllers (Admin\StockingDocumentsController, Admin\CatchDocumentsController,
 * including their file uploads), this page just lists the documents and
 * links into those controllers' add/edit/delete actions. Each document is
 * nothing but a title and a PDF — no other fields.
 */
$this->assign('title', __('Edit "Zarybnenie a úlovky" page'));
$description = $page->content['description'] ?? '';
?>
<div class="content form-card">
    <?= $this->Form->create($page) ?>
        <div class="form-grid">
            <?= $this->Form->control('content.description', [
                'type' => 'textarea',
                'rows' => 3,
                'label' => __('Description'),
                'value' => $description,
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>
</div>

<div class="content" style="margin-top: 2rem;">
    <h2><?= __('Zarybnenie') ?></h2>
    <p>
        <?= $this->Html->link(
            __('Add document'),
            ['prefix' => 'Admin', 'controller' => 'StockingDocuments', 'action' => 'add'],
            ['class' => 'button'],
        ) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Title') ?></th>
                    <th><?= __('File') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stockingDocuments as $stockingDocument): ?>
                    <tr>
                        <td><?= h($stockingDocument->title) ?></td>
                        <td>
                            <?= $this->Html->link(__('PDF'), '/files/stocking-documents/' . $stockingDocument->file, ['target' => '_blank']) ?>
                        </td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['prefix' => 'Admin', 'controller' => 'StockingDocuments', 'action' => 'edit', $stockingDocument->id],
                                'deleteUrl' => ['prefix' => 'Admin', 'controller' => 'StockingDocuments', 'action' => 'delete', $stockingDocument->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $stockingDocument->title),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content" style="margin-top: 2rem;">
    <h2><?= __('Úlovky') ?></h2>
    <p>
        <?= $this->Html->link(
            __('Add document'),
            ['prefix' => 'Admin', 'controller' => 'CatchDocuments', 'action' => 'add'],
            ['class' => 'button'],
        ) ?>
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= __('Title') ?></th>
                    <th><?= __('File') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($catchDocuments as $catchDocument): ?>
                    <tr>
                        <td><?= h($catchDocument->title) ?></td>
                        <td>
                            <?= $this->Html->link(__('PDF'), '/files/catch-documents/' . $catchDocument->file, ['target' => '_blank']) ?>
                        </td>
                        <td class="actions">
                            <?= $this->element('Admin/rowActions', [
                                'editUrl' => ['prefix' => 'Admin', 'controller' => 'CatchDocuments', 'action' => 'edit', $catchDocument->id],
                                'deleteUrl' => ['prefix' => 'Admin', 'controller' => 'CatchDocuments', 'action' => 'delete', $catchDocument->id],
                                'confirmMessage' => __('Are you sure you want to delete "{0}"?', $catchDocument->title),
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
