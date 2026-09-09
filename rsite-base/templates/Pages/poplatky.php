<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var array<string, array{heading: string, rows: array<int, array{0: string, 1: string}>, size: string}> $feeSections
 *
 * Fee tables are admin-managed (Admin\FeesController, edited via
 * Admin\PagesController::editPoplatky()), grouped by category into
 * $feeSections by PagesController::poplatky(). Everything below the tables
 * (permit issue dates, fishing licence exemptions, payment account, etc.)
 * is a single WYSIWYG-edited HTML blob (page.content['notice']), sanitized
 * on save the same way Events/News::content is — see HtmlSanitizeTrait.
 */
$this->assign('title', __($page->title));
$notice = $page->content['notice'] ?? '';
?>
<section class="p-poplatky">
    <div class="p-poplatky__body">
        <div class="p-poplatky__grid">
            <?php foreach ($feeSections as $section): ?>
                <div class="p-poplatky__table-block p-poplatky__table-block--<?= h($section['size']) ?>">
                    <h2 class="p-poplatky__section-title"><?= h($section['heading']) ?></h2>
                    <div class="table-responsive">
                        <table class="p-poplatky__table">
                            <tbody>
                                <?php foreach ($section['rows'] as [$label, $price]): ?>
                                    <tr>
                                        <td><?= h($label) ?></td>
                                        <td class="p-poplatky__price"><?= h($price) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($notice !== ''): ?>
            <div class="p-poplatky__notice p-poplatky__notice--wysiwyg">
                <?= $notice ?>
            </div>
        <?php endif; ?>
    </div>
</section>
