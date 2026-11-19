# DonorMap

## Purpose
Map and geolocation for nearby available donors and hospitals without exposing exact locations.

## Status
`done` (location only) — 2026-11-19: the map screens were removed on the instructor's request ([[30_DECISIONS]] DEC-023). What remains is the donor's approximate location for matching. M2.4 was done 2026-11-17 with the map (DEC-017).

## Responsibilities
- Donor's approximate location (about 1 km): browser Geolocation API, or the hospital nearest to the donor when permission is denied
- No map, no map API, no Leaflet (DEC-023)

## Dependencies
- [[Donors]]
- [[RestApi]]

## Runtime References
- Location capture: `ProfileController@edit/update` (`approx_latitude`/`approx_longitude`, hospital list), `lifeline/public/js/location-picker.js`
- Settings: `lifeline/config/lifeline.php` (`location_bounds`)
- Tests: `lifeline/tests/Feature/Profile/DonorLocationTest.php`, `lifeline/tests/Feature/SystemCompletionTest.php` (no map, hospital fallback)

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
