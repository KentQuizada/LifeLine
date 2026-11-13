# AdminDashboard

## Purpose
Administrator tools for managing accounts, requests, hospitals, and viewing statistics and reports.

## Status
`unstarted`

## Responsibilities
- Donor verification queue
- User / donor management; request management with status override
- Hospital (reference data) management
- Statistics, charts (Chart.js), and exportable reports

## Dependencies
- [[Authentication]]
- [[BloodRequests]]
- [[Donors]]
- [[Database]]

## Runtime References
- _TBD_ (no source yet — set when implemented)

## Invariants
- Every admin route requires the admin role
- Admin status changes are recorded in request_history with the acting admin

## Notes
- Not covered by the mobile design; uses a desktop layout in the same brand.

## Future Changes
- _TBD_
