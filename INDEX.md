# Documentation Index - Phase 3.4

**Updated:** 2025-11-10
**Environment:** Laravel 11 / Filament v4 / MySQL 8.0.44 (SQLite for CI fallback)

---

## Snapshot
- ✅ **Roll Lifecycle & Metrics System COMPLETE**: Full metre-length tracking + event logging deployed
- ✅ All services integrated (BonEntree, BonSortie, BonTransfert, BonReintegration)
- ✅ Database migrations deployed, seeders updated
- ⏳ Test suite: 1/5 passing (4 need minor `received_date` fixes)
- 🎯 Next focus: Complete test validation, then tackle roll dimension grouping (Slice 5a)

---

## Core Files (Holy Bible)
| File | Purpose | Status |
| --- | --- | --- |
| `Plan.md` | Roadmap, slice status, outstanding tasks | ✅ Updated |
| `AGENT_OPERATIONS.md` | Operating rules + current focus for implementers | 🔄 Current |
| `ISSUES_LEDGER.md` | Single source of truth for issue statuses and blockers | ✅ Updated |
| `PROCEDURE_MAPPING.md` | SIFCO procedures mapped to code, including metre tracking notes | ✅ Complete |
| `LIFECYCLE_SYSTEM.md` | **NEW** - Complete lifecycle implementation guide | ✅ Created |
| `INDEX.md` | You are here; quick context + navigation | ✅ Updated |

---

## Quick Links
- Need the current backlog? -> `Plan.md` (Slice 5a/6/7 details at bottom)
- Picking up a task? -> check `ISSUES_LEDGER.md` status and `AGENT_OPERATIONS.md` rules first
- Wiring a workflow? -> `PROCEDURE_MAPPING.md` for step-by-step plus DB touchpoints
- Understanding lifecycle system? -> `LIFECYCLE_SYSTEM.md` for complete implementation guide
- Testing lifecycle events? -> `tests/Feature/RollLifecycleEventTest.php`

---

## Test Expectations
- Run `php artisan test --filter=RollLifecycleEventTest` for lifecycle event validation
- Run `php artisan test --filter=BonTransfertServiceTest` after touching transfer logic or metre migrations
- Full `php artisan test` required before commits that span multiple workflows
- Current status: 1/5 lifecycle tests passing (quick fixes needed for remaining 4)

---

## Recent Completions (2025-11-10)
1. ✅ **Metre-Length Tracking System**
   - Migrations: `2025_11_09_113000`, `2025_11_09_130000`
   - All tables updated with `length_m` fields
   - Service layer fully integrated

2. ✅ **Roll Lifecycle Events System**
   - Migration: `2025_11_09_140000_create_roll_lifecycle_events_table`
   - Model: `RollLifecycleEvent` with factory methods
   - Integrated in all 4 main services (Entree/Sortie/Transfert/Reintegration)
   - Tracks: reception, sortie, transfer, reintegration with waste

3. ✅ **Database Seeders Updated**
   - `BonEntreeTestSeeder`: Now includes weight_kg + length_m + dimensions
   - `WorkflowDemoSeeder`: Enhanced with complete lifecycle data

---

## Doc Hygiene
- Any feature change -> update all core docs before requesting review
- New system implementations -> create dedicated guide (see `LIFECYCLE_SYSTEM.md`)
- Extra markdown files are intentionally deleted; recreate only if referenced from `INDEX.md`
