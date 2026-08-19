<?php
/**
 * @var \App\View\AppView $this
 *
 * Homepage "Aktuálne novinky" section — up to 10 latest News articles,
 * shown as a Swiper carousel controlled only by the arrow buttons (no
 * autoplay, no loop) since there are too many to fit in one row.
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
                <article class="quick-news__card swiper-slide">
                    <div class="quick-news__image" style="background-image: url('<?= h($this->Url->build('/img/news/' . $article->image)) ?>')">
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

    <div class="quick-news__nav-row">
        <button type="button" class="quick-news__nav quick-news__nav--prev" aria-label="<?= __('Previous') ?>">&larr;</button>
        <button type="button" class="quick-news__nav quick-news__nav--next" aria-label="<?= __('Next') ?>">&rarr;</button>
    </div>

    <?php // TODO: point at the news listing page once it exists ?>
    <a class="quick-news__all" href="#"><?= __('All news') ?> &rarr;</a>
</section>
<?= $this->Html->script('vendor/swiper-bundle.min') ?>
<?= $this->Html->script('news-swiper') ?>
