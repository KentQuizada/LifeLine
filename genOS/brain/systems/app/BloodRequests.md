# BloodRequests

## Purpose
Emergency blood requests: creation, unique reference numbers, status lifecycle, history, and tracking.

## Status
`active` — M2.2 done 2026-11-14: SOS form (D1), request sent (D2), my requests with cancel and mark fulfilled, status history, public track-by-reference page (via `/api/v1`). Checked in the browser at 375 px and laptop width.

## Responsibilities
- SOS request form (design D1): blood type, units, urgency (Critical / Urgent), hospital, location
- Reference number generation and uniqueness (see [[30_DECISIONS]] DEC-006)
- Status lifecycle and request_history audit trail
- Track-by-reference page (not in design; to be added)
- Request sent screen (design D2) must show the reference number

## Dependencies
- [[Database]]
- [[Matching]]
- [[Notifications]]

## Runtime References
- Model: `lifeline/app/Models/BloodRequest.php` — `BloodRequest::submit()`, `changeStatus()`, `canChangeTo()`, `STATUS_LABELS`, `TRANSITIONS`
- Web: `lifeline/app/Http/Controllers/BloodRequestController.php`; views `lifeline/resources/views/requests/` (`create`, `show`, `index`, `track`), `partials/request-status.blade.php`
- API: `lifeline/app/Http/Controllers/Api/TrackRequestController.php`; tracking page script `lifeline/public/js/track.js`
- Tests: `lifeline/tests/Feature/Requests/` (`RequestLifecycleTest`, `SubmitRequestTest`, `MyRequestsTest`, `TrackRequestTest`)

## Invariants
- Reference numbers are unique at the database level (UNIQUE index) and in backend validation
- Every status change writes a request_history row
- Public tracking shows status only, never patient or contact details
- Units must be a positive integer within a sane maximum
- Requests are created only with `BloodRequest::submit()` and statuses changed only with `changeStatus()` (row lock + history row in one transaction)
- Tracking shows status only (DEC-014 field list): reference, status, urgency, blood type, hospital name, submitted/updated times, status history (status and time)
- Request routes act only on the signed-in user's own requests (looked up through `$request->user()->bloodRequests()`; others get 404)

## Notes
- Statuses (final, M1.2): open, donor_found, fulfilled, cancelled, expired. Units 1–10 enforced by CHECK. Generator: `BloodRequest::generateReferenceNumber()`; collision retry on save is built in M2.2.
- `donor_found` comes only from the first donor acceptance (`DonorMatch::respond()`, see [[Matching]]). `BloodRequest::LOOKING_FOR_DONORS` (`open`, `donor_found`) are the statuses in which matching runs and donors may answer. Since M2.3, a refused `changeStatus()` leaves the model in memory untouched.
- Since M2.2, `status`, `reference_number` and `requester_id` are not fillable; factories run unguarded so tests and seeders may still set them. Request rules: [[30_DECISIONS]] DEC-014.

## Future Changes
- Done in M3.2: forms marked `data-submit-once` send once (`public/js/lifeline.js`); the track box trims a pasted reference (BUG-14, BUG-15).
- Not yet tested: donor-role cancel, cancel from `donor_found`. `submit()` throws a `RuntimeException` (500) after 5 reference collisions (negligible).
