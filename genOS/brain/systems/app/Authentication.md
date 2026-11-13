# Authentication

## Purpose
Identity, registration, login, verification, and role-based access for donors, requesters, and administrators.

## Status
`active` — M1.3 done 2026-11-14 on branch `feat/m1.3-auth`: register, login, logout, email verification, password reset, role gates and admin donor verification, with feature tests.

## Responsibilities
- 3-step registration from the design (B1 role + basic info, B2 medical info for donors, B3 password + consent + terms)
- Login by email or mobile number; logout; password reset
- Email verification; admin verification of donor accounts
- Role gates: donor, requester, admin

## Dependencies
- [[Database]] (users, donors, administrators)
- [[AdminDashboard]] (verification queue)

## Runtime References
- Controllers: `lifeline/app/Http/Controllers/Auth/`, `lifeline/app/Http/Controllers/Admin/DonorVerificationController.php`
- Role gate: `lifeline/app/Http/Middleware/EnsureUserHasRole.php` (alias `role`, in `lifeline/bootstrap/app.php`)
- Mobile normalizer: `lifeline/app/Support/MobileNumber.php`
- Routes: `lifeline/routes/web.php`; password rules and the `registration` rate limiter: `lifeline/app/Providers/AppServiceProvider.php`
- Tests: `lifeline/tests/Feature/Auth/`, `lifeline/tests/Feature/Admin/DonorVerificationTest.php`, `lifeline/tests/Unit/MobileNumberTest.php`

## Invariants
- Passwords are stored only as hashes (framework hashing)
- Email and mobile number are each unique across users
- Unverified donors never appear in matching or on the map
- One credentials table only (see [[30_DECISIONS]] DEC-003)

## Notes
- Design B4 shows "Continue with Google": optional stretch (free OAuth), not core.
- Design B3 government ID upload was dropped (see [[30_DECISIONS]] DEC-009); donors are verified manually by admins.
- M1.3: normalize mobile numbers to one format (e.g. `09XXXXXXXXX`) before validation on both registration and login; the UNIQUE index only catches exact matches. Email uniqueness is already case-insensitive (utf8mb4_unicode_ci).
- M1.3 design (plan `docs/superpowers/plans/2026-11-14-m1.3-auth.md`): one login form for every role (admins are sent to their area); the same error for an unknown account and a wrong password; lock for 60 s after 5 failed attempts per login + IP; email verification is required for every app page; donor admin verification affects matching and the map, not login. `donors.verified_by` / `verified_at` record the reviewing admin and time for both verify and reject.
- Eligibility thresholds (age, weight, donation interval) are not checked at registration. They need a cited source ([[30_DECISIONS]] DEC-008) and belong to [[Matching]].
- M1.3: `users.role` is not fillable. Registration sets it explicitly from a whitelist (`donor`, `requester`, never `admin`) and creates the user and donor profile in one `DB::transaction()`.

- Anti-enumeration: login, forgot-password and reset give the same answer whether or not an account exists. Registration must say "already taken", so its POSTs are rate-limited instead (named limiter `registration`, 20/min per IP, its own counter); forgot/reset POSTs use `throttle:6,1`.
- Admin login uses the same form; admins land on the donor verification queue and get no bottom nav.

## Future Changes
Carry-forwards from the M1.3 final review (deferred, not blocking):
- Before production: end other sessions on password reset (`auth.session` middleware); `SESSION_SECURE_COOKIE=true`, `APP_DEBUG=false`; record consent (`privacy_consent_at` / `terms_accepted_at`, Data Privacy Act evidence; best done with the profile in M2.1).
- M2.5: queue verification/reset mail (also removes a small timing difference on forgot-password).
- M2.6: verify/reject should only act on `pending` donors (today an admin can flip a verified donor to rejected).
- M3.1: unique-check/insert race returns 500 (catch the unique violation → back to step 1); lowercase email at login/reset (case-insensitivity currently relies on the collation); per-IP ceiling on `POST /login`; step-1 resubmit clears step-2 answers even if the role is unchanged; extra tests (admin end-to-end login, token reuse, location_sharing default, session regeneration, rejected-donor home).
