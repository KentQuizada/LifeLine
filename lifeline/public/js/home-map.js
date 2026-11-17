// Home "Donors near you" card (design C1). Same data as the Map tab: GET /api/v1/map with the login
// session (DEC-015). Circles are approximate (about 1 km); text is set with textContent only.
(function () {
    const card = document.getElementById('home-map');
    if (!card) return;

    const summary = card.querySelector('[data-summary]');
    const mapNode = card.querySelector('[data-map]');
    const params = new URLSearchParams();
    if (card.dataset.lat && card.dataset.lng) {
        params.set('latitude', card.dataset.lat);
        params.set('longitude', card.dataset.lng);
    }

    let map = null;
    if (window.L && mapNode) {
        // A preview only: no dragging or zooming, so it never traps scrolling on a phone.
        map = L.map(mapNode, { zoomControl: false, dragging: false, scrollWheelZoom: false, doubleClickZoom: false, touchZoom: false, keyboard: false })
            .setView([parseFloat(card.dataset.centerLat), parseFloat(card.dataset.centerLng)], 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);
    } else if (mapNode) {
        mapNode.remove();
    }

    fetch(card.dataset.api + (params.toString() ? '?' + params : ''), { headers: { Accept: 'application/json' } })
        .then((response) => (response.ok ? response.json() : Promise.reject(response)))
        .then((body) => {
            const donors = body.data.donors;
            const near = donors.filter((donor) => donor.distance_km <= 10);
            summary.textContent = near.length === 0
                ? 'No available donors within 10 km right now.'
                : near.length + ' potential donor(s) within 10 km · nearest about ' + near[0].distance_km + ' km';

            if (!map) return;
            const seen = new Set();
            donors.forEach((donor) => {
                const key = donor.latitude + ',' + donor.longitude;
                if (seen.has(key)) return;
                seen.add(key);
                L.circle([donor.latitude, donor.longitude], {
                    radius: 600, color: '#d32f2f', fillColor: '#d32f2f', fillOpacity: 0.25, weight: 1,
                }).addTo(map);
            });
        })
        .catch(() => {
            summary.textContent = 'Could not load nearby donors. Open the Map tab to try again.';
        });
})();
