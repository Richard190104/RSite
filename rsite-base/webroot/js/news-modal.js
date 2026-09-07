document.addEventListener('DOMContentLoaded', function () {
    var modal = document.querySelector('[data-news-modal]');
    if (!modal) {
        return;
    }

    var image = modal.querySelector('.p-news__modal-image');
    var category = modal.querySelector('.p-news__modal-category');
    var title = modal.querySelector('.p-news__modal-title');
    var meta = modal.querySelector('.p-news__modal-meta');
    var metaText = modal.querySelector('.p-news__modal-meta-text');
    var description = modal.querySelector('.p-news__modal-description');
    var body = modal.querySelector('.p-news__modal-body');
    var poster = modal.querySelector('.p-news__modal-poster');
    var frame = modal.querySelector('.p-news__modal-frame');
    var closeButton = modal.querySelector('.p-news__modal-close');

    function renderPoster(html) {
        poster.innerHTML = '';

        var iframe = document.createElement('iframe');
        iframe.className = 'p-news__modal-poster-frame';
        iframe.setAttribute('scrolling', 'no');
        iframe.srcdoc = '<!doctype html><html><head><meta charset="utf-8"></head>'
            + '<body style="margin:0;">' + html + '</body></html>';
        iframe.addEventListener('load', function () {
            try {
                iframe.style.height = (iframe.contentWindow.document.documentElement.scrollHeight + 2) + 'px';
            } catch (e) {
                // Cross-origin or otherwise inaccessible — leave the CSS fallback height.
            }
        });
        poster.appendChild(iframe);
    }

    function open(card) {
        // A News article with its own HTML poster (News::content, same
        // AI-assistant feature as Events::content) shows that instead of
        // the plain image/title/meta/description layout. Falls back to the
        // plain layout for articles with no poster.
        if (card.dataset.content) {
            body.hidden = true;
            image.hidden = true;
            renderPoster(card.dataset.content);
            poster.hidden = false;
            frame.classList.add('is-poster');
            modal.classList.add('is-open');

            return;
        }

        frame.classList.remove('is-poster');
        poster.hidden = true;
        poster.innerHTML = '';
        body.hidden = false;

        title.textContent = card.dataset.title || '';
        description.textContent = card.dataset.description || '';

        if (card.dataset.image) {
            image.src = card.dataset.image;
            image.alt = card.dataset.title || '';
            image.hidden = false;
        } else {
            image.hidden = true;
        }

        if (card.dataset.category) {
            category.textContent = card.dataset.category;
            category.hidden = false;
        } else {
            category.hidden = true;
        }

        if (card.dataset.date) {
            metaText.textContent = card.dataset.date;
            meta.hidden = false;
        } else {
            meta.hidden = true;
        }

        modal.classList.add('is-open');
    }

    function close() {
        modal.classList.remove('is-open');
        image.src = '';
        poster.innerHTML = '';
    }

    document.addEventListener('click', function (event) {
        var card = event.target.closest('[data-news-card]');
        if (!card) {
            return;
        }
        if (event.target.closest('a, button')) {
            return;
        }
        open(card);
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
