# LiteratureReview

## Purpose
Chapter 2: verified local and foreign literature, studies, existing systems, synthesis, and research gap.

## Status
`active`: M4.2 started 2026-11-17. Source matrix has 21 sources (17 Verified, 4 Partly verified: S10, S16, S17, S18) checked on 2026-11-17, plus a list of found-but-unusable candidates. Chapter 2 has all sections drafted from those sources; [TODO] marks list the sources still needed (more local studies, SUS literature, privacy/location literature, full texts for the existing-systems table).

## Responsibilities
- Source matrix (author, year, title, venue, link/DOI, verified flag)
- Existing systems comparison
- Synthesis and research gap

## Dependencies
- [[ThesisCore]]

## Runtime References
- `docs/thesis/source-matrix.md`
- `docs/thesis/chapter-2.md`

## Invariants
- No source is cited unless its existence and content have been verified (see [[30_DECISIONS]] DEC-008)
- Partly verified sources keep a [VERIFY] mark in the chapters until a team member opens them
- The research gap is scoped to the reviewed sources; it never claims no such system exists anywhere

## Notes
- The donor eligibility source for [[Donors]] / [[Matching]] config is found: Philippine Red Cross "Give blood" page (S05) states a healthy individual may donate every three months. Age and weight limits differ across sources and are not used by the app.
- Several Philippine news and government pages (Inquirer, PIA, redcrossblood.org) refuse automated access (HTTP 403); a person must open them.

## Future Changes
- Add the TODO sources; confirm S10 (Bloodline) is a Philippine study; verify S16–S18 contents.
