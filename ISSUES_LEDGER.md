# Issues Ledger (Holy File)
- last_update: 2025-11-09
- rule: keep entries concise, status-first, update immediately after change

## High-Priority
1. status_field_bypass (in_review) — status inputs removed/locked across Bon Entrée, Bon Sortie, Roll resources; pages now reject tampering and force actions/services for transitions.
2. transfer_cump_instant_move (open) — `BonTransfertService` increments dest qty + wipes CUMP during draft transfer; defer qty until receive, recompute CUMP, set `last_movement_id`.
3. transfer_edit_lockout (open) — table hides edit for `in_transit` so receive actions unreachable; relax `EditAction` visibility + align status filters in `BonTransfertsTable`.
4. low_stock_schema_drift (open) — `App\Models\LowStockAlert` fields missing in migration; add migration or prune model API before alerts run.
5. rolls_grouping_dimensions (open) — bobine listings + queries must group by grammage/laize/quality; adjust schema/indexing + resources to surface grouping.
6. roll_metrage_tracking (open) — rolls need meter-length tracked alongside weight for stock movements, sorties, transfers, dashboards; extend models, migrations, services.

## Medium-Priority
1. transfer_pending_state (open) — transfer IN movement should stay pending + qty buffered until receive; adjust service logic + statuses.
2. stock_quantity_last_move (open) — `last_movement_id` not set in entry/sortie/transfer services; propagate movement ids when updating stock.
3. transfer_status_vocab (open) — UI enumerations omit `in_transit`/`received`; sync names across service & table badges.

## Low-Priority
1. movement_number_helpers (open) — reuse `StockMovement::generateMovementNumber()` everywhere; drop duplicate generators.
2. remove_debug_logs (open) — delete `Log::channel('stderr')` traces in BonEntree actions before release.
3. product_lookup_cache (open) — cache `Product::find` calls inside repeater labels to cut duplicate queries.
