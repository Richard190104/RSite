document.addEventListener('DOMContentLoaded', function () {
    var carousel = document.querySelector('.p-gallery__subcats-carousel.swiper');
    if (!carousel) {
        return;
    }

    // 'auto' lets each slide keep the fixed width set in _gallery.scss
    // (matching .p-gallery__cards outside the carousel) instead of Swiper
    // stretching/shrinking slides to fit a fixed count per view.
    new Swiper(carousel, {
        slidesPerView: 'auto',
        spaceBetween: 25.6,
        speed: 400,
        navigation: {
            nextEl: '.p-gallery__subcats-nav-btn--next',
            prevEl: '.p-gallery__subcats-nav-btn--prev',
        },
    });
});
