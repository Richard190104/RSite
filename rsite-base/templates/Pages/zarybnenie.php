<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var array<\App\Model\Entity\StockingDocument> $stockingDocuments
 * @var array<\App\Model\Entity\CatchDocument> $catchDocuments
 */
$this->assign('title', __($page->title));
?>
<section class="p-zarybnenie">
    <div class="p-zarybnenie__section">
        <h1 class="p-zarybnenie__heading"><?= __('Zarybnenie') ?></h1>

        <?php if (!$stockingDocuments): ?>
            <p class="p-zarybnenie__empty"><?= __('No documents have been added yet.') ?></p>
        <?php else: ?>
            <div class="p-zarybnenie__list">
                <?php foreach ($stockingDocuments as $stockingDocument): ?>
                    <a
                        class="p-zarybnenie__doc"
                        href="<?= h($this->Url->build('/files/stocking-documents/' . $stockingDocument->file)) ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <?= $this->element('pdfIcon') ?>
                        <span class="p-zarybnenie__doc-title"><?= h($stockingDocument->title) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="p-zarybnenie__section">
        <h2 class="p-zarybnenie__heading"><?= __('Catches') ?></h2>

        <?php if (!$catchDocuments): ?>
            <p class="p-zarybnenie__empty"><?= __('No documents have been added yet.') ?></p>
        <?php else: ?>
            <div class="p-zarybnenie__list">
                <?php foreach ($catchDocuments as $catchDocument): ?>
                    <a
                        class="p-zarybnenie__doc"
                        href="<?= h($this->Url->build('/files/catch-documents/' . $catchDocument->file)) ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <?= $this->element('pdfIcon') ?>
                        <span class="p-zarybnenie__doc-title"><?= h($catchDocument->title) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
