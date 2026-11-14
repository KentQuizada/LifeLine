# DonorMap

## Purpose
Map and geolocation for nearby available donors and hospitals without exposing exact locations.

## Status
`unstarted`

## Responsibilities
- Leaflet + OpenStreetMap map (design C2, home mini-map C1)
- Browser Geolocation API with manual city/pin fallback
- Blood type filter and donor list with rounded distance
- Hospital markers

## Dependencies
- [[Donors]]
- [[RestApi]]

## Runtime References
- _TBD_ (no source yet — set when implemented)

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
- _TBD_
