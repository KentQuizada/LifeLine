# Authentication

## Purpose
Identity, registration, login, verification, and role-based access for donors, requesters, and administrators.

## Status
`unstarted`

## Responsibilities
- 3-step registration from the design (B1 role + basic info, B2 medical info for donors, B3 password + consent + terms)
- Login by email or mobile number; logout; password reset
- Email verification; admin verification of donor accounts
- Role gates: donor, requester, admin

## Dependencies
- [[Database]] (users, donors, administrators)
- [[AdminDashboard]] (verification queue)

## Runtime References
- _TBD_ (no source yet — set when implemented)

## Invariants
- Passwords are stored only as hashes (framework hashing)
- Email and mobile number are each unique across users
- Unverified donors never appear in matching or on the map
- One credentials table only (see [[30_DECISIONS]] DEC-003)

## Notes
- Design B4 shows "Continue with Google": optional stretch (free OAuth), not core.
- Design B3 government ID upload was dropped (see [[30_DECISIONS]] DEC-009); donors are verified manually by admins.

## Future Changes
- _TBD_
