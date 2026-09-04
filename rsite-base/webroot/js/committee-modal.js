document.addEventListener('DOMContentLoaded', function () {
    var modal = document.querySelector('[data-committee-modal]');
    if (!modal) {
        return;
    }

    var image = modal.querySelector('.committee-modal__image');
    var placeholder = modal.querySelector('.committee-modal__photo-placeholder');
    var name = modal.querySelector('.committee-modal__name');
    var role = modal.querySelector('.committee-modal__role');
    var email = modal.querySelector('.committee-modal__email');
    var phone = modal.querySelector('.committee-modal__phone');
    var closeButton = modal.querySelector('.committee-modal__close');

    function setLink(el, href, text) {
        if (!text) {
            el.hidden = true;

            return;
        }
        el.href = href;
        el.textContent = text;
        el.hidden = false;
    }

    function open(card) {
        name.textContent = card.dataset.name || '';

        if (card.dataset.photo) {
            image.src = card.dataset.photo;
            image.alt = card.dataset.name || '';
            image.hidden = false;
            placeholder.hidden = true;
        } else {
            image.hidden = true;
            placeholder.hidden = false;
        }

        if (card.dataset.role) {
            role.textContent = card.dataset.role;
            role.hidden = false;
        } else {
            role.hidden = true;
        }

        setLink(email, 'mailto:' + card.dataset.email, card.dataset.email);
        setLink(phone, 'tel:' + card.dataset.phone.replace(/\s+/g, ''), card.dataset.phone);

        modal.classList.add('is-open');
    }

    function close() {
        modal.classList.remove('is-open');
        image.src = '';
    }

    document.querySelectorAll('[data-committee-member]').forEach(function (card) {
        card.addEventListener('click', function (event) {
            // Let the email/phone links inside the card do their own
            // mailto:/tel: thing instead of also opening the popup.
            if (event.target.closest('a')) {
                return;
            }
            open(card);
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
