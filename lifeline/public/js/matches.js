// Request page: lists potential donors from GET /api/v1/requests/{id}/matches.
// The browser sends the login session cookie (DEC-015). Text from the API is set with textContent only.
(function () {
    const list = document.getElementById('matches');
    if (!list) return;

    const BADGES = {
        notified: 'text-bg-warning',
        accepted: 'text-bg-success',
        declined: 'text-bg-secondary',
        withdrawn: 'text-bg-secondary',
    };

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function showMessage(text, type) {
        list.replaceChildren(element('div', 'alert alert-' + type + ' small mb-0', text));
    }

    function contactLink(scheme, mobileNumber, icon, label) {
        const link = element('a', 'btn btn-sm btn-outline-success');
        link.href = scheme + ':' + encodeURIComponent(mobileNumber);
        const iconNode = element('i', 'bi ' + icon);
        iconNode.setAttribute('aria-hidden', 'true');
        link.append(iconNode, document.createTextNode(' ' + label));
        return link;
    }

    function showMatches(matches) {
        if (matches.length === 0) {
            showMessage('No potential donors found yet. Use "Find more donors" later.', 'secondary');
            return;
        }

        const group = element('ul', 'list-group shadow-sm');
        matches.forEach((match) => {
            const item = element('li', 'list-group-item py-3');
            // Design C2 row: red blood type tile, then who and how far.
            const header = element('div', 'd-flex align-items-center gap-3');
            const tile = element('span', 'll-blood', match.blood_type);
            tile.setAttribute('aria-hidden', 'true');
            const who = element('div', 'flex-grow-1');
            const name = element('div', 'fw-semibold', match.donor ? match.donor.name : 'Potential donor');
            name.append(element('span', 'visually-hidden', ', blood type ' + match.blood_type));
            who.append(name, element('div', 'small text-secondary', 'About ' + match.distance_km + ' km away'));
            header.append(tile, who, element('span', 'badge ' + (BADGES[match.status] || 'text-bg-secondary'), match.status_label));
            item.append(header);

            if (match.donor) {
                const contact = element('div', 'mt-2 d-flex flex-wrap align-items-center gap-2');
                contact.append(
                    element('span', 'small', match.donor.name + ' · ' + match.donor.mobile_number),
                    contactLink('tel', match.donor.mobile_number, 'bi-telephone', 'Call'),
                    contactLink('sms', match.donor.mobile_number, 'bi-chat-dots', 'SMS')
                );
                // Design D3: the donor's profile, where the donation is recorded.
                const profile = element('a', 'btn btn-sm btn-outline-primary', 'View donor');
                profile.href = match.donor.profile_url;
                contact.append(profile);
                item.append(contact);
            }

            group.append(item);
        });
        list.replaceChildren(group);
    }

    fetch(list.dataset.api, { headers: { Accept: 'application/json' } })
        .then((response) => (response.ok ? response.json() : Promise.reject(response)))
        .then((body) => showMatches(body.data))
        .catch(() => showMessage('Could not load potential donors. Refresh the page to try again.', 'danger'));
})();
