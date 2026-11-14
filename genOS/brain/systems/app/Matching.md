# Matching

## Purpose
Find compatible, eligible, nearby potential donors for a blood request.

## Status
`unstarted`

## Responsibilities
- Red-cell ABO/Rh compatibility table (recipient -> acceptable donor types)
- Filter: verified, available, location shared, outside donation interval
- Rank by distance from the request location (Haversine)
- donor_matches lifecycle: notified -> accepted / declined
- Reveal requester and donor contact only after acceptance

## Dependencies
- [[Donors]]
- [[BloodRequests]]
- [[Notifications]]
- [[DonorMap]] (distance)

## Runtime References
- _TBD_ (no source yet — set when implemented)

## Invariants
- Results are always labeled potential donors; disclaimer shown (see [[30_DECISIONS]] DEC-007)
- A donor is matched at most once per request (UNIQUE request_id + donor_id)

## Notes
- Rule-based matching is the honest "intelligent matching": explainable, testable, no decorative AI.
- Compatibility covers red cells / whole blood only; plasma and platelets differ and are out of scope.
- Request location = the selected hospital's coordinates (DEC-014); rank donors by distance from the hospital.
- Accepting a match sets the request to `donor_found` with `$bloodRequest->changeStatus('donor_found', $donorUser, ...)`, never by assigning `status` (fix the in-memory mutation noted in [[BloodRequests]] first).
- Start donor queries from `Donor::searchable()` (verified + available + sharing location).

## Future Changes
- _TBD_
