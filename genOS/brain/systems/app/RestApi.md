# RestApi

## Purpose
Versioned JSON REST API between the frontend and backend.

## Status
`active` — M2.2 done 2026-11-14: `/api/v1` set up with the first endpoint (request tracking).

## Responsibilities
- /api/v1 endpoints for map data, matching, request tracking, notifications, and availability
- Authentication: to be decided in M2.3 (the `api` route group has no session, so `/api/v1` is stateless today); consistent JSON error format; request validation; rate limiting

## Dependencies
- [[Authentication]]
- [[BloodRequests]]
- [[DonorMap]]
- [[Notifications]]

## Runtime References
- Setup: `lifeline/bootstrap/app.php` (`apiPrefix: 'api/v1'`; errors render as JSON for `api/*` or when the client expects JSON); routes in `lifeline/routes/api.php`
- First endpoint: `GET /api/v1/requests/track/{reference}` (`Api\TrackRequestController`, `throttle:30,1,track`, 404 unknown / 422 malformed); consumer `lifeline/public/js/track.js` on the `/track` page

## Invariants
- The app's own screens consume these endpoints (the API is used, not decorative)
- Validation rules are identical for web forms and API

## Notes
- Documented in Chapter 3 API Design (see [[Methodology]]).
- Each rate limit uses its own counter prefix (`throttle:max,minutes,prefix`); plain `throttle:x,y` shares one counter per user/IP across routes.
- M2.3–M2.5 add routes to `routes/api.php`. Before adding an authenticated API route, decide how it gets the user: the `web` middleware (session + CSRF) or Sanctum stateful (a new package, against DEC-001's simplicity rule).

## Future Changes
- Deployment: the track throttle keys on `$request->ip()`; behind a reverse proxy or tunnel, configure TrustProxies or every visitor shares one 30/min bucket.
