(() => {
    // Truncate long event card descriptions — show "Read more" only when needed.
    document.querySelectorAll('.p-aktivity__event-card').forEach((card) => {
        const text = card.querySelector('.p-aktivity__event-text');
        const button = card.querySelector('.p-aktivity__event-more');
        if (!text || !button) {
            return;
        }

        if (text.scrollHeight > text.clientHeight + 1) {
            button.hidden = false;
        }

        button.addEventListener('click', () => {
            const expanded = card.classList.toggle('is-expanded');
            button.textContent = expanded
                ? button.dataset.lessLabel
                : button.dataset.moreLabel;
        });
    });

    const root = document.getElementById('aktivity-calendar');
    if (!root) {
        return;
    }

    let events = [];
    try {
        events = JSON.parse(root.dataset.events || '[]');
    } catch {
        events = [];
    }

    let weekdays = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'];
    try {
        weekdays = JSON.parse(root.dataset.weekdays || '[]');
    } catch {
        // keep defaults
    }

    const locale = root.dataset.locale || 'sk-SK';
    const emptyLabel = root.dataset.emptyLabel || '';
    const dayEmptyLabel = root.dataset.dayEmptyLabel || emptyLabel;
    const closestLabel = root.dataset.closestLabel || '';

    const monthLabel = root.querySelector('[data-cal-month]');
    const weekdaysEl = root.querySelector('[data-cal-weekdays]');
    const gridEl = root.querySelector('[data-cal-grid]');
    const listEl = root.querySelector('[data-cal-list]');
    const asideTitle = root.querySelector('[data-cal-aside-title]');
    const prevBtn = root.querySelector('[data-cal-prev]');
    const nextBtn = root.querySelector('[data-cal-next]');

    if (!monthLabel || !weekdaysEl || !gridEl || !listEl || !prevBtn || !nextBtn) {
        return;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let view = new Date(today.getFullYear(), today.getMonth(), 1);
    let selectedDate = null;

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const toKey = (date) => {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const parseKey = (key) => {
        const [y, m, d] = key.split('-').map(Number);
        return new Date(y, m - 1, d);
    };

    const eventsByDate = events.reduce((acc, event) => {
        if (!event.date) {
            return acc;
        }
        if (!acc[event.date]) {
            acc[event.date] = [];
        }
        acc[event.date].push(event);
        return acc;
    }, {});

    weekdaysEl.innerHTML = weekdays
        .map((day) => `<span class="p-aktivity__calendar-weekday">${escapeHtml(day)}</span>`)
        .join('');

    const metaLine = (event) => {
        const parts = [event.location, event.time].filter(Boolean);
        return parts.length ? `<p class="p-aktivity__calendar-item-meta">${escapeHtml(parts.join(' · '))}</p>` : '';
    };

    const renderMonthList = (monthStart) => {
        if (asideTitle) {
            asideTitle.textContent = closestLabel;
        }

        const monthEnd = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 0);
        const monthEvents = events
            .filter((event) => {
                if (!event.date) {
                    return false;
                }
                const date = parseKey(event.date);
                return date >= monthStart && date <= monthEnd;
            })
            .sort((a, b) => a.date.localeCompare(b.date));

        const upcoming = monthEvents.filter((event) => parseKey(event.date) >= today);
        const shown = upcoming.length ? upcoming : monthEvents;

        if (!shown.length) {
            listEl.innerHTML = `<li class="p-aktivity__calendar-empty">${escapeHtml(emptyLabel)}</li>`;
            return;
        }

        listEl.innerHTML = shown
            .map((event) => {
                const date = parseKey(event.date);
                const isPast = date < today;
                const day = date.toLocaleDateString(locale, { day: 'numeric' });
                const month = date.toLocaleDateString(locale, { month: 'short' }).replace(/\./g, '');
                return `
                    <li class="p-aktivity__calendar-item${isPast ? ' is-past' : ''}">
                        <span class="p-aktivity__calendar-badge">${escapeHtml(`${day} ${month}`)}</span>
                        <div class="p-aktivity__calendar-item-body">
                            <span class="p-aktivity__calendar-item-title">${escapeHtml(event.title)}</span>
                            ${metaLine(event)}
                        </div>
                    </li>
                `;
            })
            .join('');
    };

    const renderDayList = (key) => {
        const date = parseKey(key);
        if (asideTitle) {
            asideTitle.textContent = date.toLocaleDateString(locale, {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
        }

        const dayEvents = eventsByDate[key] || [];
        if (!dayEvents.length) {
            listEl.innerHTML = `<li class="p-aktivity__calendar-empty">${escapeHtml(dayEmptyLabel)}</li>`;
            return;
        }

        const isPast = date < today;
        const day = date.toLocaleDateString(locale, { day: 'numeric' });
        const month = date.toLocaleDateString(locale, { month: 'short' }).replace(/\./g, '');
        listEl.innerHTML = dayEvents
            .map((event) => `
                <li class="p-aktivity__calendar-item is-detail${isPast ? ' is-past' : ''}">
                    <span class="p-aktivity__calendar-badge">${escapeHtml(`${day} ${month}`)}</span>
                    <div class="p-aktivity__calendar-item-body">
                        <span class="p-aktivity__calendar-item-title">${escapeHtml(event.title)}</span>
                        ${event.description ? `<p class="p-aktivity__calendar-item-text">${escapeHtml(event.description)}</p>` : ''}
                        ${metaLine(event)}
                    </div>
                </li>
            `)
            .join('');
    };

    const render = () => {
        monthLabel.textContent = view.toLocaleDateString(locale, {
            month: 'long',
            year: 'numeric',
        });

        const year = view.getFullYear();
        const month = view.getMonth();
        const firstDay = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        let startOffset = firstDay.getDay() - 1;
        if (startOffset < 0) {
            startOffset = 6;
        }

        const cells = [];
        for (let i = 0; i < startOffset; i += 1) {
            cells.push('<span class="p-aktivity__calendar-day is-empty" aria-hidden="true"></span>');
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(year, month, day);
            const key = toKey(date);
            const dayEvents = eventsByDate[key] || [];
            const isToday = key === toKey(today);
            const isPast = date < today;
            const hasEvents = dayEvents.length > 0;
            const isSelected = selectedDate === key;

            let classes = 'p-aktivity__calendar-day';
            if (isToday) {
                classes += ' is-today';
            }
            if (isSelected) {
                classes += ' is-selected';
            }
            if (hasEvents) {
                classes += isPast ? ' has-event is-past' : ' has-event is-future';
            }

            cells.push(
                `<button type="button" class="${classes}" data-date="${key}" ${hasEvents ? '' : 'disabled'}>${day}</button>`
            );
        }

        // Always fill 6 weeks (42 cells) so the grid height stays stable
        // when switching between months with different week counts.
        while (cells.length < 42) {
            cells.push('<span class="p-aktivity__calendar-day is-empty" aria-hidden="true"></span>');
        }

        gridEl.innerHTML = cells.join('');

        if (selectedDate && eventsByDate[selectedDate]) {
            renderDayList(selectedDate);
        } else {
            selectedDate = null;
            renderMonthList(firstDay);
        }
    };

    gridEl.addEventListener('click', (event) => {
        const dayBtn = event.target.closest('[data-date]');
        if (!dayBtn || dayBtn.disabled) {
            return;
        }

        const key = dayBtn.dataset.date;
        if (!eventsByDate[key]) {
            return;
        }

        selectedDate = selectedDate === key ? null : key;
        render();
    });

    prevBtn.addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() - 1, 1);
        selectedDate = null;
        render();
    });

    nextBtn.addEventListener('click', () => {
        view = new Date(view.getFullYear(), view.getMonth() + 1, 1);
        selectedDate = null;
        render();
    });

    render();
})();
