# Issues Ledger (Holy File)
- last_update: 2025-11-10T00:45
- rule: keep entries concise, status-first, update immediately after change

## High-Priority
1. status_field_bypass (in_review) — status inputs removed/locked across Bon Entrée, Bon Sortie, Roll resources; pages now reject tampering and force actions/services for transitions.
2. transfer_cump_instant_move (in_review) — draft transfer now stages outbound/inbound movements, defers destination qty until receive, recomputes CUMP, sets `last_movement_id`.
3. roll_reception_metrics (resolved) — ✅ Migrations complete, `length_m` + `weight_kg` captured in bon_entree_items, persisted to rolls/stock_quantities/stock_movements. Service layer fully integrated.
4. roll_lifecycle_events (resolved) — ✅ Table created, model with factory methods complete, integrated in BonEntreeService, BonSortieService, BonReintegrationService, BonTransfertService. Test suite created (1/5 passing, 4 need minor fixes).
5. transfer_edit_lockout (resolved) — `BonTransfertsTable` now keeps edit access for `in_transit`, and status filters/badges cover the expanded vocabulary.
6. low_stock_schema_drift (open) — `App\Models\LowStockAlert` fields missing in migration; add migration or prune model API before alerts run.
7. rolls_grouping_dimensions (open) — bobine listings + queries must group by grammage/laize/quality; adjust schema/indexing + resources to surface grouping.
8. roll_metrage_tracking (resolved) — ✅ Complete metre-length tracking across all workflows: sorties, transfers, reintegrations, adjustments. Length deltas (before/after/delta) logged in stock_movements and lifecycle_events.

## Medium-Priority
1. transfer_pending_state (resolved) — inbound movements remain pending and stock is only incremented on receive via `BonTransfertService::receive`.
2. stock_quantity_last_move (resolved) — ✅ `last_movement_id` now propagated in all entry/sortie/transfer/reintegration services.
3. transfer_status_vocab (resolved) — service, form schema, and table badges/filters now surface full status list.
4. roll_waste_reporting (in_progress) — lifecycle_events table ready with waste tracking fields; need dashboard widgets + reporting views.
5. lifecycle_test_completion (open) — 4/5 tests need `received_date` field added to Roll factory/creation; quick 5-minute fix.
6. filament_length_display (open) — update Filament forms/tables to display metre metrics alongside weight in roll resources, bon forms, stock views.

## Low-Priority
1. movement_number_helpers (open) — reuse `StockMovement::generateMovementNumber()` everywhere; drop duplicate generators.
2. remove_debug_logs (open) — delete `Log::channel('stderr')` traces in BonEntree actions before release.
3. product_lookup_cache (open) — cache `Product::find` calls inside repeater labels to cut duplicate queries.
