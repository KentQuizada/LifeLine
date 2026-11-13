# Donors

## Purpose
Donor profiles, medical info, location, availability, and donation history.

## Status
`unstarted`

## Responsibilities
- Donor profile CRUD (blood type, birthdate, weight, last donation date, city/municipality)
- Availability toggle (home dashboard and profile)
- Location sharing opt-in and approximate location storage
- Donation history records

## Dependencies
- [[Authentication]]
- [[Database]]
- [[DonorMap]] (location)

## Runtime References
- _TBD_ (no source yet — set when implemented)

## Invariants
- Store birthdate, not age (age is derived)
- A donor with availability off or location sharing off is excluded from map and matching
- Blood type is one of A+, A-, B+, B-, AB+, AB-, O+, O-

## Notes
- Design B2 says last donation "must be 3+ months ago to be eligible"; the interval is a config value that must be confirmed against a cited source (see [[30_DECISIONS]] DEC-008).
- Design E1 "Lives Saved" stat is not verifiable; prefer "Completed donations" unless the team keeps it as a label.

## Future Changes
- _TBD_
