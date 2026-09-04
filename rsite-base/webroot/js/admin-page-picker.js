document.addEventListener('DOMContentLoaded', function () {
    // Pairs with the .admin-page-picker markup (NavbarCategories/edit.php)
    // and admin-drag-reorder.js: each list item is a page with a checkbox
    // (on = included in this category) that the admin can also drag to
    // reorder. Toggling a checkbox doesn't move the item — reordering is a
    // separate, deliberate drag — but a checked item's position in the DOM
    // at submit time IS what gets saved as its position (see
    // Admin\NavbarCategoriesController::edit()), so this only needs to
    // style the checked/unchecked state; the browser's own form
    // serialization already sends checked checkboxes in DOM order.
    document.querySelectorAll('.admin-page-picker').forEach(function (list) {
        function syncCheckedClass(item) {
            var checkbox = item.querySelector('input[type="checkbox"]');
            item.classList.toggle('is-checked', checkbox.checked);
        }

        list.querySelectorAll('.admin-page-picker__item').forEach(syncCheckedClass);

        list.addEventListener('change', function (event) {
            if (event.target.matches('input[type="checkbox"]')) {
                syncCheckedClass(event.target.closest('.admin-page-picker__item'));
            }
        });
    });
});
