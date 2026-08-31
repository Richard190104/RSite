document.addEventListener('DOMContentLoaded', function () {
    var popup = document.querySelector('.notification-popup');
    if (!popup) {
        return;
    }

    function dismiss() {
        popup.classList.add('is-leaving');
        popup.addEventListener('transitionend', function () {
            popup.remove();
        }, { once: true });
    }

    requestAnimationFrame(function () {
        popup.classList.add('is-visible');
    });

    var closeButton = popup.querySelector('.notification-popup__close');
    if (closeButton) {
        closeButton.addEventListener('click', dismiss);
    }

    setTimeout(dismiss, 10000);
});
