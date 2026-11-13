# Database

## Purpose
Normalized relational schema in MariaDB/MySQL with integrity constraints and sample data.

## Status
`active`

## Responsibilities
- Tables: users, donors, administrators, hospitals, blood_requests, donor_matches, notifications, request_history (+ supporting tables)
- PK/FK, UNIQUE, CHECK constraints; migrations; seeders
- Documented SQL CRUD queries for the thesis

## Dependencies
- none (foundation)

## Runtime References
- Migrations (source of truth): `lifeline/database/migrations/`
- ERD and data dictionary: `docs/database/erd.md` (kept in sync by `ErdMatchesSchemaTest`)
- Exported schema: `docs/database/lifeline_schema.sql`; SQL examples: `docs/database/crud_queries.sql`
- Constraint tests: `lifeline/tests/Feature/Database/`

## Invariants
- Schema is at least 3NF; no credentials duplicated across tables (see [[30_DECISIONS]] DEC-003)
- Referential integrity enforced by foreign keys, not only by application code

## Notes
- Local target: XAMPP MariaDB 10.4 (supports enforced CHECK constraints).
- "Last donation" is MAX(donations.donated_on); it is not stored on donors (3NF).
- App and DB both run in Philippine time (see [[30_DECISIONS]] DEC-012).
- Never run `php artisan config:cache` in development: cached config ignores phpunit.xml, so RefreshDatabase would wipe the `lifeline` dev database instead of `lifeline_test`.
- Seeded admin `admin@lifeline.test` / `password` is local sample data only; guard or change it before any hosted deployment.

## Future Changes
- _TBD_
