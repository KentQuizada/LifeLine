// Donor map (M2.4, design C2). Data from GET /api/v1/map with the login session (DEC-015).
// Text from the API is set with textContent only. Works without location permission: it falls back
// to Butuan City, and tapping the map chooses another point.
(function () {
    const app = document.getElementById('donor-map-app');
    if (!app) return;

    const status = app.querySelector('[data-map-status]');
    const donorList = app.querySelector('[data-donor-list]');
    const hospitalList = app.querySelector('[data-hospital-list]');
    const bloodType = app.querySelector('#map_blood_type');
    const mapNode = app.querySelector('[data-map]');
    let point = null; // { latitude, longitude } chosen by the user, or null for the default centre
    let map = null;
    let layer = null;

    const round = (value) => Math.round(value * 100) / 100;

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    // The map needs internet tiles; the lists below work without them.
    if (window.L && mapNode) {
        map = L.map(mapNode).setView([parseFloat(app.dataset.centerLat), parseFloat(app.dataset.centerLng)], 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);
        layer = L.layerGroup().addTo(map);
        map.on('click', (event) => {
            point = { latitude: round(event.latlng.lat), longitude: round(event.latlng.lng) };
            load('Showing donors near the point you chose.');
        });
    } else if (mapNode) {
        mapNode.remove();
    }

    function listItem(title, detail) {
        const item = element('li', 'list-group-item d-flex justify-content-between align-items-center gap-2');
        item.append(element('span', 'fw-semibold', title), element('span', 'small text-secondary text-nowrap', detail));
        return item;
    }

    function draw(data) {
        donorList.replaceChildren();
        hospitalList.replaceChildren();
        if (layer) layer.clearLayers();

        if (data.donors.length === 0) {
            donorList.append(element('li', 'list-group-item text-secondary small', 'No available donors match this filter yet.'));
        }

        // Donors sharing the same approximate square are drawn as one circle with a count.
        const groups = {};
        data.donors.forEach((donor) => {
            donorList.append(listItem('Potential donor · ' + donor.blood_type, 'about ' + donor.distance_km + ' km'));
            const key = donor.latitude + ',' + donor.longitude;
            (groups[key] = groups[key] || { donor: donor, types: [] }).types.push(donor.blood_type);
        });

        data.hospitals.forEach((hospital) => {
            const label = hospital.type === 'blood_bank' ? 'Blood bank' : 'Hospital';
            hospitalList.append(listItem(hospital.name, label + ' · about ' + hospital.distance_km + ' km'));
            if (layer) {
                L.marker([hospital.latitude, hospital.longitude])
                    .bindPopup(document.createTextNode(hospital.name + ' (' + label + ')'))
                    .addTo(layer);
            }
        });

        if (!map) return;

        Object.values(groups).forEach((group) => {
            const text = group.types.length + ' potential donor(s): ' + group.types.join(', ');
            L.circle([group.donor.latitude, group.donor.longitude], {
                radius: 600, color: '#d32f2f', fillColor: '#d32f2f', fillOpacity: 0.25,
            }).bindPopup(document.createTextNode(text)).addTo(layer);
        });

        const center = [data.center.latitude, data.center.longitude];
        if (data.center.source === 'user') {
            L.circleMarker(center, { radius: 7, color: '#0d6efd', fillOpacity: 0.9 })
                .bindPopup(document.createTextNode('Search point')).addTo(layer);
        }
        map.setView(center, map.getZoom());
    }

    function load(message) {
        const params = new URLSearchParams();
        if (bloodType.value) params.set('blood_type', bloodType.value);
        if (point) {
            params.set('latitude', point.latitude);
            params.set('longitude', point.longitude);
        }
        status.textContent = 'Loading donors…';

        fetch(app.dataset.api + '?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : Promise.reject(response)))
            .then((body) => {
                draw(body.data);
                status.textContent = message || (body.data.center.source === 'default'
                    ? 'Showing donors around Butuan City. Use "Near me" or tap the map to search elsewhere.'
                    : 'Showing donors near your search point.');
            })
            .catch(() => { status.textContent = 'Could not load the donor map. Refresh the page to try again.'; });
    }

    bloodType.addEventListener('change', () => load());

    app.querySelector('[data-use-location]').addEventListener('click', () => {
        if (!navigator.geolocation) {
            status.textContent = 'This browser cannot share its location. Tap the map to choose a point.';
            return;
        }
        status.textContent = 'Getting your location…';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                // Rounded before it leaves the browser: about 1 km is enough to rank donors.
                point = { latitude: round(position.coords.latitude), longitude: round(position.coords.longitude) };
                load('Showing donors near you.');
            },
            () => load('Location permission was denied, so the map shows Butuan City. Tap the map to choose a point.'),
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 600000 }
        );
    });

    load();
})();
