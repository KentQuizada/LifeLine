# AI Workforce

Authoritative source of truth for the project's AI workforce.
AI-maintained. See `integrations/agency_agents.md` for the selection protocol.

This file tracks which specialist agents (e.g., Agency Agents) are active, deferred, or removed for this project.

**Project overrides (apply to every agent):** the team's "keep the code simple" constraint in `[[00_PROJECT]]` and the stack in `[[30_DECISIONS]]` DEC-001 take precedence over any agent's default preferences. Agents' advice about microservices, massive scale, React/Vue/Angular, PostgreSQL-specific features, or cloud infrastructure is adapted to a single Laravel 12 app with Blade, Bootstrap, plain JavaScript, and MariaDB/MySQL.

## Core Agents
- **Backend Architect** (`engineering/engineering-backend-architect.md`) — Laravel controllers, `/api/v1` REST design, authentication, validation, error handling. Files: `.agents/skills/engineering-backend-architect/SKILL.md`, `.cursor/rules/engineering-backend-architect.mdc`.
- **Frontend Developer** (`engineering/engineering-frontend-developer.md`) — mobile-first Blade + Bootstrap screens from the team's design, plain-JS map and forms, accessibility. Files: `.agents/skills/engineering-frontend-developer/SKILL.md`, `.cursor/rules/engineering-frontend-developer.mdc`.
- **Code Reviewer** (`engineering/engineering-code-reviewer.md`) — correctness, security, and simplicity review of every milestone. Files: `.agents/skills/engineering-code-reviewer/SKILL.md`, `.cursor/rules/engineering-code-reviewer.mdc`.

## Milestone Agents
- **Database Optimizer** (`engineering/engineering-database-optimizer.md`) — M1.2: normalization, keys, UNIQUE/CHECK constraints, indexes, sample data. Files: `.agents/skills/engineering-database-optimizer/SKILL.md`, `.cursor/rules/engineering-database-optimizer.mdc`.
- **AppSec Engineer** (`security/security-appsec-engineer.md`) — M1.3: authentication, authorization, mass assignment, enumeration and throttling review; later donor-privacy review (M2.4). Files: `.agents/skills/security-appsec-engineer/SKILL.md`, `.cursor/rules/security-appsec-engineer.mdc`.

## Optional Specialists
- None installed.

## Deferred Agents
- **API Tester** (`testing/testing-api-tester.md`) — M3.1 API test collection.
- **UX Researcher** (`design/design-ux-researcher.md`) — M3.1 usability testing (SUS questionnaire).
- **Research Synthesist** (`research/research-synthesist.md`) — M4.2 literature review and research gap.
- **Technical Writer** (`engineering/engineering-technical-writer.md`) — M4 manuscript structure and clarity.

## Removed Agents
- None.

## Workforce Rationale
Small workforce on purpose, matching the team's simplicity constraint: three core agents cover backend, frontend, and review for the whole project; the Database Optimizer covers the active milestone M1.2. The UI Designer was not added because the team already has a design. Ponytail (restraint) and the Superpowers skills ship with genOS. Deferred agents are installed at their milestone via `kickoff workforce`.

## Last Workforce Review
2026-11-13 — Day 0 setup, active milestone M1.2. Installed by `guides/scripts/install_agent.py` with team approval.
2026-11-14 — M1.3 kickoff: AppSec Engineer installed with team approval.
