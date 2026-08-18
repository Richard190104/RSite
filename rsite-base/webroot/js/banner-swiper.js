document.addEventListener('DOMContentLoaded', function () {
    var banner = document.querySelector('.page-banner.swiper');
    if (!banner) {
        return;
    }

    new Swiper(banner, {
        loop: true,
        speed: 2000,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        autoplay: { delay: 6000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
    });
});
