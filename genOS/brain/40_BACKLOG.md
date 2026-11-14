# Backlog

Durable ad-hoc queue for non-milestone work (bugs, ideas). AI-owned. See `AGENTS.md` → Ownership.
**Do not duplicate milestone criteria here.** Planned milestones live strictly in `50_ROADMAP.md`.

**Columns:** Todo | Done | Icebox | Rejected  
**In-progress work lives only in** `brain/20_PROGRESS.yaml` → `working_on` — not here.

**Item format:** `- [TASK-001] Description` optional `(ref: DEC-003)`

---

## Todo

<!-- Queued work not yet started -->
- [TASK-001] Existing `throttle:6,1` (password reset, email verification) and `throttle:10,1` (profile update) routes share one counter per user or guest IP, because plain `throttle:x,y` has no route-specific key. Give each its own prefix (third argument) and add a test.

## Done

<!-- Completed items -->

## Icebox

<!-- Deferred, not rejected -->

## Rejected

<!-- Explicitly rejected ideas -->
