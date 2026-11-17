# DonorMap

## Purpose
Map and geolocation for nearby available donors and hospitals without exposing exact locations.

## Status
`done` — M2.4 done 2026-11-17: donor map page, `GET /api/v1/map`, location capture in Edit profile, home prompt for donors without a location. Rules: [[30_DECISIONS]] DEC-017. Checked in the browser at 375 px (denied-permission fallback, filter, tap-to-choose).

## Responsibilities
- Leaflet + OpenStreetMap map (design C2, home mini-map C1)
- Browser Geolocation API with manual city/pin fallback
- Blood type filter and donor list with rounded distance
- Hospital markers

## Dependencies
- [[Donors]]
- [[RestApi]]

## Runtime References
- Page: `lifeline/app/Http/Controllers/MapController.php`, `lifeline/resources/views/map/index.blade.php`, `lifeline/public/js/map.js`
- API: `lifeline/app/Http/Controllers/Api/DonorMapController.php` (`api.map`)
- Location capture: `ProfileController@update` (`approx_latitude`/`approx_longitude`), `lifeline/public/js/location-picker.js`
- Settings: `lifeline/config/lifeline.php` (`map.center`, `map.donor_limit`, `location_bounds`)
- Vendored Leaflet 1.9.4: `lifeline/public/vendor/leaflet/`
- Tests: `lifeline/tests/Feature/Map/DonorMapTest.php`, `lifeline/tests/Feature/Profile/DonorLocationTest.php`

## Invariants
- No response sent to the browser contains a donor's exact coordinates (see [[30_DECISIONS]] DEC-004)
- Map data is visible only to logged-in users
- Works when location permission is denied

## Notes
- Round donor coordinates to 2 decimals in PHP before saving; do not rely on MariaDB's silent DECIMAL(5,2) rounding, and never log or return raw coordinates.
- API responses return explicit field lists (or Resources), never serialized `User`/`Donor` models: those would leak email, mobile number and birthdate before a donor accepts (DEC-004).
- Reuse `App\Support\Distance::km()` (Haversine, from M2.3) for distances, and show them rounded to whole km like matching does.
- OSM tile and Nominatim usage policies apply (attribution, light use, identifying User-Agent); avoid bulk geocoding.

## Future Changes
- Home mini-map (design C1) is not built; Home links to the Map tab instead.
- Donors in the same ~1 km square are drawn as one circle with a count; in very sparse areas one circle may still hint at a household. Consider coarser rounding if the team worries about this.
- OSM tile usage policy: fine for a class demo; a public deployment needs its own tile provider or caching.
