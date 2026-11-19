// Edit profile: the donor's approximate location (M2.4). Coordinates are rounded to 2 decimals
// (about 1 km) before they leave the browser; the server rounds again (DEC-004).
// No map (DEC-023): the device location, or the hospital nearest to the donor.
(function () {
    const box = document.querySelector('[data-location-picker]');
    if (!box) return;

    const latInput = box.querySelector('[data-latitude]');
    const lngInput = box.querySelector('[data-longitude]');
    const text = box.querySelector('[data-location-text]');
    const hospital = box.querySelector('[data-near-hospital]');

    const round = (value) => Math.round(value * 100) / 100;

    function setLocation(lat, lng, message) {
        latInput.value = round(lat).toFixed(2);
        lngInput.value = round(lng).toFixed(2);
        text.textContent = message + ' (about ' + latInput.value + ', ' + lngInput.value + '). Save changes to keep it.';
    }

    box.querySelector('[data-use-location]').addEventListener('click', () => {
        if (!navigator.geolocation) {
            text.textContent = 'This browser cannot share its location. Pick the hospital nearest to you instead.';
            return;
        }
        text.textContent = 'Getting your location…';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                hospital.value = '';
                setLocation(position.coords.latitude, position.coords.longitude, 'Location from your device');
            },
            () => { text.textContent = 'Location permission was denied or unavailable. Pick the hospital nearest to you instead.'; },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 600000 }
        );
    });

    hospital.addEventListener('change', () => {
        const option = hospital.selectedOptions[0];
        if (!option || option.value === '') return;
        setLocation(parseFloat(option.dataset.lat), parseFloat(option.dataset.lng), 'Near ' + option.textContent);
    });

    box.querySelector('[data-clear-location]').addEventListener('click', () => {
        latInput.value = '';
        lngInput.value = '';
        hospital.value = '';
        text.textContent = 'Location cleared. Save changes to remove it.';
    });
})();
