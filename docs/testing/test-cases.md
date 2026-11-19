# LifeLine test cases (M3.1)

Test cases for the nine required test categories, with expected and actual results.

- **Automated** cases run in PHPUnit (`cd lifeline && php artisan test`). The evidence column names the test class and
  method. Actual results are from the run on **2026-11-17**: **462 tests, 1,457 assertions, 0 failures**
  (full list: [automated-test-results.txt](automated-test-results.txt)).
- **Manual** cases (`MAN-…`) were run in a browser on 2026-11-17 against `php artisan serve` with the sample data, at
  375 px (phone) and 1280 px (laptop) widths.
- **Not run** cases need people (usability testing) or the team's own setup (real email inbox). Their actual result stays
  empty until someone runs them; never fill it in from memory.

Abbreviations: P = pass, F = fail, NR = not run yet.

## 1. Functional testing

| ID | Feature | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|---|
| FUN-01 | Donor profile | Donor edits name, mobile, blood type, weight, city | Saved; a changed blood type sends the donor back to admin verification | As expected | P | `EditProfileTest` |
| FUN-02 | Availability | Donor toggles availability on Home | Saved; unavailable donors leave the map and matching | As expected | P | `AvailabilityTest`, `SearchableDonorsTest` |
| FUN-03 | Donation history | Donor adds and removes a self-reported donation | Saved; one per date; only own records | As expected | P | `DonationHistoryTest` |
| FUN-04 | Donor map | Requester opens Map, filters O-, presses Near me | Approximate circles, list with whole-km distances, hospitals nearest first | As expected | P | Removed: map screens taken out on the instructor's request (DEC-023) |
| FUN-05 | Location capture | Donor taps the map in Edit profile and saves | Coordinates saved rounded to 2 decimals (about 1 km) | As expected | P | `DonorLocationTest`, MAN-03 |
| FUN-06 | Alerts | Donor opens Alerts, marks all read | Today/Earlier groups, unread badge clears | As expected | P | `AlertsTabTest`, MAN-02 |
| FUN-07 | Admin dashboard | Admin opens Dashboard | Number cards and 3 charts load | As expected | P | `AdminDashboardTest`, MAN-04 |
| FUN-08 | Hospital management | Admin adds, edits, deletes a hospital | CRUD works; a hospital with requests cannot be deleted | As expected | P | `HospitalsAndReportsTest` |
| FUN-09 | Reports | Admin picks a date range and downloads CSVs | Summary per status/blood type/hospital; CSV without contact details | As expected | P | `HospitalsAndReportsTest`, MAN-04 |
| FUN-10 | Whole demo | Requester → donor → requester → admin flow on fresh demo data | Every step succeeds | As expected | P | `DemoScriptTest` |

## 2. Database testing

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| DB-01 | Insert a second request with an existing reference number directly in SQL | Rejected by the UNIQUE index | Rejected | P | `BloodRequestsTableTest::test_duplicate_reference_number_is_rejected_by_database` |
| DB-02 | Insert units 0 and 11 | Rejected by the CHECK constraint (1–10) | Rejected | P | `BloodRequestsTableTest` |
| DB-03 | Insert an invalid blood type or status | Rejected by the ENUM | Rejected | P | `DonorsTableTest`, `BloodRequestsTableTest` |
| DB-04 | Insert a request for a hospital that does not exist | Rejected by the foreign key | Rejected | P | `BloodRequestsTableTest::test_request_requires_existing_hospital` |
| DB-05 | Insert a duplicate email, mobile number or hospital name | Rejected by UNIQUE | Rejected | P | `UsersAndHospitalsTest` |
| DB-06 | Save an exact coordinate for a donor | Stored rounded to 2 decimals | Rounded | P | `DonorsTableTest::test_exact_coordinates_are_rounded_to_about_one_kilometre` |
| DB-07 | Migrate and seed a fresh database | Sample and demo data load; every request has history | Loaded | P | `SampleDataTest`, `DemoScriptTest` |
| DB-08 | Compare the migrated schema with the ERD | Tables and columns match | Match | P | `ErdMatchesSchemaTest` |
| DB-09 | Admin statistics against database counts | Every number equals a COUNT on the table | Equal | P | `AdminDashboardTest::test_statistics_match_database_counts` |

## 3. API testing

Collection: [api-tests.http](api-tests.http).

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| API-01 | GET `/api/v1/requests/track/{reference}` | 200 with status and history only, no patient details | As expected | P | `TrackRequestTest` |
| API-02 | Track an unknown / malformed reference | 404 / 422 JSON | As expected | P | `TrackRequestTest` |
| API-03 | GET `/api/v1/map` as a guest; as an admin | 401 / 403 JSON | As expected | P | Removed: map screens taken out on the instructor's request (DEC-023) |
| API-04 | GET `/api/v1/map` as a requester | Donors with blood type, 2-decimal coordinates and distance only; no names, emails or phones | As expected | P | Removed: map screens taken out on the instructor's request (DEC-023) |
| API-05 | GET `/api/v1/requests/{id}/matches` | Nearest first; contact only after acceptance; others' requests 404 | As expected | P | `RequestMatchesTest` |
| API-06 | GET `/api/v1/notifications`; POST `/api/v1/notifications/read` | Own alerts only; marks only own alerts | As expected | P | `AlertsTabTest` |
| API-07 | POST mark-read from the browser with the CSRF token | 200 | 200 | P | MAN-02 (network log) |
| API-08 | GET `/api/v1/admin/stats` as admin / donor / guest | 200 / 403 / 401 | As expected | P | `AdminDashboardTest` |
| API-09 | Unknown `/api/v1` path | JSON 404 | As expected | P | `TrackRequestTest::test_unknown_api_paths_answer_in_json` |

## 4. Input validation testing

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| VAL-01 | Register with an invalid mobile number, weight or birthdate | Field errors shown; nothing saved | As expected | P | `RegistrationTest` |
| VAL-02 | SOS form with missing fields, 0 or 11 units, unknown hospital | Field errors | As expected | P | `SubmitRequestTest::test_invalid_requests_are_rejected` |
| VAL-03 | Try to set status, reference or requester from the SOS form | Ignored | Ignored | P | `SubmitRequestTest::test_status_reference_and_requester_cannot_be_set_from_the_form` |
| VAL-04 | Donor location outside the Philippines, or latitude without longitude | Field errors | As expected | P | `DonorLocationTest` |
| VAL-05 | Map filter with blood type `Z+`, latitude without longitude | 422 with field errors | As expected | P | `DonorMapTest::test_invalid_filters_are_rejected_as_json` |
| VAL-06 | Admin override without a reason, or to "Donor found" | Field errors; status unchanged | As expected | P | `RequestOverrideTest` |
| VAL-07 | Hospital with a taken name, wrong type, latitude 40.7, empty address | Field errors | As expected | P | `HospitalsAndReportsTest::test_hospital_validation` |
| VAL-08 | Report date range with "to" before "from" | Field error | As expected | P | `HospitalsAndReportsTest` |
| VAL-09 | Browser-side validation (required, min/max, maxlength) on the forms | Browser blocks submission before the server | Attributes present on forms | P | MAN-03 |

## 5. Login and registration testing

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| AUTH-01 | Donor registers in 3 steps; requester skips the medical step | Account created, email verification sent | As expected | P | `RegistrationTest` |
| AUTH-02 | Register with an email or mobile already used (any letter case or format) | Rejected | Rejected | P | `RegistrationTest` |
| AUTH-03 | Try to register as admin | Role ignored | Ignored | P | `RegistrationTest::test_nobody_can_register_as_admin` |
| AUTH-04 | Log in with email or mobile number | Logged in | As expected | P | `LoginTest` |
| AUTH-05 | Wrong password / unknown account | Same error message for both | As expected | P | `LoginTest` |
| AUTH-06 | Six wrong passwords in a row | Locked out for a while | As expected | P | `LoginTest::test_login_is_locked_after_five_failed_attempts` |
| AUTH-07 | Unverified email opens the app | Sent to the verification notice | As expected | P | `EmailVerificationTest` |
| AUTH-08 | Donor or requester opens any admin page or admin API | 403 | 403 | P | `RoleAccessTest`, `AdminDashboardTest::test_every_admin_route_requires_the_admin_role` |
| AUTH-09 | Password reset by email link | New password works | As expected | P | `PasswordResetTest` |

## 6. Duplicate reference number testing

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| REF-01 | Generate a reference | Format `LL-YYMMDD-XXXXXX` with no ambiguous characters | As expected | P | `SampleDataTest::test_reference_number_format` |
| REF-02 | Submit when the generated reference already exists | A new reference is generated | As expected | P | `RequestLifecycleTest::test_submit_skips_a_reference_that_already_exists` |
| REF-03 | The database rejects a duplicate saved in between | Retried with a new reference | As expected | P | `RequestLifecycleTest::test_submit_retries_when_the_database_rejects_a_duplicate_reference` |
| REF-04 | Five references in a row are taken | Clear error, nothing saved | As expected | P | `RequestLifecycleTest::test_submit_gives_up_after_five_taken_references` |
| REF-05 | Duplicate inserted directly in SQL | Rejected by UNIQUE | Rejected | P | DB-01 |

## 7. Emergency request testing

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| EMR-01 | Submit an SOS request | Saved as "Looking for donors" with a first history row and the Request sent screen | As expected | P | `SubmitRequestTest`, `RequestLifecycleTest` |
| EMR-02 | Every status change | Writes request_history with who and when | As expected | P | `RequestLifecycleTest::test_every_status_change_writes_request_history` |
| EMR-03 | Closed request changed again | Refused, no history row | As expected | P | `RequestLifecycleTest` |
| EMR-04 | Matched donors are notified | In-app alert, plus email if switched on | As expected | P | `EmergencyNotificationsTest::test_a_new_request_notifies_each_matched_donor_in_app_and_by_email` |
| EMR-05 | Donor turned email off | In-app alert only | As expected | P | `EmergencyNotificationsTest` |
| EMR-06 | Email server is down | Request still saved and matched; error logged | As expected | P | `EmergencyNotificationsTest::test_an_email_failure_never_blocks_the_request` |
| EMR-07 | Donor accepts | Requester gets "Donor match found!" | As expected | P | `EmergencyNotificationsTest` |
| EMR-08 | Request cancelled | Waiting and accepted donors are told; donors who declined are not | As expected | P | `EmergencyNotificationsTest` |
| EMR-09 | Admin marks a stale request expired | History shows the admin and reason; requester notified | As expected | P | `RequestOverrideTest` |
| EMR-10 | More than 5 requests in 10 minutes | Limited | As expected | P | `SubmitRequestTest::test_submitting_is_limited_to_five_requests_per_ten_minutes` |
| EMR-11 | Send an alert email over SMTP (`MAIL_MAILER=smtp`, 127.0.0.1:2525) to a local test mailbox | Email with title, message, link and disclaimer | Delivered: From "LifeLine &lt;no-reply@lifeline.test&gt;", Subject "LifeLine: Critical request: O- blood needed", link to /alerts, disclaimer present | P | MAN-05; content also checked by `test_the_email_has_a_link_and_the_medical_disclaimer` |
| EMR-12 | Same email to a real inbox (Gmail or Mailtrap) | Arrives, not in spam | — | NR | Team: put the SMTP details in `lifeline/.env` before the defense and repeat EMR-11 |

## 8. Donor matching testing

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| MAT-01 | All 8 × 8 recipient/donor blood type pairs | Red-cell ABO/Rh compatibility table | All 64 pairs correct | P | `BloodCompatibilityTest` |
| MAT-02 | Unavailable, unverified, not sharing location, no coordinates | Not matched | Not matched | P | `MatchDonorsTest::test_only_searchable_donors_with_coordinates_are_matched` |
| MAT-03 | Donated within the last 3 months | Not matched | Not matched | P | `MatchDonorsTest::test_donors_who_gave_blood_within_the_interval_are_not_matched` |
| MAT-04 | Requester who is also a donor | Never matched to own request | As expected | P | `MatchDonorsTest` |
| MAT-05 | Many eligible donors | Up to 10 nearest, ties by lowest id | As expected | P | `MatchDonorsTest` |
| MAT-06 | Find more donors | Adds only donors not matched yet, and notifies only them | As expected | P | `MatchDonorsTest`, `EmergencyNotificationsTest::test_find_more_donors_notifies_only_the_new_donors` |
| MAT-07 | Accept / decline once; answer a closed request | Second answer and closed request refused | As expected | P | `DonorAnswersTest` |
| MAT-08 | Contact details before and after acceptance | Hidden before, shown both ways after | As expected | P | `DonorAnswersTest`, `RequestMatchesTest` |
| MAT-09 | Medical disclaimer | Shown on matches, request form, profile, map, email | As expected | P | `SubmitRequestTest`, MAN-01 |

## 9. Usability testing

| ID | Steps | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|
| USE-01 | Participants do tasks T1–T7 and answer the SUS | Mean SUS score reported with number of participants | — | NR | Team to run [sus-questionnaire.md](sus-questionnaire.md) with real participants |
| USE-02 | Phone width 375 px: Map, Alerts, Edit profile | No sideways scrolling; controls usable | Page width 375 px on all three | P | MAN-01 – MAN-03 |
| USE-03 | Laptop width 1280 px: admin pages | All admin pages load; tables fit or scroll inside their box | All 12 admin pages 200 | P | MAN-04 |
| USE-04 | Location permission denied | Map still works from Butuan City; message explains what to do | As expected | P | MAN-01 |

## Manual browser checks (2026-11-17)

| ID | What was done | Observed |
|---|---|---|
| MAN-01 | Requester at 375 px: Map tab, Near me (permission not available in the test browser), filter O-, tap map | Removed: map screens taken out on the instructor's request (DEC-023) |
| MAN-02 | Donor at 375 px: Alerts tab with 2 unread, Mark all as read | Badge "2" then hidden; "All caught up."; network: GET notifications 200, POST notifications/read 200, GET 200 |
| MAN-03 | Donor at 375 px: Edit profile, tap the location map | Removed: map screens taken out on the instructor's request (DEC-023) |
| MAN-04 | Admin at 1280 px: Dashboard and all admin pages | 8 number cards, 3 charts drawn; Donors, Verify, Users, Requests, request detail, donor detail, Hospitals, add/edit hospital, Reports: 200; both CSV exports 200 `text/csv` |
| MAN-05 | `App\Support\Alerts::send()` for a sample donor with `MAIL_MAILER=smtp`, received by a local SMTP test mailbox on port 2525 | One message saved with the From, To and Subject above, the "Open LifeLine" link and the medical disclaimer |
| MAN-06 | Demo donor at 375 px after the M3.2 fixes: Home colours, a throwaway `data-submit-once` form submitted twice, Track box with a pasted reference `  ll-261117-abc234  ` | Hero gradient starts at #c62828 with white stat text; switch on = white track, green knob; off = #6c757d track; second submit blocked and button disabled; reference trimmed and accepted by the pattern |
| MAN-08 | Requester and demo donor at 375 px (system completion): Home, new O- request, donor accepts, requester opens View donor and presses Record donation; /no-such-page; /offline.html; unread-count API | Home "Donors near you": 16 donors within 10 km, map tiles and circles drawn; service worker active (/sw.js); donor accepted, "I can no longer donate" shown; D3 page showed name, O-, 0 donations, 2 km, Call/SMS; after Record donation: "Donation recorded", count 1, last donation Nov 17, 2026; 404 page with LifeLine styling; offline page 200; unread count JSON |
| MAN-07 | Keyboard-only walkthrough (Tab, Shift+Tab, Enter, Space) of register, login, Home, emergency request, Requests for you, Alerts and Map | **Not run yet: a team member does this and writes what they observed** |
