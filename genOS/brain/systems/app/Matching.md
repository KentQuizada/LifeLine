# Matching

## Purpose
Find compatible, eligible, nearby potential donors for a blood request.

## Status
`active` — M2.3 done 2026-11-14 on branch `feat/m2.3-matching`: compatibility table, eligibility, distance ranking, matching on submit and "Find more donors", donor "Requests for you" page with accept/decline, contact reveal after acceptance, requester list through `/api/v1`. Checked in the browser at 375 px and 1280 px.

## Responsibilities
- Red-cell ABO/Rh compatibility table (recipient -> acceptable donor types)
- Filter: verified, available, location shared, coordinates set, outside donation interval, not the requester
- Rank by distance from the request location (Haversine), nearest first, up to the match limit
- donor_matches lifecycle: notified -> accepted / declined
- Reveal requester and donor contact only after acceptance

## Dependencies
- [[Donors]]
- [[BloodRequests]]
- [[Notifications]]
- [[DonorMap]] (distance)

## Runtime References
- Models: `lifeline/app/Models/BloodRequest.php` (`COMPATIBLE_DONORS`, `LOOKING_FOR_DONORS`, `matchDonors()`), `lifeline/app/Models/Donor.php` (`scopeEligibleFor()`), `lifeline/app/Models/DonorMatch.php` (`respond()`, `ANSWERS`, `STATUS_LABELS`)
- Distance: `lifeline/app/Support/Distance.php`; rules: `lifeline/config/lifeline.php` (`donation_interval_months`, `match_limit`)
- Donor side: `lifeline/app/Http/Controllers/DonorMatchController.php`, view `lifeline/resources/views/matches/index.blade.php`, home button in `home.blade.php`
- Requester side: `BloodRequestController@store` (matches on submit) and `@findDonors`; API `Api\RequestMatchesController`; script `lifeline/public/js/matches.js`
- Tests: `lifeline/tests/Unit/BloodCompatibilityTest.php` (all 64 pairs), `DistanceTest.php`, `lifeline/tests/Feature/Matching/`
- Plan: `docs/superpowers/plans/2026-11-14-m2.3-matching.md`; rules [[30_DECISIONS]] DEC-016

## Invariants
- Results are always labeled potential donors; disclaimer shown (see [[30_DECISIONS]] DEC-007)
- A donor is matched at most once per request (UNIQUE request_id + donor_id)
- Matches are created only by `BloodRequest::matchDonors()` and answered only by `DonorMatch::respond()`; match `status` and `responded_at` are not mass-assignable
- Every matching query starts from `Donor::searchable()` (through `eligibleFor()`) and selects only id and approximate coordinates
- Contact details only after acceptance, both ways (DEC-004): the requester sees an accepted donor's name and mobile; the accepting donor sees the patient, requester and hospital phone
- No page or API response contains donor coordinates; distances are whole km, minimum 1
- The first acceptance moves the request open -> donor_found through `changeStatus()`; `respond()` locks the request row, then the match row (always this order)
- Ties in distance are broken by donor id, so the same data always gives the same matches

## Notes
- Rule-based matching is the honest "intelligent matching": explainable, testable, no decorative AI.
- Compatibility covers red cells / whole blood only; plasma and platelets differ and are out of scope.
- Request location = the selected hospital's coordinates (DEC-014); rank donors by distance from the hospital.
- Until M2.4 captures coordinates, only sample (seeded) donors have coordinates, so only they can be matched. No city fallback (DEC-016).
- A matching error on submit is logged and does not break the request; the requester can press "Find more donors".
- No notifications are sent yet: the `notified` match rows are what M2.5 will notify.

## Future Changes
- Done in M3.2: `matchDonors()` re-reads the status under the request row lock; `respond()` refuses an acceptance from a donor who is not verified (BUG-12, BUG-13).
- Done 2026-11-17 (DEC-022): donors withdraw an acceptance; requesters record the donation on the D3 donor profile.
- Done in M3.2: "Requests for you" is paginated (15 per page).
- The donation interval (3 months, from design B2) still needs a cited source before the defense (DEC-008; TODO in `config/lifeline.php`).
