# Project

Authoritative file for project intent. See `AGENTS.md` → Ownership.

**Ownership:** Humans own vision and intent. AI may reorganize, summarize, clarify, and maintain this document. AI must never change intent without explicit human approval.

Fill sections when decided. Use `_TBD_` until then. `_TBD_` means unknown — do not invent content.

If Vision is still `_TBD_`, Empty Brain Protocol in `AGENTS.md` applies.

---

## Vision

LifeLine is a blood donor finder and emergency coordination platform that connects patients, families, hospitals, blood banks, and verified volunteer blood donors. It reduces delays in finding potential blood donors through a centralized platform with donor registration, blood type filtering, location-based donor search, emergency blood requests, and notifications — making the search faster, more organized, accessible, and reliable.

LifeLine is the team's **ITE-18 Final Project**: a fully functional, database-driven system to be demonstrated at the final defense and hands-on evaluation, plus a Mini Thesis (Chapters 1–3).

## Research Goal

Produce a Mini Thesis (Chapters 1–3) consistent with a Philippine university research paper:
- **Ch. 1** The Problem and Its Background (introduction, background, statement of the problem, general/specific objectives, scope and limitations, significance, definition of terms)
- **Ch. 2** Review of Related Literature and Studies (local and foreign literature/studies, existing systems, synthesis, research gap)
- **Ch. 3** Methodology (R&D methodology, SDLC, requirements analysis, functional/non-functional requirements, architecture, use case diagram, DFD, ERD, database design, API design, testing and evaluation procedures)

Hard rule: only credible, verifiable sources. Never invent authors, papers, statistics, or citations.

## Scope

**In scope (required features):**
1. User registration and login — donor, requester, administrator accounts; secure authentication; password hashing; account verification.
2. Donor management — blood type, contact information, location, availability updates, donor records.
3. Blood request management — blood type, quantity, hospital, location, urgency; unique reference number per request; status tracking.
4. Donor matching — by blood type compatibility and location; shows eligible *potential* donors; states that final compatibility and eligibility are confirmed by healthcare professionals.
5. Donor map — nearby available donors via GPS/location services without publicly exposing exact home locations.
6. Emergency notifications — notify relevant donors of new requests and status updates (email / push / SMS considered).
7. Admin dashboard — manage donors and requests, verify accounts, monitor statuses, view statistics and reports.
8. Request tracking — search by unique reference number; no duplicate reference numbers.
9. Relational SQL database (MySQL/MariaDB) — PKs/FKs, relationships, types, UNIQUE constraints, validation, normalization, CRUD queries, sample records. Expected tables include users, donors, hospitals, blood_requests, donor_matches, notifications, request_history, administrators.
10. API design and integration — REST API between frontend and backend; mapping, geolocation, and email/SMS APIs; free alternatives preferred over paid.
11. Practical emerging technologies — geolocation, real-time notifications, rule-based donor matching, analytics/visualization, optional cloud database — each with purpose, benefits, implementation, limitations.
12. Testing and debugging — functional, database, API, input validation, login/registration, duplicate reference number, emergency request, donor matching, usability; test cases with expected and actual results.
13. Defense preparation — likely defense questions and answers.

**Out of scope:**
- AI or advanced features added only for decoration.
- Unnecessary paid APIs where free alternatives exist.
- Medical decision-making: LifeLine suggests potential donors only; it never certifies compatibility or eligibility.

## Target Environment & Stack

Mobile-first web application (installable PWA) that also runs in a laptop browser for the hands-on evaluation. Laravel 12 (PHP 8.2) with Blade, Bootstrap 5, and plain JavaScript; MariaDB/MySQL; REST JSON API under `/api/v1`; Leaflet + OpenStreetMap; browser Geolocation API; in-app + email notifications. Approved by the team 2026-11-13 — see `[[30_DECISIONS]]` DEC-001. Local dev machine: XAMPP (PHP 8.2, MariaDB 10.4), Composer, Node 22.

**UI design (provided by the team, 2026-11-13):** mobile-first phone screens A1–E1 — splash, 3 onboarding screens, 3-step registration (role, medical info, ID verification), login, home dashboard, live donor map, notifications, SOS request form, request sent, donor profile, my profile; bottom navigation Home / Map / SOS / Alerts / Profile; red LifeLine brand. Screen-level detail lives in `[[FrontendUI]]`.

## Core User Workflow / Domain Loop

1. Requester submits an emergency blood request (blood type, quantity, hospital, location, urgency) → system issues a unique reference number.
2. System matches compatible, available donors by blood type and proximity, and notifies them.
3. Donors respond; the requester tracks the request by reference number while the status advances.
4. Administrators verify accounts, oversee requests and statuses, and review statistics.

## Constraints

- Academic final project for ITE-18, built by a student team; must be achievable within the course deadline while maintaining professional quality.
- Deadlines (from the course, given by the team 2026-11-17): Mini Thesis softcopy **Nov 19**; **link of the system by Nov 20, 11:59 PM** (the link is inaccessible afterwards); presentation and hands-on with debugging around **Dec 7-8** (course note says Dec 15), 10 minutes, 3-5 problems, each member brings a laptop.
- Graded: functional and usable system (completeness, correctness), API design / integration of emerging technology, data management and database design (e.g. SQL check that a reference number is not already recorded), documentation with evidence and statistics.
- Team size / roles: _TBD_
- Demo environment (localhost vs hosted): _TBD_
- **Keep the code simple** (team request, 2026-11-13): code the student team can read, explain at the defense, and maintain. Prefer framework built-ins and plain code over extra packages, build tools, and abstractions.
- Working functionality over visual effects; no broken pages or placeholder functionality.
- Required qualities: functional navigation, working forms, frontend and backend validation, database connectivity, CRUD, responsive design, error handling, secure authentication, search and filtering, consistent UI.
- Donor privacy: exact home locations must never be publicly exposed.
- Work proceeds step by step; do not assume the existing prototype's implementation without seeing it.

## Success Criteria

The instructor's evaluation areas:
- Completeness of the system
- Correctness of system functions
- Usability and user experience
- Database management
- API design and integration
- Integration of emerging technologies
- System testing and debugging
- Quality of documentation

The team can demonstrate a fully functional LifeLine during the final defense and hands-on evaluation.
