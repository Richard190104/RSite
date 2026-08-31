document.addEventListener('DOMContentLoaded', function () {
    var wrapper = document.querySelector('.site-nav__news-icon');
    var toggle = document.querySelector('.site-nav__notifications-toggle');
    if (!wrapper || !toggle) {
        return;
    }

    function setOpen(isOpen) {
        wrapper.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('has-notifications-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    toggle.addEventListener('click', function (event) {
        event.stopPropagation();
        setOpen(!wrapper.classList.contains('is-open'));
    });

    document.addEventListener('click', function (event) {
        if (!wrapper.contains(event.target)) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
});
