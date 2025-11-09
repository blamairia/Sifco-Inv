# Documentation Index - Phase 3.3

**Updated:** 2025-11-09
**Environment:** Laravel 11 / Filament v4 / MySQL 8.0.44 (SQLite for CI fallback)

---

## Snapshot
- Transfers now stage weight + metre metrics end to end; SQLite migrations use correlated updates to mirror MySQL joins.
- Roll Adjustment form rebuilt without BOM issues; schema shows weight/length helpers and drives new deltas.
- Next focus: finish metre capture for Bon d'Entree/Bon Reintegration, extend lifecycle ledger, then tackle roll grouping (Slice 5a).

---

## Core Files (Holy Bible)
| File | Purpose |
| --- | --- |
| `Plan.md` | Roadmap, slice status, outstanding tasks |
| `AGENT_OPERATIONS.md` | Operating rules + current focus for implementers |
| `ISSUES_LEDGER.md` | Single source of truth for issue statuses and blockers |
| `PROCEDURE_MAPPING.md` | SIFCO procedures mapped to code, including metre tracking notes |
| `INDEX.md` | You are here; quick context + navigation |

---

## Quick Links
- Need the current backlog? -> `Plan.md` (Slice 5a/5b details at bottom)
- Picking up a task? -> check `ISSUES_LEDGER.md` status and `AGENT_OPERATIONS.md` rules first
- Wiring a workflow? -> `PROCEDURE_MAPPING.md` for step-by-step plus DB touchpoints

---

## Test Expectations
- Run `php artisan test --filter=BonTransfertServiceTest` after touching transfer logic or metre migrations.
- Full `php artisan test` required before commits that span multiple workflows.

---

## Doc Hygiene
- Any feature change -> update all four core docs before requesting review.
- Extra markdown files are intentionally deleted; recreate only if referenced from `INDEX.md`.
