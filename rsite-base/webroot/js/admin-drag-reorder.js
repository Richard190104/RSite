document.addEventListener('DOMContentLoaded', function () {
    // Generic HTML5-drag-and-drop reordering for any .js-drag-reorder-list
    // container of .js-drag-reorder-item rows/elements, each grabbable only
    // by its .js-drag-reorder-handle. Two use modes, told apart by whether
    // the container has a data-drag-reorder-url:
    //   - WITH the attribute (e.g. NavbarCategories index): each drop
    //     immediately POSTs the full new id order to that URL.
    //   - WITHOUT it (e.g. NavbarCategories edit's page picker): reordering
    //     only rearranges the DOM — checked checkboxes are then submitted
    //     in that DOM order by the browser's own form serialization, no
    //     extra JS needed to read the order back out (see
    //     admin-page-picker.js for that markup's checked/unchecked styling).
    document.querySelectorAll('.js-drag-reorder-list').forEach(function (list) {
        var draggedItem = null;
        var pressedHandle = false;

        // dragstart's event.target is the [draggable] element itself in
        // most browsers, not the innermost element under the pointer — so
        // closest('.js-drag-reorder-handle') from there can never match a
        // descendant. Track whether the press actually started on the
        // handle via mousedown instead, and gate dragstart on that.
        list.addEventListener('mousedown', function (event) {
            pressedHandle = !!event.target.closest('.js-drag-reorder-handle');
        });

        list.addEventListener('dragstart', function (event) {
            var item = event.target.closest('.js-drag-reorder-item');
            if (!item || !pressedHandle) {
                event.preventDefault();

                return;
            }
            draggedItem = item;
            event.dataTransfer.effectAllowed = 'move';
            // Firefox requires setData to be called for drag to start at all.
            event.dataTransfer.setData('text/plain', item.dataset.id || '');
            item.classList.add('is-dragging');
        });

        list.addEventListener('dragend', function () {
            if (draggedItem) {
                draggedItem.classList.remove('is-dragging');
            }
            draggedItem = null;
            pressedHandle = false;
        });

        list.addEventListener('dragover', function (event) {
            if (!draggedItem) {
                return;
            }
            event.preventDefault();

            var target = event.target.closest('.js-drag-reorder-item');
            if (!target || target === draggedItem) {
                return;
            }

            // Both current uses (a <tbody> of rows, a block-level <ul> of
            // list items) stack vertically, so the drop position is always
            // decided by which half of the target's height the pointer is
            // over — no horizontal-layout case to handle (yet).
            var rect = target.getBoundingClientRect();
            var before = event.clientY - rect.top < rect.height / 2;

            target.parentNode.insertBefore(draggedItem, before ? target : target.nextSibling);
        });

        list.addEventListener('drop', function (event) {
            event.preventDefault();
            if (!draggedItem) {
                return;
            }

            var url = list.dataset.dragReorderUrl;
            if (!url) {
                list.dispatchEvent(new CustomEvent('reordered', { bubbles: true }));

                return;
            }

            var order = Array.prototype.map.call(
                list.querySelectorAll('.js-drag-reorder-item'),
                function (item) { return item.dataset.id; },
            );

            // Present (possibly empty-string, meaning "no parent"/top-level)
            // only for lists that opted in via the attribute — e.g.
            // Categories, which has more than one same-page drag list and
            // needs the server to scope the reorder to just this parent.
            // Absent entirely for single-list pages like NavbarCategories,
            // which have nothing to scope by.
            var body = { order: order };
            if (list.dataset.dragReorderParentId !== undefined) {
                body.parentId = list.dataset.dragReorderParentId || null;
            }

            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfMeta ? csrfMeta.content : '',
                    Accept: 'application/json',
                },
                body: JSON.stringify(body),
            }).catch(function () {
                // Best-effort — a failed reorder just means the old order
                // persists server-side; the admin can retry the drag.
            });
        });
    });
});
