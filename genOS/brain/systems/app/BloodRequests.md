# BloodRequests

## Purpose
Emergency blood requests: creation, unique reference numbers, status lifecycle, history, and tracking.

## Status
`unstarted`

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
- _TBD_ (no source yet — set when implemented)

## Invariants
- Reference numbers are unique at the database level (UNIQUE index) and in backend validation
- Every status change writes a request_history row
- Public tracking shows status only, never patient or contact details
- Units must be a positive integer within a sane maximum

## Notes
- Statuses (final, M1.2): open, donor_found, fulfilled, cancelled, expired. Units 1–10 enforced by CHECK. Generator: `BloodRequest::generateReferenceNumber()`; collision retry on save is built in M2.2.
- M2.2: `status`, `reference_number` and `requester_id` are fillable on the model, so create requests via `$request->user()->bloodRequests()->create($validated)` and set reference and status explicitly; never pass raw request input.

## Future Changes
- _TBD_
