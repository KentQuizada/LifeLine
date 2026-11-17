# LifeLine bug log (M3.2)

Defects found while building and testing M2.4–M3, and how they were fixed. Earlier milestones' fixes are in the git
history (`git log --grep=fix`).

| ID | Found | How found | Defect | Fix | Verified by |
|---|---|---|---|---|---|
| BUG-01 | 2026-11-17 | Automated test | Reports page crashed (500): `created_at` was ambiguous once `blood_requests` was joined with `hospitals` for the "by hospital" summary | Range filter names the table: `blood_requests.created_at` | `HospitalsAndReportsTest::test_the_report_summarises_requests_in_the_date_range` |
| BUG-02 | 2026-11-17 | Browser check (MAN-01) | Donor map listed hospitals alphabetically, so a far hospital could appear before a near one | Hospitals sorted by distance, then name | `DonorMapTest::test_hospitals_are_listed_with_distance` |
| BUG-03 | 2026-11-17 | Code review while adding the location picker | An Edit profile form sent without the location fields (old page, other client) would have erased a donor's saved location | Location is changed only when the form sends the location fields | `DonorLocationTest::test_a_form_without_location_fields_keeps_the_saved_location` |
| BUG-04 | 2026-11-17 | Code review of CSV export | A cell starting with `=`, `+`, `-` or `@` (e.g. a city typed as a formula) would run as a formula in Excel (CSV injection) | Such cells are prefixed with `'` | `HospitalsAndReportsTest::test_the_donors_csv_has_no_contact_details_and_neutralises_formulas` |
| BUG-05 | 2026-11-17 | Code review (TASK-001) | Password reset, email verification and profile update limits shared one counter per user/IP, so using one form could block another | Each limit has its own counter (third `throttle` argument) | `HardeningTest::test_forgot_password_requests_do_not_use_up_the_reset_limit` |
| BUG-06 | 2026-11-17 | Code review (Authentication carry-over) | Login had a 5-tries limit per account but no limit per network, so one address could try a few passwords on many accounts | Extra limit: 30 login attempts per minute per IP, with a friendly message | `HardeningTest::test_one_network_cannot_try_many_accounts` |
| BUG-07 | 2026-11-17 | Code review (Authentication carry-over) | Login and password reset matched emails case-insensitively only because of the database collation | Emails are lower-cased before login and reset, like at registration | `HardeningTest::test_login_accepts_an_email_typed_in_capitals` |
| BUG-08 | 2026-11-17 | Code review (Authentication carry-over) | After a password reset, other logged-in browsers stayed logged in | `AuthenticateSession` middleware ends other sessions when the password changes | `HardeningTest::test_changing_the_password_ends_other_sessions` |
| BUG-09 | 2026-11-17 | Code review (Authentication carry-over) | Two sign-ups with the same email at the same moment gave a 500 error | The duplicate is caught and the user goes back to step 1 with a message | `HardeningTest::test_a_duplicate_saved_at_the_last_moment_sends_the_user_back_to_step_one` |
| BUG-10 | 2026-11-17 | Code review (Authentication carry-over) | Going back to step 1 of registration and resubmitting it erased the medical answers even when the role stayed the same | Step-2 answers are kept unless the role changes | `HardeningTest::test_resubmitting_step_one_with_the_same_role_keeps_the_medical_answers` |
| BUG-11 | 2026-11-17 | Code review (Authentication carry-over) | An admin could flip an already verified donor to rejected from a stale pending page | Verify/reject act on pending donors only | `HardeningTest::test_an_admin_cannot_reject_a_donor_who_is_already_verified` |
| BUG-12 | 2026-11-17 | Code review (Matching carry-over) | A request cancelled at the same moment as "Find more donors" could still notify new donors | Matching re-reads the status under a row lock | `HardeningTest::test_a_closed_request_gets_no_new_matches_even_from_a_stale_copy` |
| BUG-13 | 2026-11-17 | Code review (Matching carry-over) | A donor whose verification was withdrawn after matching could still accept and see contact details | Only verified donors can accept | `HardeningTest::test_a_donor_who_is_no_longer_verified_cannot_accept` |
| BUG-14 | 2026-11-17 | Code review (BloodRequests carry-over) | A double click on "Send emergency request" could create two requests | Forms marked `data-submit-once` (emergency request, find more donors, create account) send only once (`public/js/lifeline.js`) | Browser check 2026-11-17; HardeningTest::test_emergency_and_find_more_forms_send_only_once |
| BUG-15 | 2026-11-17 | Code review (BloodRequests carry-over) | A pasted reference with spaces around it was refused by the track box | Spaces are trimmed while typing; box allows 24 characters | Browser check 2026-11-17 |
| BUG-16 | 2026-11-17 | Code review (Donors carry-over) | Birthdate could be edited to a date after recorded donations; the availability message said "available" to donors who are not searchable | Birthdate must be before the first donation; the message explains what is still missing | `HardeningTest::test_the_birthdate_cannot_move_after_a_recorded_donation, HardeningTest::test_a_pending_donor_switching_available_is_told_they_are_not_searchable_yet` |
| BUG-17 | 2026-11-17 | Automated test (flaky) | `DonorAnswersTest` used a random-status donor for a second acceptance, which now fails 1 time in 4 because pending donors cannot accept (BUG-13) | The test creates a verified donor | `DonorAnswersTest::test_a_second_acceptance_keeps_donor_found_without_another_history_row` |

## Responsive and accessibility pass

Checked on 2026-11-17 (see MAN-01 – MAN-04 in [test-cases.md](test-cases.md)):

- No sideways scrolling at 375 px on Map, Alerts and Edit profile.
- New screens use real labels for every input, `role="status"` with `aria-live` for map/alerts/location messages,
  `aria-current` on the active tab, an "unread" text for screen readers on unread alerts, and `aria-label`s on the maps
  and charts. Map and alert text from the API is inserted with `textContent` only (no HTML injection).
- Admin tables sit inside `.table-responsive`; the admin menu scrolls sideways on small screens.

Contrast fixes (2026-11-17, WCAG 2.1 AA ratios computed from the rendered colours in the browser):

| Element | Before | After |
|---|---|---|
| Small white text in the red hero (stat labels) | about 4.0:1 | 4.6:1 (hero starts at `#c62828`, text fully white) |
| Hero secondary text | about 4.0:1 | 5.2:1 |
| Availability switch, off (grey track on white card) | 1.5:1 | 4.7:1 (`#6c757d` track) |
| Availability switch, on (track on green card / knob on track) | 2.3:1 | 5.1:1 (white track, green knob) |
| Section labels on the grey page | 4.3:1 | 5.6:1 (`#5c636a`) |

Still open: a real keyboard-only walkthrough by a team member (MAN-07 in [test-cases.md](test-cases.md)).
