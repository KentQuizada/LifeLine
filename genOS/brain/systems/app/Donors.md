# Donors

## Purpose
Donor profiles, medical info, location, availability, and donation history.

## Status
`active` — M2.1 done 2026-11-14 on branch `feat/m2.1-donors`: my profile (E1), edit profile and medical info, availability toggle, home dashboard (C1), donation history, and the `Donor::searchable()` gate, with feature tests. Location capture (coordinates) done in M2.4 (2026-11-17): Edit profile location picker, home prompt when sharing is on but no location is set.

## Responsibilities
- Donor profile CRUD (blood type, birthdate, weight, city; last donation derived from donations)
- Availability toggle (home dashboard and profile)
- Location sharing opt-in and approximate location storage
- Donation history records (donor-entered, self-reported)

## Dependencies
- [[Authentication]]
- [[Database]]
- [[DonorMap]] (location)

## Runtime References
- Controllers: `lifeline/app/Http/Controllers/ProfileController.php`, `DonationController.php`, `AvailabilityController.php`, `HomeController.php`
- Model: `lifeline/app/Models/Donor.php` (`searchable()` scope, `lastDonationOn()`)
- Views: `lifeline/resources/views/home.blade.php`, `profile/`, `donations/`, partials `donor-status` and `availability-card`
- Routes: `lifeline/routes/web.php` (`profile.*` for donors and requesters; `donations.*` and `availability.update` for donors)
- Tests: `lifeline/tests/Feature/Profile/`, `lifeline/tests/Feature/Donors/`, `lifeline/tests/Feature/BottomNavTest.php`
- Plan: `docs/superpowers/plans/2026-11-14-m2.1-donors.md`

## Invariants
- Store birthdate, not age (age is derived)
- Blood type is one of A+, A-, B+, B-, AB+, AB-, O+, O-
- Matching (M2.3, through `Donor::eligibleFor()`) and the map (M2.4) start every donor query from `Donor::searchable()` (admin-verified, available, sharing location). A donor with availability off, location sharing off, or not verified never appears there.
- `searchable()` does not guarantee coordinates: until M2.4 captures them, a searchable donor may have null `approx_latitude`/`approx_longitude`. The map query must also require non-null coordinates.
- Turning location sharing off clears the stored approximate coordinates (Data Privacy Act data minimisation, [[30_DECISIONS]] DEC-004).
- A changed blood type sends a verified or rejected donor back to `pending` for admin review; matching relies on it.
- Donor pages act only on the signed-in user; no URL carries a user or donor id. Donors may add and remove only their own self-reported donations; donations recorded through a LifeLine request (future, see [[Matching]]) cannot be removed by the donor.

## Notes
- Email is not editable in the profile (no re-verification flow). Name, mobile (normalized, unique), and donor medical info are editable.
- Design B2 says last donation "must be 3+ months ago to be eligible"; the interval is a config value that must be confirmed against a cited source (see [[30_DECISIONS]] DEC-008). It is not enforced in M2.1; since M2.3 matching enforces it (`config/lifeline.php` `donation_interval_months`, 3).
- Design E1 "Lives Saved" is shown as "Completed donations" (lives saved is not verifiable).
- A rejected donor can return to `pending` by changing their blood type. Accepted: an admin must still verify them.

## Future Changes
- When donations are recorded through an accepted match (not built in M2.3; see [[Matching]] Future Changes): decide how one interacts with a self-reported donation on the same date before adding a DB unique index on `donations(donor_id, donated_on)` (today only validation enforces one per date).
- Open: birthdate can be edited to a date after recorded donations; the availability flash says "available" even for donors who are not yet searchable (status card explains it).
- M3.2: hero text and switch-track contrast (accessibility pass).
