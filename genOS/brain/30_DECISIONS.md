# Decisions

Authoritative decision log. AI records entries; humans approve intent-affecting decisions.
See `AGENTS.md` → Ownership.

Record a decision when intent, architecture, or constraints change in a way future agents cannot infer from runtime environment or source code.

**ID format:** `DEC-001`, `DEC-002`, …  
**Status:** `proposed` | `accepted` | `superseded` | `rejected`

Do not duplicate implementation details. Link to systems or runtime paths when useful.
Keep the Index in sync with entries below. Do not leave blank placeholder rows.

---

## Index

| ID | Status | Date | Title |
|----|--------|------|-------|
| DEC-016 | accepted | 2026-11-14 | M2.3 matching rules: when matching runs, eligibility, distance, answers, contact reveal, limits |
| DEC-015 | accepted | 2026-11-14 | Signed-in /api/v1 routes use the web session (no Sanctum) |
| DEC-014 | accepted | 2026-11-14 | M2.2 blood request rules: who requests, location, statuses, tracking fields, limits |
| DEC-013 | accepted | 2026-11-14 | Bootstrap and Bootstrap Icons served from the app, not a CDN |
| DEC-012 | accepted | 2026-11-14 | App and database run in Philippine time |
| DEC-011 | accepted | 2026-11-14 | In-app alerts use LifeLine's own notifications table; Laravel notifications for mail only |
| DEC-010 | accepted | 2026-11-13 | Sample hospitals fake until team supplies real ones; max 10 units |
| DEC-009 | accepted | 2026-11-13 | No government ID upload |
| DEC-008 | accepted | 2026-11-13 | Mini Thesis uses only verified sources |
| DEC-007 | accepted | 2026-11-13 | Matching suggests potential donors only (medical disclaimer) |
| DEC-006 | accepted | 2026-11-13 | Reference numbers: random format + UNIQUE index + retry |
| DEC-005 | accepted | 2026-11-13 | Notifications: in-app + email core; Web Push / SMS optional |
| DEC-004 | accepted | 2026-11-13 | Donor privacy: approximate map locations, contact after acceptance |
| DEC-003 | accepted | 2026-11-13 | Single users table with roles; role profile tables 1:1 |
| DEC-002 | accepted | 2026-11-13 | genOS hybrid layout: app + research system categories |
| DEC-001 | accepted | 2026-11-13 | Stack: Laravel 12 + Blade/Bootstrap mobile-first PWA + MariaDB |

---

## Template

```markdown
### DEC-XXX: Title
- **Status:** proposed
- **Date:** YYYY-MM-DD
- **Reason:**
- **Impact:**
- **Supersedes:** none
```

---

## Decisions

### DEC-016: M2.3 matching rules: when matching runs, eligibility, distance, answers, contact reveal, limits
- **Status:** accepted
- **Date:** 2026-11-14
- **Reason:** Team approved the M2.3 kickoff defaults.
- **Impact:** Matching runs when a request is submitted and again on the requester's "Find more donors" (while `open` or `donor_found`); each run adds up to 10 of the nearest eligible donors not matched yet (`config/lifeline.php` `match_limit`), no radius. Eligible = `Donor::searchable()` + red-cell ABO/Rh compatible + approximate coordinates set + no donation within 3 months (`donation_interval_months`, from design B2; source still to be cited, DEC-008) + not the requester. No city fallback: until M2.4 captures coordinates only sample donors can match. Distance = Haversine from the hospital (DEC-014), shown in whole km (minimum 1). Donors answer each match once (accept / decline) on a "Requests for you" page; answers are refused once the request is closed. The first acceptance sets `donor_found` through `changeStatus()`; declines never change the request. Contact both ways only after acceptance (DEC-004). No notifications in M2.3 (M2.5). Limits: matches API 60/min per user, Find more donors 5 per 10 min per user, each with its own prefix. Plan: `docs/superpowers/plans/2026-11-14-m2.3-matching.md`. See `[[Matching]]`.
- **Supersedes:** none

### DEC-015: Signed-in /api/v1 routes use the web session (no Sanctum)
- **Status:** accepted
- **Date:** 2026-11-14
- **Reason:** The `api` route group has no session, so `/api/v1` could only serve public data. Sanctum (stateful) would add a package, config, a migration and a `SANCTUM_STATEFUL_DOMAINS` setting that breaks the API if the demo host is not listed, against DEC-001's simplicity rule. Team chose option A at the M2.3 kickoff.
- **Impact:** Signed-in API routes in `routes/api.php` use `['web', 'auth', 'verified', 'role:...']`: the normal login session cookie, and the CSRF token on POST/PATCH/DELETE (plain-JS files send it from a `csrf-token` meta tag when the first API write is added). Public routes such as tracking stay stateless. Implements DEC-001's "normal login session (no Sanctum tokens)"; Sanctum tokens only if a native client is ever added. See `[[RestApi]]`.
- **Supersedes:** none

### DEC-014: M2.2 blood request rules: who requests, location, statuses, tracking fields, limits
- **Status:** accepted
- **Date:** 2026-11-14
- **Reason:** Team approved the M2.2 kickoff defaults.
- **Impact:** Donors and requesters with a verified email may submit requests (not admins; donors need no admin verification to ask). The request location is the selected hospital's coordinates (no new column). Requesters may cancel or mark fulfilled; `donor_found` is set by M2.3; no automatic expiry, `expired` comes from the M2.6 admin override. Public tracking (`/api/v1`, used by the `/track` page) shows reference, status, urgency, blood type, hospital, times and status history only. Limits: 5 submissions per 10 minutes per user, 30 lookups per minute per IP, each with its own throttle prefix. Plan: `docs/superpowers/plans/2026-11-14-m2.2-requests.md`. See `[[BloodRequests]]`, `[[RestApi]]`.
- **Supersedes:** none

### DEC-013: Bootstrap and Bootstrap Icons served from the app, not a CDN
- **Status:** accepted
- **Date:** 2026-11-14
- **Reason:** The demo environment is still undecided, and the defense venue may have no reliable internet. DEC-001 rules out an npm build step. Team approved the default at the M1.3 kickoff.
- **Impact:** Bootstrap 5.3.3 and Bootstrap Icons 1.11.3 dist files are committed under `lifeline/public/vendor/`, and the brand lives in plain `lifeline/public/css/lifeline.css`. Views do not use `@vite`. Upgrading means replacing those files. See `[[FrontendUI]]`.
- **Supersedes:** none

### DEC-012: App and database run in Philippine time
- **Status:** accepted
- **Date:** 2026-11-14
- **Reason:** M1.2 final review found Laravel in UTC while MariaDB used the host zone (UTC+8): reference numbers got yesterday's date between 00:00 and 07:59, and Eloquent and MariaDB timestamps disagreed by 8 hours. LifeLine serves Butuan City only.
- **Impact:** `config/app.php` timezone `Asia/Manila`; the `mariadb` connection sets session time zone `+08:00`, independent of the host OS. Tested in `DatabaseSetupTest`. See `[[Database]]`.
- **Supersedes:** none

### DEC-011: In-app alerts use LifeLine's own notifications table; Laravel notifications for mail only
- **Status:** accepted
- **Date:** 2026-11-14
- **Reason:** The approved M1.2 plan created a simple custom `notifications` table (user_id, blood_request_id, title, message, read_at) instead of Laravel's polymorphic UUID table, which is easier for the team to explain. Laravel's `database` channel cannot write to it.
- **Impact:** Clarifies DEC-005: in-app alerts are created with `App\Models\Notification::create()`; email uses Laravel notifications (mail channel) through the `RoutesNotifications` trait on `User`. Do not add `Notifiable` or the `database` channel. See `[[Notifications]]`.
- **Supersedes:** none

### DEC-010: Sample hospitals fake until team supplies real ones; max 10 units
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** Team approved the M1.2 plan defaults. Real hospital names and coordinates in Butuan City must not be guessed; the "CARAGA Regional Hospital" in the design may not be in Butuan. A request needs a sane upper bound for units.
- **Impact:** Seeder uses clearly fake "Sample …" hospitals until the team provides verified names. `blood_requests.units` CHECK is 1–10. See `[[BloodRequests]]`, `[[Database]]`.
- **Supersedes:** none

### DEC-009: No government ID upload
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** A government ID photo is sensitive personal information under the Data Privacy Act (RA 10173), and storing it adds code and risk. Team approved the default to drop it.
- **Impact:** Registration step B3 keeps password, consent, and terms but no ID upload; no ID column or file storage in the schema. Donors are verified manually by admins. See `[[Authentication]]`.
- **Supersedes:** none

### DEC-008: Mini Thesis uses only verified sources
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** The team requires credible, verifiable academic sources; fabricated citations would fail the defense. Domain numbers in the app (e.g. donation interval, donor age/weight limits) must also trace to an authoritative source such as the Philippine Red Cross or DOH.
- **Impact:** Every citation is located and checked before use; unverified claims are marked for the team to confirm. Eligibility thresholds in code are configuration values linked to a cited source. See `[[LiteratureReview]]`.
- **Supersedes:** none

### DEC-007: Matching suggests potential donors only (medical disclaimer)
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** Required by the project brief: final compatibility and donor eligibility must be confirmed by healthcare professionals.
- **Impact:** UI and documentation label matches as "potential donors"; the disclaimer appears on match results, request confirmation, and donor profiles. Matching uses red-cell ABO/Rh compatibility and self-reported data only. See `[[Matching]]`.
- **Supersedes:** none

### DEC-006: Reference numbers: random format + UNIQUE index + retry
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** Brief requires a unique reference number per request, validated before save. Sequential IDs are guessable, which matters because tracking by reference number is semi-public.
- **Impact:** Format `LL-YYMMDD-XXXXXX` (date + 6 random uppercase alphanumerics excluding ambiguous characters). Backend checks for existence, then inserts; the database `UNIQUE` index is the final guard, and a duplicate-key error triggers regeneration (bounded retries). Public tracking by reference number shows status only, not patient details. See `[[BloodRequests]]`.
- **Supersedes:** none

### DEC-005: Notifications: in-app + email core; Web Push / SMS optional
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** Philippine SMS gateways are paid; email (SMTP) and in-app notifications are free. The design's "Push Notifications Sent" screen implies push; standards-based Web Push (VAPID) is free for an installed PWA on Android but adds setup risk.
- **Impact:** Core: database (in-app Alerts tab) + email via Laravel notifications. Stretch: Web Push channel. SMS documented as a pluggable channel but disabled. See `[[Notifications]]`.
- **Supersedes:** none

### DEC-004: Donor privacy: approximate map locations, contact after acceptance
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** Brief forbids public exposure of exact home locations. The design's map, "Contact" button, and donor profile Call/Message buttons would otherwise expose donors' position and phone numbers.
- **Impact:** Map markers use approximate coordinates (rounded/offset to roughly barangay level) and are visible only to logged-in users; distances are shown rounded. Phone/Call/Message are revealed to a requester only after the donor accepts that request (the design's "Donor Match Found! … Contact info shared" notification). Location sharing is opt-in (registration consent + profile toggle). Message = device SMS link, not in-app chat. See `[[DonorMap]]`.
- **Supersedes:** none

### DEC-003: Single users table with roles; role profile tables 1:1
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** The brief lists both `users` and `administrators`; storing credentials in two tables breaks normalization and doubles the auth logic.
- **Impact:** `users` holds credentials and a role (donor / requester / admin). `donors` and `administrators` are 1:1 profile tables keyed by `user_id`. Hospitals are reference data managed by admins. See `[[Database]]` and `[[Authentication]]`.
- **Supersedes:** none

### DEC-002: genOS hybrid layout: app + research system categories
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** The project is both a working system and a Mini Thesis. genOS keeps one brain and splits systems into 1-level category folders.
- **Impact:** Blend of the `web-fullstack` and `academic-research` profiles. Systems live in `brain/systems/app/` and `brain/systems/research/` with globally unique names.
- **Supersedes:** none

### DEC-001: Stack: Laravel 12 + Blade/Bootstrap mobile-first PWA + MariaDB
- **Status:** accepted
- **Date:** 2026-11-13
- **Reason:** Matches the instructor's PHP + MySQL + Bootstrap guidance and the team's installed toolchain (XAMPP PHP 8.2 + MariaDB 10.4, Composer, Node). Laravel 12 runs on PHP 8.2 (Laravel 13 needs PHP 8.3+). One codebase that is mobile-first and installable as a PWA reproduces the phone design and still runs in a laptop browser for the hands-on evaluation; a separate Flutter or React client would double the work within an academic deadline.
- **Impact:** Laravel 12 + MariaDB, kept deliberately simple per the team's "keep the code simple" constraint in `[[00_PROJECT]]`: plain controllers + Blade views (no Livewire/React/Vue), Bootstrap 5 and small plain-JavaScript files (no npm build step required), Laravel built-ins for auth/validation/mail/notifications, few or no extra Composer packages, no queue worker. Versioned JSON REST API under `/api/v1` using the normal login session (no Sanctum tokens) and consumed by the app's own map, matching, tracking, and notification screens, so the API is real rather than decorative. Leaflet + OpenStreetMap tiles; browser Geolocation API; Chart.js for admin reports; PHPUnit feature tests. Alternative if the instructor requires a native mobile app: Flutter client on the same `/api/v1`, adding Sanctum tokens then.
- **Supersedes:** none
