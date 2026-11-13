# Notifications

## Purpose
Notify donors and requesters about emergency requests, matches, and status changes.

## Status
`unstarted`

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
- _TBD_ (no source yet — set when implemented)

## Invariants
- A notification failure never blocks saving a request
- Users who disabled notifications receive in-app only

## Notes
- Email is sent directly with no queue worker, to keep setup simple; failures are caught and logged. Revisit only if sending makes request submission noticeably slow.

## Future Changes
- _TBD_
