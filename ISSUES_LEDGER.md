# Issues Ledger (Holy File)
- last_update: 2025-11-09
- rule: keep entries concise, status-first, update immediately after change

## High-Priority
1. status_field_bypass (in_review) — status inputs removed/locked across Bon Entrée, Bon Sortie, Roll resources; pages now reject tampering and force actions/services for transitions.
2. transfer_cump_instant_move (in_review) — draft transfer now stages outbound/inbound movements, defers destination qty until receive, recomputes CUMP, sets `last_movement_id`.
3. transfer_edit_lockout (resolved) — `BonTransfertsTable` now keeps edit access for `in_transit`, and status filters/badges cover the expanded vocabulary.
4. low_stock_schema_drift (open) — `App\Models\LowStockAlert` fields missing in migration; add migration or prune model API before alerts run.
5. rolls_grouping_dimensions (open) — bobine listings + queries must group by grammage/laize/quality; adjust schema/indexing + resources to surface grouping.
6. roll_metrage_tracking (open) — rolls need meter-length tracked alongside weight for stock movements, sorties, transfers, dashboards; extend models, migrations, services.

## Medium-Priority
1. transfer_pending_state (resolved) — inbound movements remain pending and stock is only incremented on receive via `BonTransfertService::receive`.
2. stock_quantity_last_move (open) — `last_movement_id` not set in entry/sortie/transfer services; propagate movement ids when updating stock.
3. transfer_status_vocab (resolved) — service, form schema, and table badges/filters now surface full status list.

## Low-Priority
1. movement_number_helpers (open) — reuse `StockMovement::generateMovementNumber()` everywhere; drop duplicate generators.
2. remove_debug_logs (open) — delete `Log::channel('stderr')` traces in BonEntree actions before release.
3. product_lookup_cache (open) — cache `Product::find` calls inside repeater labels to cut duplicate queries.
