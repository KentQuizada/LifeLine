# Notifications

## Purpose
Notify donors and requesters about emergency requests, matches, and status changes.

## Status
`done` — M2.5 done 2026-11-17: in-app alerts and email for new matches, acceptances, closures and changes by others; Alerts tab with Today/Earlier, unread badge, mark-all-read through the API; email preference in Edit profile. Rules: [[30_DECISIONS]] DEC-018. SMTP delivery checked with a local test mailbox (`docs/testing/test-cases.md` EMR-11).

## Responsibilities
- In-app Alerts tab (design C3): unread state, mark all read, Today / Earlier grouping
- Email notifications
- Notification preference toggle (design E1)
- Optional Web Push; SMS channel documented but disabled (see [[30_DECISIONS]] DEC-005)

## Dependencies
- [[BloodRequests]]
- [[Matching]]
- [[Database]]

## Runtime References
- Sending: `lifeline/app/Support/Alerts.php` (called after commit from `BloodRequest::matchDonors()`, `BloodRequest::changeStatus()`, `DonorMatch::respond()`); email: `lifeline/app/Notifications/LifeLineAlert.php`
- Alerts tab: `AlertController` (`alerts.index`, `alerts.open`), `lifeline/resources/views/alerts/index.blade.php`, `lifeline/public/js/alerts.js`; unread badge in `partials/bottom-nav.blade.php`
- API: `Api\NotificationController` (`api.notifications.index`, `api.notifications.read`)
- Tests: `lifeline/tests/Feature/Notifications/`

## Invariants
- A notification failure never blocks saving a request
- Users who disabled notifications receive in-app only

## Notes
- In-app alerts are rows in LifeLine's own `notifications` table, created with `App\Models\Notification::create()`; Laravel notifications are used for the mail channel only (see [[30_DECISIONS]] DEC-011). Do not add the `Notifiable` trait or the `database` channel.
- Email is sent directly with no queue worker, to keep setup simple; failures are caught and logged. Revisit only if sending makes request submission noticeably slow.

## Future Changes
- Web Push (DEC-005 stretch) and SMS are not built.
- Email is sent during the request (no queue): with many matched donors and a slow SMTP server, submitting could feel slow. Then move mail to a queue (needs a queue worker at the demo).
- Real-inbox delivery (Gmail/Mailtrap) still to be checked by the team with their SMTP details (EMR-12).
