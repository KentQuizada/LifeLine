# Roadmap

Authoritative long-term development roadmap and **sole source of truth for milestone definitions**.
AI-owned. See `AGENTS.md` → Ownership.

**This file owns:** milestone IDs, objectives, deliverables, completion criteria, dependencies, and lifecycle status.  
**This file does not own:** live session execution state (that is `20_PROGRESS.yaml` only).

Do not copy milestone criteria into Progress or Backlog. Progress stores only `current_milestone` (ID) plus execution RAM.

**Status values:** `planned` | `active` | `done` | `cancelled`

**ID conventions:**
- Program milestones: `M1`, `M2`, … (or named phases: `EVAL`, `RC`, etc.)
- Child milestones: `M1.1`, `M1.2`, … under a parent

> Approved by the team 2026-11-13, derived from their 11-step work plan. Dates depend on the course deadline (`_TBD_` in `[[00_PROJECT]]`).

---

## Index

| ID | Parent | Status | Title |
|----|--------|--------|-------|
| M1 | — | done | Foundation |
| M1.1 | M1 | done | Requirements analysis & stack sign-off |
| M1.2 | M1 | done | Architecture, ERD & SQL database |
| M1.3 | M1 | done | Authentication, roles & app shell |
| M2 | — | active | Core features |
| M2.1 | M2 | done | Donor management & availability |
| M2.2 | M2 | done | Blood requests, reference numbers & tracking |
| M2.3 | M2 | active | Donor matching |
| M2.4 | M2 | planned | Donor map & geolocation |
| M2.5 | M2 | planned | Emergency notifications |
| M2.6 | M2 | planned | Admin dashboard & reports |
| M3 | — | planned | Testing, debugging & demo readiness |
| M3.1 | M3 | planned | Test plan & automated tests |
| M3.2 | M3 | planned | Debugging, usability pass & demo data |
| M4 | — | planned | Mini Thesis & defense |
| M4.1 | M4 | planned | Chapter 1 — The Problem and Its Background |
| M4.2 | M4 | planned | Chapter 2 — Review of Related Literature and Studies |
| M4.3 | M4 | planned | Chapter 3 — Methodology |
| M4.4 | M4 | planned | Defense questions & answers |

---

## Suggested sequence

M1.1 → M1.2 → M1.3 → M2.1 → M2.2 → M2.3 → M2.4 → M2.5 → M2.6 → M3.1 → M3.2 → M4.4.
M4.1 and M4.2 can run in parallel with M2 (writing work, no code dependency). M4.3 follows M1.2 (diagrams and database design) and is finalized after M3.1 (testing procedures).

---

## M1 — Foundation

- **Status:** done
- **Objective:** Agree on requirements and stack, design the database, and stand up an authenticated application skeleton.
- **Success criteria:**
  - Stack and design decisions accepted by the team
  - Database schema migrated with sample data
  - Users can register, verify, log in, and log out by role
- **Children:** M1.1, M1.2, M1.3
- **Done when:** M1.1–M1.3 are `done`.

## M1.1 — Requirements analysis & stack sign-off

- **Status:** done
- **Objective:** Analyze the brief and UI design, surface missing requirements and risky features, and lock the stack.
- **Deliverables:** Gap analysis with realistic alternatives; stack recommendation; decisions DEC-001…DEC-008 accepted or revised; open questions answered (deadline, team, demo environment, existing code).
- **Completion criteria:** DEC-001 through DEC-006 and DEC-008 are `accepted` or replaced; `[[00_PROJECT]]` Target Environment & Stack and Constraints have no `_TBD_` blocking M1.2.
- **Dependencies:** none
- **Systems:** all (review only)

## M1.2 — Architecture, ERD & SQL database

- **Status:** done
- **Objective:** Design the system architecture and a normalized relational database that meets every instructor database requirement.
- **Deliverables:** Laravel 12 project in `lifeline/` (needed to host migrations); architecture diagram; ERD; data dictionary; migrations for users, donors, administrators, hospitals, blood_requests, donor_matches, notifications, request_history (plus supporting tables as needed); PK/FK, UNIQUE, CHECK constraints; seeders with sample records; documented SQL CRUD queries.
- **Completion criteria:** Migrations run cleanly on MariaDB; seeders load sample data; inserting a duplicate reference number fails at the database level; the ERD matches the migrated schema.
- **Dependencies:** M1.1
- **Systems:** Database

## M1.3 — Authentication, roles & app shell

- **Status:** done
- **Objective:** The design's app shell and secure role-based authentication on the Laravel project created in M1.2.
- **Deliverables:** Mobile-first layout with bottom navigation; splash/onboarding; 3-step registration (donor or requester; no ID upload per DEC-009); login by email or mobile; password hashing; email verification; password reset (added at kickoff 2026-11-14); admin verification of donors; role-gated routes; admin login. Plan: `docs/superpowers/plans/2026-11-14-m1.3-auth.md`.
- **Completion criteria:** Feature tests pass for register, login, logout, wrong password, duplicate email/mobile, unverified access blocked, role access control.
- **Dependencies:** M1.2
- **Systems:** Authentication, FrontendUI

---

## M2 — Core features

- **Status:** active
- **Objective:** Deliver every required feature end-to-end with database-backed CRUD, validation, and no placeholder functionality.
- **Success criteria:**
  - All eight brief features work in the browser on phone and laptop sizes
  - REST API endpoints back the map, matching, tracking, and notifications
- **Children:** M2.1–M2.6
- **Done when:** M2.1–M2.6 are `done`.

## M2.1 — Donor management & availability

- **Status:** done
- **Objective:** Donors maintain their profile, medical info, location, and availability.
- **Deliverables:** Home dashboard; availability toggle; my profile; edit profile and medical info; donation history; location sharing toggle; consent timestamps (carried over from M1.3). Plan: `docs/superpowers/plans/2026-11-14-m2.1-donors.md`.
- **Completion criteria:** Donor CRUD validated on frontend and backend; availability changes affect matching and map visibility.
- **Dependencies:** M1.3
- **Systems:** Donors

## M2.2 — Blood requests, reference numbers & tracking

- **Status:** done
- **Objective:** Requesters submit emergency requests with unique reference numbers and track their status.
- **Deliverables:** SOS request form (blood type, units, urgency, hospital, location); request sent screen showing the reference number; status lifecycle with history; track-by-reference page; request list and cancel. Rules: `[[30_DECISIONS]]` DEC-014. Plan: `docs/superpowers/plans/2026-11-14-m2.2-requests.md`.
- **Completion criteria:** Duplicate reference numbers are impossible (tested); every status change writes request_history; tracking works for valid, invalid, and malformed references.
- **Dependencies:** M1.3
- **Systems:** BloodRequests, RestApi

## M2.3 — Donor matching

- **Status:** active
- **Objective:** Find compatible, available, nearby potential donors for a request.
- **Deliverables:** Red-cell ABO/Rh compatibility rules; distance ranking; eligibility filter (availability, verification, configurable donation interval); donor accept/decline; contact reveal on acceptance; medical disclaimer. Rules: `[[30_DECISIONS]]` DEC-015 (API sign-in), DEC-016 (matching). Plan: `docs/superpowers/plans/2026-11-14-m2.3-matching.md`.
- **Completion criteria:** Unit tests cover all 8×8 blood type pairs; matches exclude unavailable, unverified, and recently donated donors.
- **Dependencies:** M2.1, M2.2
- **Systems:** Matching

## M2.4 — Donor map & geolocation

- **Status:** planned
- **Objective:** Show nearby available donors on a map without exposing exact locations.
- **Deliverables:** Leaflet + OpenStreetMap map; browser geolocation with manual fallback; blood type filter; donor list with rounded distance; approximate markers; hospital markers.
- **Completion criteria:** No API response contains a donor's exact coordinates; the map works when location permission is denied.
- **Dependencies:** M2.1
- **Systems:** DonorMap, RestApi

## M2.5 — Emergency notifications

- **Status:** planned
- **Objective:** Notify matched donors and requesters about requests and status changes.
- **Deliverables:** In-app Alerts tab with unread count and mark-all-read; email notifications; notification preferences; optional Web Push.
- **Completion criteria:** A new request notifies the matched donors; acceptance and status changes notify the requester; email delivery verified in a test mailbox.
- **Dependencies:** M2.3
- **Systems:** Notifications

## M2.6 — Admin dashboard & reports

- **Status:** planned
- **Objective:** Administrators manage accounts and requests and see system statistics.
- **Deliverables:** Admin dashboard (desktop layout); donor verification queue; user and donor management; request management with status override (including marking stale requests `expired`, DEC-014); hospital management; statistics and charts; exportable reports.
- **Completion criteria:** Every admin action is role-protected and audited in request_history where relevant; statistics match database counts.
- **Dependencies:** M2.2
- **Systems:** AdminDashboard

---

## M3 — Testing, debugging & demo readiness

- **Status:** planned
- **Objective:** Prove the system works and is ready for the hands-on evaluation.
- **Success criteria:**
  - Test case document with expected and actual results for all nine required test categories
  - No known defects in the demo flows
- **Children:** M3.1, M3.2
- **Done when:** M3.1 and M3.2 are `done`.

## M3.1 — Test plan & automated tests

- **Status:** planned
- **Objective:** Write and run test cases covering functional, database, API, input validation, login/registration, duplicate reference number, emergency request, donor matching, and usability testing.
- **Deliverables:** Test case table (ID, steps, expected, actual, pass/fail); automated feature and unit tests; API test collection; SUS-style usability questionnaire.
- **Completion criteria:** All automated tests pass; manual test table filled with actual results.
- **Dependencies:** M2
- **Systems:** QualityAssurance

## M3.2 — Debugging, usability pass & demo data

- **Status:** planned
- **Objective:** Fix defects found in testing and prepare a reliable demo.
- **Deliverables:** Bug log with fixes; responsive and accessibility pass; realistic demo seed data; demo script.
- **Completion criteria:** Demo script runs end-to-end on a fresh database without errors.
- **Dependencies:** M3.1
- **Systems:** QualityAssurance, FrontendUI

---

## M4 — Mini Thesis & defense

- **Status:** planned
- **Objective:** Mini Thesis Chapters 1–3 in Philippine university format, plus defense preparation.
- **Success criteria:**
  - All chapters complete with verified citations only
  - Diagrams and design sections match the built system
- **Children:** M4.1–M4.4
- **Done when:** M4.1–M4.4 are `done`.

## M4.1 — Chapter 1 — The Problem and Its Background

- **Status:** planned
- **Objective:** Introduction, background, statement of the problem, general and specific objectives, scope and limitations, significance, definition of terms.
- **Deliverables:** Chapter 1 manuscript.
- **Completion criteria:** Specific objectives map one-to-one to implemented features; every factual claim is cited or marked for verification.
- **Dependencies:** M1.1
- **Systems:** ThesisCore

## M4.2 — Chapter 2 — Review of Related Literature and Studies

- **Status:** planned
- **Objective:** Local and foreign literature and studies, existing systems, synthesis, research gap.
- **Deliverables:** Chapter 2 manuscript; source matrix with verified links/DOIs.
- **Completion criteria:** Every source is verified to exist and to say what is cited; the research gap follows from the synthesis.
- **Dependencies:** M1.1
- **Systems:** LiteratureReview

## M4.3 — Chapter 3 — Methodology

- **Status:** planned
- **Objective:** R&D methodology, SDLC, requirements, architecture, use case diagram, DFD, ERD, database design, API design, testing and evaluation procedures.
- **Deliverables:** Chapter 3 manuscript with diagrams.
- **Completion criteria:** ERD, API design, and testing procedures match the implemented system and M3.1 results.
- **Dependencies:** M1.2, M3.1
- **Systems:** Methodology

## M4.4 — Defense questions & answers

- **Status:** planned
- **Objective:** Prepare the team for panel questions on the system, database, APIs, technologies, testing, and research.
- **Deliverables:** Q&A document grouped by evaluation criterion.
- **Completion criteria:** Covers each instructor evaluation area in `[[00_PROJECT]]`.
- **Dependencies:** M3.2, M4.3
- **Systems:** ThesisCore
