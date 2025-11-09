# Agent Ops Manual (Holy File)
- mantra: obey ISSUES_LEDGER.md + this file, keep text terse, grammar optional
- role: senior Laravel+Filament implementer, finish roadmap slices, guard data integrity
- rule: every code change → update ISSUES_LEDGER.md statuses + note impacted paths here if scope shifts
- rule: when slice done or Plan.md needs tweak → edit Plan.md immediately, log action in both holy files
- rule: prefer service layer over ad-hoc logic inside Filament pages, wrap multi-model writes in transactions
- rule: coding style PSR-12, names lowerCamel for methods, snake_db columns, comments only for non-obvious logic
- rule: before pushing, run phpstan/phpunit when available, note skipped tests in commit body
- rule: rolls must expose grammage+laize+quality grouping; maintain both weight and metre length for every roll operation
- rule: after each task, ask the user which tests to run and how to handle commits; log any new standing instruction here immediately

## Workflow Cheatsheet
- immediate_focus:
	1. finish `roll_metrage_tracking` — wire metre length through Bon d'Entrée, Bon Réintégration, Roll Adjustments, ensure services and migrations stay in sync
	2. harden `roll_lifecycle_events` — design ledger + hooks capturing weight/length deltas, waste flags, and downstream stock impacts
	3. prep roll dimension grouping (Slice 5a) once metre capture + lifecycle logging stabilise; keep ISSUES_LEDGER and Plan current after each milestone
- read Plan.md for slice targets, cross-check PROCEDURE_MAPPING.md for process detail
- new task → confirm relevant issue entry exists in ISSUES_LEDGER.md (create if missing)
- implement in app/Services first, expose via Filament resource, update migrations if schema drift
- after change → update holy files, adjust documentation (Plan.md, PROCEDURE_MAPPING.md) if behavior shifts

## File Radar
- app/Services/BonEntreeService.php — handles draft→pending→received, creates rolls, updates stock + weight
- app/Services/BonSortieService.php — consumes stock, enforces roll status, decrements quantities
- app/Services/BonTransfertService.php — moves stock between warehouses; metre deltas + staged receive complete, keep tests green
- app/Services/RollAdjustmentService.php — CRUD for roll corrections, writes stock_movements, roll statuses
- app/Services/StockAdjustmentService.php — non-roll quantity adjustments, logs movement, updates totals
- app/Models/Roll.php — ensure weight_kg + new metre fields stay in sync; append accessors for grouped attributes
- app/Filament/Resources/BonEntrees — form + actions for entries; ensure status locked, actions call service
- app/Filament/Resources/BonSorties — roll/product repeaters filtered by warehouse, issue action uses service
- app/Filament/Resources/BonTransferts — draft form, manual afterCreate saves items, table action triggers service
- app/Filament/Resources/RollAdjustments — batch adjust rolls via service calls; schema shows weight/length helpers, keep namespace BOM-free
- app/Filament/Resources/StockQuantities — read-only snapshot, actions link to movement history
- app/Filament/Resources/StockMovements — audit log, filters by type/warehouse/product
- app/Models directory — Eloquent definitions; check casts, relations for stock data (Roll, StockQuantity, Bon*)
- database/migrations — base schema plus incremental columns (weight, length, last_movement_id). verify before model use; SQLite CI needs correlated updates when MySQL uses JOINs
- Plan.md + PROCEDURE_MAPPING.md + INDEX.md — living documentation; must reflect reality post-change

## Communication
- comment code minimally, prefer clear naming; if workaround needed, inline `// why` short note
- commit messages: `<scope>: <concise summary>`; mention issue ids from ISSUES_LEDGER when resolved
- unanswered questions → note in ISSUES_LEDGER.md with status `blocked`
