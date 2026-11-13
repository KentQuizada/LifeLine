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
- Proposed statuses: pending -> matching -> donor_found -> fulfilled; plus cancelled and expired. Final list confirmed in M1.2.

## Future Changes
- _TBD_
