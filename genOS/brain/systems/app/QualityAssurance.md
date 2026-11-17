# QualityAssurance

## Purpose
Testing and debugging evidence for the hands-on evaluation.

## Status
`active` — M3.1 in progress (2026-11-17): 414 automated tests pass; test case table for the nine categories filled from real runs; API collection, SUS questionnaire, bug log, demo script and `DemoScriptTest` (demo on a fresh seeded database) written. Waiting on the team: SUS usability sessions with real participants (USE-01) and a real-inbox email check (EMR-12).

## Responsibilities
- Test plan covering functional, database, API, input validation, login/registration, duplicate reference number, emergency request, donor matching, and usability testing
- Automated unit and feature tests; API test collection
- Bug log and fixes

## Dependencies
- all app systems

## Runtime References
- Documents: `docs/testing/` (`test-cases.md`, `automated-test-results.txt`, `api-tests.http`, `sus-questionnaire.md`, `bug-log.md`, `demo-script.md`)
- Tests: `lifeline/tests/` (run `cd lifeline && php artisan test`; MariaDB must be running); end-to-end demo: `lifeline/tests/Feature/DemoScriptTest.php`
- Demo data: `lifeline/database/seeders/DatabaseSeeder.php` (fixed demo accounts, DEC-020)

## Invariants
- Every test case records expected and actual results
- Actual results come from real runs, never assumed

## Notes
- Usability testing likely uses SUS; instrument choice confirmed in [[Methodology]].

## Future Changes
- Re-run `php vendor/bin/phpunit --testdox` and refresh `automated-test-results.txt` after any code change before the defense.
- Contrast check of the hero text and switches (Donors system file) and a keyboard-only walkthrough by a team member.
