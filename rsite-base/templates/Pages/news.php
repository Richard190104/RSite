<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var \Cake\Datasource\Paging\PaginatedInterface<\App\Model\Entity\News> $news
 * @var array<\App\Model\Entity\Category> $categories
 * @var int|string|null $categoryId
 */
$this->loadHelper('Paginator');
$this->assign('title', __($page->title));
?>
<section class="p-news">
    <div class="p-news__filters">
        <a
            class="p-news__filter<?= $categoryId === null || $categoryId === '' ? ' p-news__filter--active' : '' ?>"
            href="<?= $this->Url->build(['action' => 'news']) ?>"
        >
            <?= __('All news') ?>
        </a>
        <?php foreach ($categories as $category): ?>
            <a
                class="p-news__filter<?= (int)$categoryId === $category->id ? ' p-news__filter--active' : '' ?>"
                href="<?= $this->Url->build(['action' => 'news', '?' => ['category' => $category->id]]) ?>"
            >
                <?= h($category->title) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($news->isEmpty()): ?>
        <p class="p-news__empty"><?= __('No news articles yet.') ?></p>
    <?php else: ?>
        <div class="p-news__grid">
            <?php foreach ($news as $article): ?>
                <article
                    class="p-news__card"
                    data-news-card
                    data-title="<?= h($article->title) ?>"
                    data-description="<?= h($article->description) ?>"
                    data-image="<?= $article->image ? h($this->Url->build('/img/news/' . $article->image)) : '' ?>"
                    data-date="<?= h($article->date->i18nFormat('d. MMMM yyyy')) ?>"
                    data-category="<?= $article->category ? h(__($article->category->title)) : '' ?>"
                    data-content="<?= h($article->content ?? '') ?>"
                >
                    <div
                        class="p-news__image<?= $article->image ? '' : ' p-news__image--placeholder' ?>"
                        <?php if ($article->image): ?>
                            style="background-image: url('<?= h($this->Url->build('/img/news/' . $article->image)) ?>')"
                        <?php endif; ?>
                    >
                        <span class="p-news__date"><?= h($article->date->i18nFormat('dd MMM yyyy')) ?></span>
                    </div>
                    <div class="p-news__body">
                        <?php if ($article->category): ?>
                            <span class="p-news__category"><?= h(__($article->category->title)) ?></span>
                        <?php endif; ?>
                        <h2 class="p-news__title"><?= h($article->title) ?></h2>
                        <p class="p-news__description"><?= h($article->description) ?></p>
                        <span class="p-news__link"><?= __('Read more') ?> &rarr;</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php
        $chevronLeft = '<svg class="p-news__pagination-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 6 9 12 15 18"></polyline></svg>';
        $chevronRight = '<svg class="p-news__pagination-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 6 15 12 9 18"></polyline></svg>';
        ?>
        <?php if ($news->pageCount() > 1): ?>
            <ul class="pagination p-news__pagination">
                <?= $this->Paginator->prev($chevronLeft, ['escape' => false]) ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next($chevronRight, ['escape' => false]) ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</section>

<div class="p-news__modal" data-news-modal>
    <div class="p-news__modal-frame">
        <?= $this->element('modalCloseBtn', ['extraClass' => 'p-news__modal-close']) ?>
        <img class="p-news__modal-image" alt="" hidden>
        <div class="p-news__modal-body">
            <div class="p-news__modal-meta-row">
                <span class="p-news__modal-category" hidden></span>
                <span class="p-news__modal-meta" hidden>
                    <svg class="p-news__modal-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="p-news__modal-meta-text"></span>
                </span>
            </div>
            <h3 class="p-news__modal-title"></h3>
            <hr class="p-news__modal-divider">
            <p class="p-news__modal-description"></p>
        </div>
        <div class="p-news__modal-poster" hidden></div>
    </div>
</div>

<?= $this->Html->script('news-modal') ?>
