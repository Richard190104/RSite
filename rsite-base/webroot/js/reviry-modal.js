document.addEventListener('DOMContentLoaded', function () {
    var modal = document.querySelector('[data-reviry-modal]');
    if (!modal) {
        return;
    }

    var image = modal.querySelector('.p-reviry__modal-image');
    var type = modal.querySelector('.p-reviry__modal-type');
    var registration = modal.querySelector('.p-reviry__modal-registration');
    var title = modal.querySelector('.p-reviry__modal-title');
    var location = modal.querySelector('.p-reviry__modal-location');
    var locationText = modal.querySelector('.p-reviry__modal-location-text');
    var description = modal.querySelector('.p-reviry__modal-description');
    var closeButton = modal.querySelector('.p-reviry__modal-close');

    function open(card) {
        title.textContent = card.dataset.title || '';
        description.textContent = card.dataset.description || '';

        if (card.dataset.image) {
            image.src = card.dataset.image;
            image.alt = card.dataset.title || '';
            image.hidden = false;
        } else {
            image.hidden = true;
        }

        if (card.dataset.type) {
            type.textContent = card.dataset.type.toUpperCase();
            type.hidden = false;
        } else {
            type.hidden = true;
        }

        if (card.dataset.registrationNumber) {
            registration.textContent = card.dataset.registrationNumber;
            registration.hidden = false;
        } else {
            registration.hidden = true;
        }

        if (card.dataset.location) {
            locationText.textContent = card.dataset.location;
            location.hidden = false;
        } else {
            location.hidden = true;
        }

        modal.classList.add('is-open');
    }

    function close() {
        modal.classList.remove('is-open');
        image.src = '';
    }

    document.addEventListener('click', function (event) {
        var card = event.target.closest('[data-reviry-card]');
        if (!card) {
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
