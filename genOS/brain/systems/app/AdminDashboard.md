# AdminDashboard

## Purpose
Administrator tools for managing accounts, requests, hospitals, and viewing statistics and reports.

## Status
`done` — M2.6 done 2026-11-17: dashboard with cards and Chart.js charts, donor/user/request lists with filters, donor and request detail, status override, hospital CRUD, reports with CSV exports. Rules: [[30_DECISIONS]] DEC-019. Checked in the browser at 1280 px.

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
- Controllers: `lifeline/app/Http/Controllers/Admin/` (Dashboard, DonorVerification, Donor, User, Request, Hospital, Report)
- Statistics: `lifeline/app/Support/Statistics.php` (cards, `api.admin.stats`, tests)
- Views: `lifeline/resources/views/admin/`; menu `partials/admin-nav.blade.php`; charts `lifeline/public/js/admin-dashboard.js`, Chart.js 4.4.1 in `lifeline/public/vendor/chartjs/`
- Tests: `lifeline/tests/Feature/Admin/`

## Invariants
- Every admin route requires the admin role
- Admin status changes are recorded in request_history with the acting admin

## Notes
- Not covered by the mobile design; uses a desktop layout in the same brand.

## Future Changes
- No admin account management (create admins, deactivate users); admins come from the seeder.
- Donor verification and hospital edits are not written to an audit table (only verified_by/verified_at on donors).
