# LifeLine bug log (M3.2)

Defects found while building and testing M2.4–M3, and how they were fixed. Earlier milestones' fixes are in the git
history (`git log --grep=fix`).

| ID | Found | How found | Defect | Fix | Verified by |
|---|---|---|---|---|---|
| BUG-01 | 2026-11-17 | Automated test | Reports page crashed (500): `created_at` was ambiguous once `blood_requests` was joined with `hospitals` for the "by hospital" summary | Range filter names the table: `blood_requests.created_at` | `HospitalsAndReportsTest::test_the_report_summarises_requests_in_the_date_range` |
| BUG-02 | 2026-11-17 | Browser check (MAN-01) | Donor map listed hospitals alphabetically, so a far hospital could appear before a near one | Hospitals sorted by distance, then name | `DonorMapTest::test_hospitals_are_listed_with_distance` |
| BUG-03 | 2026-11-17 | Code review while adding the location picker | An Edit profile form sent without the location fields (old page, other client) would have erased a donor's saved location | Location is changed only when the form sends the location fields | `DonorLocationTest::test_a_form_without_location_fields_keeps_the_saved_location` |
| BUG-04 | 2026-11-17 | Code review of CSV export | A cell starting with `=`, `+`, `-` or `@` (e.g. a city typed as a formula) would run as a formula in Excel (CSV injection) | Such cells are prefixed with `'` | `HospitalsAndReportsTest::test_the_donors_csv_has_no_contact_details_and_neutralises_formulas` |

## Responsive and accessibility pass

Checked on 2026-11-17 (see MAN-01 – MAN-04 in [test-cases.md](test-cases.md)):

- No sideways scrolling at 375 px on Map, Alerts and Edit profile.
- New screens use real labels for every input, `role="status"` with `aria-live` for map/alerts/location messages,
  `aria-current` on the active tab, an "unread" text for screen readers on unread alerts, and `aria-label`s on the maps
  and charts. Map and alert text from the API is inserted with `textContent` only (no HTML injection).
- Admin tables sit inside `.table-responsive`; the admin menu scrolls sideways on small screens.

Still open (from the Donors system file): hero text and switch contrast on Home/Profile need a contrast check with a
tool such as the WebAIM contrast checker; real keyboard-only walkthrough by a team member.
