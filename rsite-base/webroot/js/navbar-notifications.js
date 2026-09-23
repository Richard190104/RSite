document.addEventListener('DOMContentLoaded', function () {
    var nav = document.querySelector('.site-nav');
    var burger = document.querySelector('.site-nav__burger');
    var wrapper = document.querySelector('.site-nav__news-icon');
    var toggle = document.querySelector('.site-nav__notifications-toggle');

    function setOpen(isOpen) {
        if (!wrapper || !toggle) {
            return;
        }
        wrapper.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('has-notifications-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function collapseCategories() {
        if (!nav) {
            return;
        }
        nav.querySelectorAll('.site-nav__category.is-open').forEach(function (item) {
            item.classList.remove('is-open');
        });
    }

    function setMenu(open) {
        if (!nav || !burger) {
            return;
        }
        nav.classList.toggle('is-open', open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('has-nav-open', open);
        if (open) {
            setOpen(false);
        } else {
            collapseCategories();
        }
    }

    if (nav && burger) {
        burger.addEventListener('click', function () {
            setMenu(!nav.classList.contains('is-open'));
        });

        nav.addEventListener('click', function (event) {
            var title = event.target.closest('.site-nav__category > span');
            if (title && getComputedStyle(burger).display !== 'none') {
                var category = title.parentElement;
                var willOpen = !category.classList.contains('is-open');
                collapseCategories();
                if (willOpen) {
                    category.classList.add('is-open');
                }
                return;
            }
            if (!event.target.closest('a')) {
                return;
            }
            setMenu(false);
        });
    }

    if (wrapper && toggle) {
        toggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var open = !wrapper.classList.contains('is-open');
            if (open) {
                setMenu(false);
            }
            setOpen(open);
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                setOpen(false);
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setMenu(false);
            setOpen(false);
        }
    });
});
