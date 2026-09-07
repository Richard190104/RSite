document.addEventListener('DOMContentLoaded', function () {
    var popup = document.querySelector('.notification-popup');
    if (!popup) {
        return;
    }

    // Shown once per notification id per browser tab: the server renders
    // this popup on every page load (it has no way to know what the
    // visitor already dismissed), so without this check navigating between
    // pages within one visit kept re-showing the same popup every time.
    // sessionStorage — not localStorage — deliberately: it clears when the
    // tab closes, so the popup is back for a genuinely new visit later,
    // same as before this change, just not on every single page within one.
    var STORAGE_KEY = 'seenNotificationPopups';
    var notificationId = popup.dataset.notificationId;

    function seenIds() {
        try {
            return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    if (notificationId) {
        var ids = seenIds();
        if (ids.indexOf(notificationId) !== -1) {
            popup.remove();

            return;
        }

        try {
            ids.push(notificationId);
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        } catch (e) {
            // Storage unavailable (private browsing, quota, etc.) — the
            // popup still shows this once, it just won't remember for the
            // next page.
        }
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
