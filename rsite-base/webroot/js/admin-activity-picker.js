document.addEventListener('DOMContentLoaded', function () {
    var picker = document.querySelector('[data-activity-picker]');
    if (!picker) {
        return;
    }

    var events = JSON.parse(picker.dataset.events || '[]');
    var max = parseInt(picker.dataset.max, 10) || 5;
    var noResultsText = picker.dataset.noResultsText || 'No matching activities.';
    var removeLabel = picker.dataset.removeLabel || 'Remove';
    var search = picker.querySelector('[data-activity-picker-search]');
    var results = picker.querySelector('[data-activity-picker-results]');
    var selectedList = picker.querySelector('[data-activity-picker-selected]');

    function selectedIds() {
        return Array.from(selectedList.querySelectorAll('[data-id]')).map(function (li) {
            return parseInt(li.dataset.id, 10);
        });
    }

    function renderResults(query) {
        // .normalize('NFC') guards against accented characters that arrive
        // decomposed (a base letter + a separate combining accent mark,
        // e.g. from copy-pasted admin input) — those compare unequal to the
        // same-looking precomposed character even after lowercasing, which
        // would otherwise make an obviously-matching search silently return
        // nothing.
        var normalized = query.trim().toLowerCase().normalize('NFC');
        var alreadySelected = selectedIds();

        // An empty query (search field just focused, nothing typed yet)
        // still shows a list — the full pool, capped the same way a real
        // search's results are — so an admin browsing a short activity list
        // doesn't have to type anything to see what's available.
        var matches = events
            .filter(function (event) {
                return alreadySelected.indexOf(event.id) === -1
                    && (normalized === '' || event.title.toLowerCase().normalize('NFC').indexOf(normalized) !== -1);
            })
            .slice(0, 8);

        results.innerHTML = '';

        if (!matches.length) {
            var empty = document.createElement('li');
            empty.className = 'activity-picker__result activity-picker__result--empty';
            empty.textContent = noResultsText;
            results.appendChild(empty);
            results.hidden = false;

            return;
        }

        matches.forEach(function (event) {
            var item = document.createElement('li');
            item.className = 'activity-picker__result';
            item.dataset.id = event.id;

            var title = document.createElement('span');
            title.className = 'activity-picker__result-title';
            title.textContent = event.title;

            var date = document.createElement('span');
            date.className = 'activity-picker__result-date';
            date.textContent = event.date;

            item.appendChild(title);
            item.appendChild(date);
            item.addEventListener('click', function () {
                addSelected(event);
            });
            results.appendChild(item);
        });

        results.hidden = false;
    }

    function addSelected(event) {
        if (selectedIds().length >= max) {
            return;
        }

        var item = document.createElement('li');
        item.className = 'activity-picker__selected-item';
        item.dataset.id = event.id;

        var title = document.createElement('span');
        title.className = 'activity-picker__selected-title';
        title.textContent = event.title;

        var date = document.createElement('span');
        date.className = 'activity-picker__selected-date';
        date.textContent = event.date;

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'activity-picker__remove';
        removeBtn.setAttribute('aria-label', removeLabel);
        removeBtn.textContent = '×';
        removeBtn.addEventListener('click', function () {
            item.remove();
        });

        var hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'content[featured_activities][]';
        hiddenInput.value = event.id;

        item.appendChild(title);
        item.appendChild(date);
        item.appendChild(removeBtn);
        item.appendChild(hiddenInput);
        selectedList.appendChild(item);

        search.value = '';
        results.hidden = true;
        results.innerHTML = '';
    }

    selectedList.addEventListener('click', function (e) {
        var removeBtn = e.target.closest('[data-activity-picker-remove]');
        if (!removeBtn) {
            return;
        }
        removeBtn.closest('.activity-picker__selected-item').remove();
    });

    search.addEventListener('input', function () {
        renderResults(search.value);
    });

    search.addEventListener('focus', function () {
        renderResults(search.value);
    });

    document.addEventListener('click', function (e) {
        if (!picker.contains(e.target)) {
            results.hidden = true;
        }
    });
});
