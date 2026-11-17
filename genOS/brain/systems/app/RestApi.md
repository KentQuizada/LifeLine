# RestApi

## Purpose
Versioned JSON REST API between the frontend and backend.

## Status
`active` — M2.2 done 2026-11-14: `/api/v1` set up with request tracking. M2.3 done 2026-11-14: sign-in decided (DEC-015) and the first signed-in endpoint (potential donors for a request). M2.4–M2.6 done 2026-11-17: map, notifications (first API write, with CSRF) and admin statistics endpoints. Test collection: `docs/testing/api-tests.http`.

## Responsibilities
- /api/v1 endpoints for map data, matching, request tracking, notifications, and availability
- Authentication: signed-in routes use the `web` middleware group, i.e. the normal login session (DEC-015); public routes stay stateless. Consistent JSON error format; request validation; rate limiting

## Dependencies
- [[Authentication]]
- [[BloodRequests]]
- [[DonorMap]]
- [[Notifications]]

## Runtime References
- Setup: `lifeline/bootstrap/app.php` (`apiPrefix: 'api/v1'`; errors render as JSON for `api/*` or when the client expects JSON); routes in `lifeline/routes/api.php`
- First endpoint: `GET /api/v1/requests/track/{reference}` (`Api\TrackRequestController`, `throttle:30,1,track`, 404 unknown / 422 malformed); consumer `lifeline/public/js/track.js` on the `/track` page
- Signed-in endpoint: `GET /api/v1/requests/{bloodRequest}/matches` (`Api\RequestMatchesController`; `web`, `auth`, `verified`, `role:donor,requester`, `throttle:60,1,matches`; 401 guest, 403 wrong role or unverified email, 404 someone else's request); consumer `lifeline/public/js/matches.js` on the request page
- M2.4–M2.6 endpoints: `GET /api/v1/map` (`throttle:60,1,map`), `GET /api/v1/notifications` (`alerts`), `POST /api/v1/notifications/read` (`alerts-read`; CSRF token sent as `X-CSRF-TOKEN` from the layout's `csrf-token` meta tag), `GET /api/v1/admin/stats` (`role:admin`, `admin-stats`)
- `lifeline/app/Http/Middleware/ForceJsonResponse.php`, prepended to the `api` group in `bootstrap/app.php`: every `/api/v1` request is treated as asking for JSON, so middleware that checks `expectsJson()` (such as `verified`) answers with JSON instead of a redirect

## Invariants
- The app's own screens consume these endpoints (the API is used, not decorative)
- Validation rules are identical for web forms and API
- Every `/api/v1` answer is JSON, including auth and verification errors (`ForceJsonResponse` + `shouldRenderJsonWhen`)
- Signed-in routes look ids up through the signed-in user, so other people's ids give 404; responses use explicit field lists, never serialized models

## Notes
- Documented in Chapter 3 API Design (see [[Methodology]]).
- Each rate limit uses its own counter prefix (`throttle:max,minutes,prefix`); plain `throttle:x,y` shares one counter per user/IP across routes.
- M2.4–M2.5 add routes to `routes/api.php`; signed-in ones go in the `['web', 'auth', 'verified', 'role:...']` group (DEC-015). API writes send the CSRF token: the layout has `<meta name="csrf-token">` and plain-JS files send it as `X-CSRF-TOKEN` (done in M2.5, `public/js/alerts.js`).
- Tests: `actingAs()` skips the session, so `RequestMatchesTest` checks the route middleware directly to prove the session group is attached.

## Future Changes
- Deployment: the track throttle keys on `$request->ip()`; behind a reverse proxy or tunnel, configure TrustProxies or every visitor shares one 30/min bucket.
