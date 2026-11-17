// Edit profile: the donor's approximate location (M2.4). Coordinates are rounded to 2 decimals
// (about 1 km) before they leave the browser; the server rounds again (DEC-004).
(function () {
    const box = document.querySelector('[data-location-picker]');
    if (!box) return;

    const latInput = box.querySelector('[data-latitude]');
    const lngInput = box.querySelector('[data-longitude]');
    const text = box.querySelector('[data-location-text]');
    const mapNode = box.querySelector('[data-location-map]');
    let map = null;
    let marker = null;

    const round = (value) => Math.round(value * 100) / 100;

    function setLocation(lat, lng, message) {
        latInput.value = round(lat).toFixed(2);
        lngInput.value = round(lng).toFixed(2);
        text.textContent = message + ' (about ' + latInput.value + ', ' + lngInput.value + '). Save changes to keep it.';
        if (map) {
            const point = [round(lat), round(lng)];
            if (marker) marker.setLatLng(point); else marker = L.circle(point, { radius: 600, color: '#d32f2f' }).addTo(map);
            map.setView(point, 14);
        }
    }

    // The map is optional: without Leaflet or internet tiles, the location button still works.
    if (window.L && mapNode) {
        const hasValue = latInput.value !== '' && lngInput.value !== '';
        const start = hasValue
            ? [parseFloat(latInput.value), parseFloat(lngInput.value)]
            : [parseFloat(box.dataset.centerLat), parseFloat(box.dataset.centerLng)];
        map = L.map(mapNode).setView(start, hasValue ? 14 : 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);
        if (hasValue) marker = L.circle(start, { radius: 600, color: '#d32f2f' }).addTo(map);
        map.on('click', (event) => setLocation(event.latlng.lat, event.latlng.lng, 'Location chosen on the map'));
    } else if (mapNode) {
        mapNode.remove();
    }

    box.querySelector('[data-use-location]').addEventListener('click', () => {
        if (!navigator.geolocation) {
            text.textContent = 'This browser cannot share its location. Tap the map instead.';
            return;
        }
        text.textContent = 'Getting your location…';
        navigator.geolocation.getCurrentPosition(
            (position) => setLocation(position.coords.latitude, position.coords.longitude, 'Location from your device'),
            () => { text.textContent = 'Location permission was denied or unavailable. Tap the map instead.'; },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 600000 }
        );
    });

    box.querySelector('[data-clear-location]').addEventListener('click', () => {
        latInput.value = '';
        lngInput.value = '';
        if (marker) { marker.remove(); marker = null; }
        text.textContent = 'Location cleared. Save changes to remove it.';
    });
})();
