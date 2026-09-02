document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-toggle-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            checkbox.closest('form').submit();
        });
    });
});
