document.addEventListener('DOMContentLoaded', function () {
    var carousel = document.querySelector('.quick-news__carousel.swiper');
    if (!carousel) {
        return;
    }

    new Swiper(carousel, {
        slidesPerView: 1,
        spaceBetween: 24,
        speed: 400,
        breakpoints: {
            640: { slidesPerView: 2 },
            992: { slidesPerView: 3 },
        },
        navigation: {
            nextEl: '.quick-news__nav--next',
            prevEl: '.quick-news__nav--prev',
        },
    });
});
