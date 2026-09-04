<?php
/**
 * @var \App\View\AppView $this
 * @var string $prevClass Full class for the "previous" button (used as the Swiper prevEl selector).
 * @var string $nextClass Full class for the "next" button (used as the Swiper nextEl selector).
 */
?>
<div class="carousel-nav">
    <button type="button" class="carousel-nav__btn carousel-nav__btn--prev <?= h($prevClass) ?>" aria-label="<?= __('Previous') ?>">&larr;</button>
    <button type="button" class="carousel-nav__btn carousel-nav__btn--next <?= h($nextClass) ?>" aria-label="<?= __('Next') ?>">&rarr;</button>
</div>
