// Track page: looks up a request with GET /api/v1/requests/track/{reference} and shows the result.
// Text from the API is set with textContent only.
(function () {
    const form = document.getElementById('track-form');
    const input = document.getElementById('reference');
    const result = document.getElementById('track-result');

    const BADGES = {
        open: 'text-bg-warning',
        donor_found: 'text-bg-info',
        fulfilled: 'text-bg-success',
        cancelled: 'text-bg-secondary',
        expired: 'text-bg-secondary',
    };

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function formatTime(iso) {
        return new Date(iso).toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Manila' });
    }

    function showMessage(text, type) {
        result.replaceChildren(element('div', 'alert alert-' + type, text));
    }

    function showRequest(data) {
        const header = element('div', 'd-flex justify-content-between align-items-start gap-2 mb-3');
        header.append(
            element('span', 'fw-bold font-monospace', data.reference_number),
            element('span', 'badge ' + (BADGES[data.status] || 'text-bg-secondary'), data.status_label)
        );

        const details = element('dl', 'row small');
        [
            ['Blood type', data.blood_type],
            ['Urgency', data.urgency.charAt(0).toUpperCase() + data.urgency.slice(1)],
            ['Hospital', data.hospital],
            ['Submitted', formatTime(data.submitted_at)],
            ['Last update', formatTime(data.updated_at)],
        ].forEach(([label, value]) => details.append(element('dt', 'col-5', label), element('dd', 'col-7', value)));

        const timeline = element('ol', 'list-group list-group-numbered');
        data.history.forEach((entry) => {
            const item = element('li', 'list-group-item small');
            item.append(element('span', 'fw-semibold', entry.status_label), document.createTextNode(' · ' + formatTime(entry.at)));
            timeline.append(item);
        });

        const body = element('div', 'card-body');
        body.append(header, details, element('h2', 'h6 fw-bold', 'Status history'), timeline);
        const card = element('div', 'card shadow-sm');
        card.append(body);
        result.replaceChildren(card);
    }

    function track(reference) {
        showMessage('Looking up ' + reference + '…', 'light');

        fetch(form.dataset.api + '/' + encodeURIComponent(reference), { headers: { Accept: 'application/json' } })
            .then((response) => response.json().catch(() => ({})).then((body) => ({ status: response.status, body })))
            .then(({ status, body }) => {
                if (status === 200) return showRequest(body.data);
                if (status === 404) return showMessage('No request found with that reference number. Check it and try again.', 'warning');
                if (status === 422) return showMessage('That is not a LifeLine reference number. It looks like LL-261114-ABC234.', 'warning');
                if (status === 429) return showMessage('Too many lookups. Please wait a minute and try again.', 'warning');
                showMessage('Something went wrong. Please try again.', 'danger');
            })
            .catch(() => showMessage('Could not reach LifeLine. Check your connection and try again.', 'danger'));
    }

    // A pasted reference often has spaces around it; drop them before the browser checks the pattern.
    input.addEventListener('input', () => {
        const trimmed = input.value.trim();
        if (trimmed !== input.value) input.value = trimmed;
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const reference = input.value.trim().toUpperCase();
        history.replaceState(null, '', '?reference=' + encodeURIComponent(reference));
        track(reference);
    });

    // /track?reference=... (linked from the request page) looks the request up right away.
    const fromUrl = new URLSearchParams(location.search).get('reference');
    if (fromUrl) {
        input.value = fromUrl.trim().toUpperCase();
        if (input.checkValidity()) track(input.value);
        else form.reportValidity();
    }
})();
