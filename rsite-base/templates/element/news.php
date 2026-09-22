<?php
/**
 * @var \App\View\AppView $this
 *
 * Homepage "Aktuálne novinky" section — up to 10 latest News articles,
 * shown as a Swiper carousel controlled only by the arrow buttons (no
 * autoplay, no loop) since there are too many to fit in one row.
 * Clicking a card opens the same detail modal as /news.
 */
$news = $this->getNews(10);

if (!$news) {
    return;
}
?>
<?= $this->Html->css('vendor/swiper-bundle.min') ?>
<section class="quick-news">
    <span class="quick-news__heading"><?= __('Latest news') ?></span>

    <div class="quick-news__carousel swiper">
        <div class="swiper-wrapper">
            <?php foreach ($news as $article): ?>
                <?php
                    $articleImageUrl = $this->Url->build($article->image
                        ? '/img/news/' . $article->image
                        : $this->randomPlaceholderImage());
                ?>
                <article
                    class="quick-news__card swiper-slide"
                    data-news-card
                    data-title="<?= h($article->title) ?>"
                    data-description="<?= h($article->description) ?>"
                    data-image="<?= h($articleImageUrl) ?>"
                    data-date="<?= h($article->date->i18nFormat('d. MMMM yyyy')) ?>"
                    data-category="<?= $article->category ? h(__($article->category->title)) : '' ?>"
                    data-content="<?= h($article->content ?? '') ?>"
                >
                    <div class="quick-news__image-frame">
                        <div
                            class="quick-news__image"
                            style="background-image: url('<?= h($articleImageUrl) ?>')"
                        ></div>
                        <span class="quick-news__date"><?= h($article->date->i18nFormat('dd MMM yyyy')) ?></span>
                    </div>
                    <div class="quick-news__body">
                        <?php if ($article->category): ?>
                            <span class="quick-news__category"><?= h(__($article->category->title)) ?></span>
                        <?php endif; ?>
                        <h3 class="quick-news__title"><?= h($article->title) ?></h3>
                        <p class="quick-news__description"><?= h($article->description) ?></p>
                        <span class="quick-news__link"><?= __('Read more') ?> &rarr;</span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="quick-news__footer">
        <button type="button" class="carousel-nav__btn quick-news__nav--prev" aria-label="<?= __('Previous') ?>">&larr;</button>

        <a class="quick-news__all" href="<?= $this->Url->build(['action' => 'news']) ?>"><?= __('All news') ?> &rarr;</a>

        <button type="button" class="carousel-nav__btn quick-news__nav--next" aria-label="<?= __('Next') ?>">&rarr;</button>
    </div>
</section>

<?= $this->element('newsModal') ?>

<?= $this->Html->script('vendor/swiper-bundle.min') ?>
<?= $this->Html->script('news-swiper') ?>
