document.addEventListener('DOMContentLoaded', function () {
    var modal = document.querySelector('[data-aktivity-event-modal]');
    if (!modal) {
        return;
    }

    var image = modal.querySelector('.p-aktivity__event-modal-image');
    var category = modal.querySelector('.p-aktivity__event-modal-category');
    var title = modal.querySelector('.p-aktivity__event-modal-title');
    var meta = modal.querySelector('.p-aktivity__event-modal-meta');
    var description = modal.querySelector('.p-aktivity__event-modal-description');
    var body = modal.querySelector('.p-aktivity__event-modal-body');
    var poster = modal.querySelector('.p-aktivity__event-modal-poster');
    var frame = modal.querySelector('.p-aktivity__event-modal-frame');
    var closeButton = modal.querySelector('.p-aktivity__event-modal-close');

    // The poster's own inline styles use rem units sized for a 16px root
    // font — exactly how the admin's own HTML preview renders it, in an
    // isolated iframe with no site CSS loaded. This site's milligram.css
    // sets html{font-size:62.5%} (1rem = 10px) though, so injecting the
    // poster's markup directly into this page (via innerHTML) would shrink
    // every rem-based size in it by that same 62.5% — an iframe keeps it in
    // its own document with the browser's default root font size, matching
    // the admin preview exactly.
    function renderPoster(html) {
        poster.innerHTML = '';

        var iframe = document.createElement('iframe');
        iframe.className = 'p-aktivity__event-modal-poster-frame';
        // Belt-and-suspenders against the iframe showing its own scrollbars:
        // the legacy `scrolling` attribute plus CSS overflow:hidden (see
        // _aktivity.scss). Resizing the iframe to its exact content height
        // below can still leave it a pixel or two short of the content due
        // to rounding, which otherwise shows a scrollbar for no real reason.
        iframe.setAttribute('scrolling', 'no');
        iframe.srcdoc = '<!doctype html><html><head><meta charset="utf-8"></head>'
            + '<body style="margin:0;">' + html + '</body></html>';
        iframe.addEventListener('load', function () {
            try {
                // +2px covers the rounding gap mentioned above.
                iframe.style.height = (iframe.contentWindow.document.documentElement.scrollHeight + 2) + 'px';
            } catch (e) {
                // Cross-origin or otherwise inaccessible — leave the CSS fallback height.
            }
        });
        poster.appendChild(iframe);
    }

    function open(card) {
        // An event with its own HTML poster (see Admin\AssistantController's
        // HTML mode, same feature as News::content) shows that instead of
        // the plain image/title/meta/description layout — the poster is
        // already a self-contained graphic with its own letterhead/title/
        // details baked in, generated from this same data. Falls back to
        // the plain layout for events with no poster. The frame widens
        // (.is-poster) for the poster case — it was designed for a roomier
        // canvas than the plain detail layout.
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

        var metaParts = [card.dataset.date, card.dataset.location, card.dataset.time].filter(Boolean);
        if (metaParts.length) {
            meta.textContent = metaParts.join(' · ');
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

    // Delegated on document (rather than binding each card individually) so
    // this also catches the activities calendar's list items — those are
    // re-rendered on the fly by aktivity-calendar.js (day/month navigation),
    // long after this listener would have been attached to a static snapshot.
    document.addEventListener('click', function (event) {
        var card = event.target.closest('[data-aktivity-event]');
        if (!card) {
            return;
        }
        // Let the "Read more" toggle (in-place text expand) do its own
        // thing instead of also opening the popup on top of it.
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
