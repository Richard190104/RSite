document.addEventListener('DOMContentLoaded', function () {
    var modal = document.querySelector('[data-gallery-lightbox-modal]');
    if (!modal) {
        return;
    }

    var image = modal.querySelector('.p-gallery__lightbox-image');
    var caption = modal.querySelector('.p-gallery__lightbox-caption');
    var closeButton = modal.querySelector('.p-gallery__lightbox-close');

    function open(src, alt, captionText) {
        image.src = src;
        image.alt = alt || '';
        if (caption) {
            caption.textContent = captionText || '';
            caption.hidden = !captionText;
        }
        modal.classList.add('is-open');
    }

    function close() {
        modal.classList.remove('is-open');
        image.src = '';
    }

    document.querySelectorAll('[data-gallery-lightbox]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            var thumb = link.querySelector('img');
            open(link.getAttribute('href'), thumb ? thumb.alt : '', link.dataset.galleryCaption);
        });
    });

    closeButton.addEventListener('click', close);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            close();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            close();
        }
    });
});
