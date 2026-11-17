// Alerts tab (M2.5, design C3). Reads GET /api/v1/notifications and marks all read with
// POST /api/v1/notifications/read, sending the CSRF token from the layout's meta tag (DEC-015).
// Text from the API is set with textContent only.
(function () {
    const app = document.getElementById('alerts-app');
    if (!app) return;

    const list = app.querySelector('[data-alerts-list]');
    const status = app.querySelector('[data-alerts-status]');
    const markRead = app.querySelector('[data-mark-read]');
    const token = document.querySelector('meta[name="csrf-token"]').content;

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function updateBadge(count) {
        document.querySelectorAll('[data-unread-badge]').forEach((badge) => {
            badge.textContent = count;
            badge.hidden = count === 0;
        });
        document.querySelectorAll('[data-unread-text]').forEach((text) => {
            text.textContent = count > 0 ? ', ' + count + ' unread' : '';
        });
    }

    function group(title, alerts) {
        if (alerts.length === 0) return null;
        const section = element('section', 'mb-3');
        const items = element('div', 'list-group shadow-sm');
        alerts.forEach((alert) => {
            const link = element('a', 'list-group-item list-group-item-action py-3' + (alert.read ? '' : ' ll-unread'));
            link.href = alert.url;
            const top = element('div', 'd-flex justify-content-between gap-2');
            top.append(element('span', 'fw-semibold', alert.title), element('span', 'small text-secondary text-nowrap', alert.time));
            link.append(top, element('div', 'small', alert.message));
            if (!alert.read) link.append(element('span', 'visually-hidden', 'Unread'));
            items.append(link);
        });
        section.append(element('h2', 'll-section-label', title), items);
        return section;
    }

    function show(body) {
        updateBadge(body.unread_count);
        markRead.disabled = body.unread_count === 0;
        if (body.data.length === 0) {
            list.replaceChildren();
            status.textContent = 'No alerts yet. You will see new requests and donor matches here.';
            return;
        }
        status.textContent = body.unread_count > 0 ? body.unread_count + ' unread' : 'All caught up.';
        list.replaceChildren(...[
            group('Today', body.data.filter((alert) => alert.group === 'today')),
            group('Earlier', body.data.filter((alert) => alert.group === 'earlier')),
        ].filter(Boolean));
    }

    function load() {
        fetch(app.dataset.api, { headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : Promise.reject(response)))
            .then(show)
            .catch(() => { status.textContent = 'Could not load alerts. Refresh the page to try again.'; });
    }

    markRead.addEventListener('click', () => {
        markRead.disabled = true;
        fetch(app.dataset.readApi, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
        })
            .then((response) => (response.ok ? load() : Promise.reject(response)))
            .catch(() => {
                markRead.disabled = false;
                status.textContent = 'Could not mark alerts as read. Please try again.';
            });
    });

    load();
})();
