# Database

## Purpose
Normalized relational schema in MariaDB/MySQL with integrity constraints and sample data.

## Status
`unstarted`

## Responsibilities
- Tables: users, donors, administrators, hospitals, blood_requests, donor_matches, notifications, request_history (+ supporting tables)
- PK/FK, UNIQUE, CHECK constraints; migrations; seeders
- Documented SQL CRUD queries for the thesis

## Dependencies
- none (foundation)

## Runtime References
- _TBD_ (no source yet — set when implemented)

## Invariants
- Schema is at least 3NF; no credentials duplicated across tables (see [[30_DECISIONS]] DEC-003)
- Referential integrity enforced by foreign keys, not only by application code

## Notes
- Local target: XAMPP MariaDB 10.4 (supports enforced CHECK constraints).

## Future Changes
- _TBD_
