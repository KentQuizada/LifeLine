# RestApi

## Purpose
Versioned JSON REST API between the frontend and backend.

## Status
`unstarted`

## Responsibilities
- /api/v1 endpoints for map data, matching, request tracking, notifications, and availability
- Authentication via the normal login session (no Sanctum tokens unless a mobile client is added); consistent JSON error format; request validation; rate limiting

## Dependencies
- [[Authentication]]
- [[BloodRequests]]
- [[DonorMap]]
- [[Notifications]]

## Runtime References
- _TBD_ (no source yet — set when implemented)

## Invariants
- The app's own screens consume these endpoints (the API is used, not decorative)
- Validation rules are identical for web forms and API

## Notes
- Documented in Chapter 3 API Design (see [[Methodology]]).

## Future Changes
- _TBD_
