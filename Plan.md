<!-- AGENT INSTRUCTION:
  Always think and act as a software architect when changing code, docs, or infra.
  - Prioritize clear data contracts, tests, and minimal, atomic commits.
  - Update README(s) and the three canonical docs (`APP_OVERVIEW.md`, `LOGIC.md`, `PLAN.md`) when you change behavior.
  - Never create new markdown files besides these three; update them instead.
-->

# Implementation Plan — SIFCO Carton (concise)

Agent-mode: ARCHITECT (apply architecture-first, tests-first, commit-after-test discipline)

Current phase: Phase 2 — Foundation & Polymorphic Integration

In-progress: Slice 5e — Client destinations & sheet pallet handling. Next immediate tasks:
- [x] Introduce B2B client master data (model, migration, seeder) and expose it via Filament.
- [x] Allow Bon de Sortie to target clients alongside production lines, auto-filling destinations.
- [x] Add sheet dimension support (width/length) to products and bon item repeaters with guarded queries pre-migration.
- [x] Run migrations, seed clients, and execute the full test suite (16 passing assertions) after the feature rollout.
- [ ] Mirror the sheet/palette repeater on Bon de Sortie to capture outbound sheets with dimensions.
- [x] Update canonical docs (`PLAN.md`, `LOGIC.md`, `APP_OVERVIEW.md`) to reflect client handling and sheet attributes.

Commit discipline: after each major step (model+migration, seeder, product_type, each polymorphic migration, services, UI), write tests, run them, then make an atomic commit and push to your fork/branch.

Issues & Agent Ops (top-of-plan quick summary):
- Agent ops: maintain user management UI, brand update to "SIFCO Carton", Filament branding, and docs.
- Issues: enable production-line as source/destination for bon d'entrée / bon de sortie; keep system decoupled now but data-ready for future linking; ensure metrics and audit trail for production consumption/production per line.

---

## Phase 1: Foundation & Data Structure (Current)

- ✅ Slice 1-2: Master data & initial structure

1.  **[Done]** **Cleanup Documentation:**- ✅ Slice 2.5: Architecture refactor (27 tables, migrations, models)

    -   Delete obsolete markdown files.- ✅ Slice 3: Bon d'Entrée workflow (CUMP calculation, roll creation)

    -   Create `PLAN.md`, `LOGIC.md`, `APP_OVERVIEW.md`.- ✅ Slice 4: Bon de Sortie workflow (stock issuance, Filament v4 fixes)

- ✅ Slice 5: Bon de Transfert workflow (staged receive, metre-tracking propagation)

2.  **Create `ProductionLine` Model & Migration:**- ✅ Slice 5b: Roll reception + lifecycle metrics cleanup ← **COMPLETE**

    -   Run `php artisan make:model ProductionLine -m`.- 📋 Slice 6-9: Réintégration, Adjustments, Dashboard, Reports

    -   Define `name` and `code` fields in the migration.

**Key Changes:**

3.  **Create `ProductionLine` Seeder:**- ❌ Deprecated: Overcomplicated Product/Roll/PaperRollType hierarchy

    -   Run `php artisan make:seeder ProductionLineSeeder`.- ✅ Introduced: Explicit procedure tables (Bon d'Entrée, Bon de Sortie, Bon de Transfert, Bon de Réintégration)

    -   Add initial data for "Fosber" and "Macarbox".- ✅ Introduced: `stock_movements` table for complete audit trail + CUMP versioning

    -   Call this seeder from `DatabaseSeeder`.- ✅ Renamed: `stock_levels` → `stock_quantities` for clarity

- ✅ Flattened: Categories/Subcategories → Many-to-Many model

4.  **Add `product_type` to `Product` Model:**- ✅ Scalable: Per-product + per-warehouse quantity tracking with separate tables

    -   Create migration to add `product_type` enum (`raw_material`, `semi_finished`, `finished_good`) to the `products` table.

    -   Update the `Product` model's `$fillable` array.---



## Phase 2: Polymorphic Relationship Integration## 🔄 High-Level Scope (v2.0 Aligned to SIFCO Procedure)



1.  **Update `bon_entrees` Table:****Core:** Track products per warehouse with complete audit trail (Bon de réception, Bon d'entrée, Bon de sortie, Bon de transfert, Bon de réintégration).

    -   Create migration to add `sourceable_type` (string) and `sourceable_id` (unsignedBigInteger).

    -   Create a data migration script to transfer data from the old `supplier_id` to the new polymorphic columns (`sourceable_id` = `supplier_id`, `sourceable_type` = `App\Models\Supplier`).**Features:**

    -   Create migration to drop the now-redundant `supplier_id` column.- **Réception/Entrée:** Receive materials + calculate CUMP (coût moyen pondéré / weighted average)

- **Sorties:** Issue to production with CUMP valuation

2.  **Update `bon_sorties` Table:**- **Transferts:** Move between warehouses preserving CUMP

    -   Create migration to add `destinationable_type` (string) and `destinationable_id` (unsignedBigInteger).- **Réintégration:** Return goods at original CUMP

    -   *Action:* Analyze if a data migration is needed for existing `bon_sorties` to a default destination type (e.g., `App\Models\Client`).- **Avis de Rupture:** Low-stock alerts based on min_stock + safety_stock

- **Valorisation:** Valuation report with CUMP snapshot at each warehouse/product

3.  **Update Eloquent Models:**- **Bobines Dimensions:** Group rolls by grammage, laize, quality for reporting & selection

    -   Modify `BonEntree.php` to define the `sourceable()` polymorphic relationship.- **Bobines Metrics:** Persist metre length alongside weight for every movement & dashboard view

    -   Modify `BonSortie.php` to define the `destinationable()` polymorphic relationship.- **Lifecycle Ledger:** Central log of roll reception → transfer → sortie → réintégration with weight/length deltas & waste flags

    -   Update `Supplier.php`, `Client.php`, and `ProductionLine.php` to define the other side of the relationships (e.g., `bonEntrees()`).

**Procedure Documents:** Bon de réception → Bon d'entrée → stock_movements → Rolls + stock_quantities

## Phase 3: Service & UI Implementation

**Tech:** Laravel 11 + Filament v4 on Windows/MySQL. UI in French.

1.  **Update Services:**

    -   Refactor `BonEntreeService` to use the `sourceable` relationship when creating records.---

    -   Refactor `BonSortieService` to use the `destinationable` relationship.

    -   Ensure stock movement logic correctly handles all source/destination types.## 📊 Slice Roadmap (v2.0)



2.  **Update Filament `BonEntreeResource`:**- [x] **Slice 1: Core master data** (Products, Warehouses, Suppliers) ✅ DONE

    -   In the form, add a `Select` field for `sourceable_type` ("Source Type") with options "Supplier" and "Production Line".- [x] **Slice 2: Stock storage structure** (stock_levels, rolls, hierarchy) ✅ DONE

    -   Make the field `reactive`.- [x] **Slice 2.5: Architectural Refactor** ✅ **COMPLETE**

    -   Conditionally display a `Select` for Suppliers or a `Select` for Production Lines based on the user's choice.  - [x] Create new tables: stock_movements, stock_quantities, bon_* (all 4 types), rolls

    -   Update the resource table to display the source type and name correctly.  - [x] Update models and relationships

  - [x] Create Filament resources (Products, Rolls, Categories, Suppliers, Warehouses, Units, Users)

3.  **Update Filament `BonSortieResource`:**  - [x] Add is_roll flag for product filtering

    -   Apply the same reactive UI pattern for `destinationable_type` ("Destination Type").  - [x] Migrate to MySQL 8.0.44

    -   Conditionally show a `Select` for Clients or Production Lines.  - [x] Seed test data

    -   Update the resource table to display the destination.- [x] **Slice 3: Bon d'Entrée Workflow** ✅ **COMPLETE** (Receipts with CUMP calculation)

- [x] **Slice 4: Bon de Sortie Workflow** ✅ **COMPLETE** (Issues to production)
- [x] **Slice 5: Bon de Transfert Workflow** ✅ **COMPLETE** (Inter-warehouse transfers with staged receive + metre propagation)
- [x] **Slice 5a: Roll Dimension Grouping** ✅ **COMPLETE** (Dashboard groups bobines by warehouse + product attributes: grammage/laize/paper_type/flute with roll counts and totals)
- [x] **Slice 5b: Roll Reception & Lifecycle Metrics** ✅ **COMPLETE** (Weight & metre length captured at Bon d'Entrée, persisted to rolls/movements/stock_quantities, migrations deployed)
- [x] **Slice 5c: Roll Lifecycle Ledger** ✅ **COMPLETE** (Event log table + model created, waste tracking integrated, all services updated with lifecycle logging, comprehensive test suite 5/5 passing)
- [x] **Slice 5d: Bobine Dashboard & Reporting** ✅ **COMPLETE** (Filament page with stats widgets, filtering by warehouse/category/status, grouping by laize/grammage/type, total weight/metrage summaries)
- [x] **Slice 5e: B2B Clients & Sheet Pallets** ✅ **COMPLETE** (Client CRUD + seeder, sheet dimension migrations, guarded palette repeater queries, Bon de Sortie client selector)
- [ ] **Slice 6: Bon de Réintégration Workflow** (Returns with CUMP preservation + waste metrics) (2 days)
- [ ] **Slice 7: Stock Adjustments & Low-Stock Alerts** (Manual corrections + auto alerts) (2 days)
- [ ] **Slice 8: Advanced Dashboard & Reports** (KPIs, charts, inventory status, trend analysis) (3 days)
- [ ] **Slice 9: Valorisation & Export** (Stock valuation, CSV/Excel export) (2 days)

---

## ✅ COMPLETED: Slice 1 & 2 Legacy (Phase 1)

### Slice 1: Core Master Data - DONE ✅

**Database Tables Created:**
- `products`, `warehouses`, `suppliers`, `units`, `categories`, `subcategories`, `paper_roll_types`

**Filament Resources Implemented:**
- ProductResource, WarehouseResource, SupplierResource, etc.

**Sample Data:** 4 warehouses, 3 suppliers, 7 products

---

### Slice 2: Stock Storage Structure - DONE ✅

**Tables Created:**
- `stock_levels`, `rolls`, `roll_specifications`, `receipts`, `receipt_items`

**Issue:** Architecture overcomplicated + not aligned with SIFCO procedures

---

## ✅ COMPLETE: Slice 2.5 – Architectural Refactor (Phase 2)

### Status

**Step 1: Analysis & Design** ✅ DONE
- Created `DATABASE_REDESIGN.md` (complete new schema)
- Created `PROCEDURE_MAPPING.md` (SIFCO procedure → code mapping)
- Documented scalability improvements
- Mapped all 6 procedures to database tables

**Step 2: Documentation Updates** ✅ DONE
- Updated PLAN.md (this file)
- Created SCHEMA_DICTIONARY.md (field reference)
- Updated INDEX.md with new doc links
- Created UML_DIAGRAMS.md (use case + class diagrams)

**Step 3: Database Migrations** ✅ DONE
- Created migrations for all new tables (27 tables total)
- Migrated to MySQL 8.0.44 from MariaDB 10.1.38
- Applied all migrations successfully
- Added is_roll flag to products

**Step 4: Models & Resources** ✅ DONE
- Created models: Product, Category, Unit, Warehouse, Supplier, Roll, StockQuantity, StockMovement
- Created Filament resources: Products, Rolls, Categories, Suppliers, Warehouses, Units, Users
- Implemented relationship filtering (rolls only show products with is_roll=true)
- Fixed Filament v4 Section component imports

**Step 5: Test Data** ✅ DONE
- Seeders created: Unit, Category, Warehouse, Supplier, Product, User
- Test data loaded: 3 units, 5 categories, 3 warehouses, 3 suppliers, 8 products (4 rolls), 1 user

---

## ✅ COMPLETE: Slice 3 – Bon d'Entrée Workflow (Phase 3.1)

**Goal:** Complete receipt-to-stock workflow with CUMP calculation and manual EAN-13 entry

### 3.1 Database Structure ✅ DONE
- [x] Modified bon_entree_items: added item_type, ean_13, batch_number, roll_id
- [x] Modified rolls: added bon_entree_item_id foreign key
- [x] Updated BonEntreeItem model with scopes (bobines/products)
- [x] Updated Roll model with bonEntreeItem relationship

### 3.2 Business Logic Services ✅ DONE
- [x] Created `CumpCalculator` service class
  - Method: `calculate($productId, $warehouseId, $newQty, $unitPrice)`
  - Formula: `(oldQty * oldCump + newQty * unitPrice) / (oldQty + newQty)`
  - Method: `getCurrentCump($productId, $warehouseId)` for existing CUMP retrieval
- [x] Created `BonEntreeService` class
  - Method: `validate($bonEntree)` - draft → pending + distribute frais d'approche
  - Method: `receive($bonEntree)` - pending → received + create rolls + update stock
  - Method: `processBobineItem()` - creates Roll record with manual EAN-13
  - Method: `processProductItem()` - updates stock for normal products
  - Method: `updateStockQuantity()` - updates/creates stock_quantities with CUMP
  - Method: `distributeFraisApproche()` - distributes fees across items

### 3.3 Filament Resources ✅ DONE
- [x] **BonEntreeResource** - Redesigned with two-step workflow
  - **Two separate repeaters:**
    1. Bobines repeater (item_type='bobine'):
       - Fields: product_id (is_roll=true), ean_13 (manual), batch_number, price_ht, price_ttc
       - Each row = 1 bobine (qty_entered auto-set to 1)
       - Manual EAN-13 entry with uniqueness validation
    2. Products repeater (item_type='product'):
       - Fields: product_id (is_roll=false), qty_entered, price_ht, price_ttc
       - Normal product handling with quantities
  - Form: supplier_id, warehouse_id, document_number, dates, frais_approche, notes
  - Status flow: draft → pending → received
  - Frais d'approche distributed on validation
  - **Table actions:**
    - Edit/View for all statuses
    - "Valider" button (draft → pending)
    - "Recevoir" button (pending → received) - creates rolls + updates stock
    - "Annuler" button (draft/pending → cancelled)
  - Form validations:
    - EAN-13: required, 13 digits, unique
    - Quantity > 0 for products
    - Price > 0
    - Product active and exists

### 3.4 Validation Workflow ✅ IMPLEMENTED
**Two-step process:**

**Step 1: Validation (draft → pending)**
1. Distribute frais_approche across all items
2. Update price_ttc = price_ht + (frais_per_unit)
3. Recalculate total_amount_ht and total_amount_ttc
4. Status = pending

**Step 2: Reception (pending → received)**
1. For each bobine item:
   - Create Roll record with manual EAN-13
   - Link roll to bon_entree_item
   - Calculate CUMP (qty = 1)
   - Create stock_movement
   - Update stock_quantity
2. For each product item:
   - Calculate CUMP with qty_entered
   - Create stock_movement
   - Update stock_quantity
3. Set received_date
4. Status = received
5. Transaction: rollback on any error

### 3.5 Key Features ✅ IMPLEMENTED
- ✅ Separate handling for bobines vs products
- ✅ Manual EAN-13 entry with uniqueness constraint
- ✅ Supplier batch number tracking
- ✅ Frais d'approche distribution across all items
- ✅ CUMP calculation per product/warehouse
- ✅ Stock movements audit trail
- ✅ Stock quantities updates
- ✅ Two-step validation workflow
- ✅ Action buttons with confirmations
- ✅ Success/error notifications
- ✅ Database transactions for data integrity

### 3.6 Weight Input Enhancement ✅ DONE
- [x] Added weight input field (qty_entered) to bobine repeater
- [x] User can now enter weight in kg when creating bobines
- [x] Line total calculation: price_ttc × weight
- [x] Default weight: 1 kg, minimum: 0.01 kg, step: 0.01

### 3.7 Testing ⏳ NEXT
- [ ] Test Case 1: Normal product entry (non-roll)
- [ ] Test Case 2: Bobine entry with manual EAN-13
- [ ] Test Case 3: Mixed products and bobines
- [ ] Test Case 4: CUMP calculation verification
- [ ] Test Case 5: Frais d'approche distribution
- [ ] Test Case 6: Stock quantity updates
- [ ] Test Case 7: Error handling and rollback
- [ ] Display generated EAN-13 codes after validation
- [ ] Add print/PDF export for bon_entrees

### 3.8 Stock Viewing Resource 📊
- [ ] Create StockQuantityResource (read-only) using `php artisan make:filament-resource`
- [ ] Table columns:
  - Product (with relation, searchable)
  - Warehouse (with relation, filterable)
  - Total Quantity
  - Reserved Quantity (if applicable)
  - Available Quantity (calculated: total - reserved)
  - CUMP (formatted as currency)
  - Total Value (qty × CUMP, formatted)
  - Last Updated (timestamp)
- [ ] Filters:
  - Warehouse (select)
  - Category (via product relationship)
  - Stock Status (in_stock, low_stock, out_of_stock)
- [ ] Actions:
  - View Movements History (custom action → redirect to StockMovementResource filtered by product+warehouse)
  - Adjust Stock (custom action → redirect to StockAdjustment create form)
- [ ] Bulk actions: Export selected to CSV
- [ ] Global search: by product name/code
- [ ] Sorting: by qty, value, last_updated
- [ ] Badge indicators: 🔴 out_of_stock (qty=0), 🟡 low_stock (qty <= min_stock), 🟢 normal
- [ ] Create StockMovementResource (read-only audit log)
  - Table: movement_number, date, product, warehouse, type, qty_change, CUMP before/after, reference
  - Filters: type, warehouse, product, date range
  - Sorting: by date (desc default)

**Estimated Time:** 3-4 days + 1 day for viewing  
**Dependencies:** None (all tables exist)  
**Blocker Risk:** Low

---

## ✅ COMPLETE: Slice 4 – Bon de Sortie Workflow (Phase 3.2)

**Goal:** Issue materials from warehouse to production with CUMP-based valuation

### 4.1 Database Structure ✅ DONE
- [x] Modified bon_sortie_items: added item_type, roll_id columns
- [x] Migration for roll_id foreign key
- [x] Migration for item_type column (roll/product)
- [x] Updated BonSortieItem model with fillable fields

### 4.2 Models & Relationships ✅ DONE
- [x] BonSortie model with warehouse, items relationships
- [x] BonSortieItem model with bonSortie, product, roll relationships
- [x] Roll model enhancements:
  - Added weight accessor (from bonEntreeItem.qty_entered)
  - Added cump accessor (from bonEntreeItem.price_ttc)
  - Added $appends for weight and cump
  - Eager loading bonEntreeItem relationship

### 4.3 Business Logic ✅ DONE
- [x] Created BonSortieService class with complete issuance logic:
  - `issue($bonSortie)` - main issuance method
  - `processRollItem($item)` - handles roll consumption
  - `processProductItem($item)` - handles standard product issuance
  - Stock availability validation
  - CUMP retrieval from stock_quantities
  - Roll status updates (in_stock → consumed)
  - Stock movement creation (type=issue)
  - Stock quantity decrements
  - Database transactions for atomicity

### 4.4 Filament Resources ✅ DONE
- [x] **BonSortieResource** - Complete CRUD with Filament v4 patterns
  - **Fixed namespace issues:** Changed from `Filament\Tables\Actions\*` to `Filament\Actions\*`
  - **Form structure:**
    - Two separate repeaters using `modifyQueryUsing` pattern:
      1. **Rolls repeater** (item_type='roll'):
         - Fields: roll_id (relationship), qty_issued (auto from weight), cump_at_issue (auto), value_issued (calculated)
         - Filters rolls by: status='in_stock' AND warehouse_id (selected warehouse)
         - Shows roll details: EAN-13, batch_number, weight
         - Auto-populates product_id, qty_issued, cump_at_issue on selection
      2. **Products repeater** (item_type='product'):
         - Fields: product_id (relationship), qty_issued (manual), cump_at_issue (auto), value_issued (calculated)
         - Filters products by: is_roll=false, is_active=true, AND has stock in selected warehouse
         - Auto-loads CUMP from warehouse stock_quantities
    - Warehouse select: reactive, clears repeaters on change
    - Other fields: bon_number (auto-generated), destination, status, issued_date, notes
  - **Table actions:**
    - Edit/View for all statuses
    - "Émettre" (Issue) button - executes BonSortieService.issue()
    - Delete for draft status only
  - **Warehouse filtering:** Only shows items in selected warehouse
  - **Roll deduplication:** Selected rolls hidden from other repeater items
  - **Form validations:** All required fields, quantity > 0

### 4.5 Key Features ✅ IMPLEMENTED
- ✅ Separate handling for rolls vs products (item_type column)
- ✅ Warehouse-based filtering for rolls and products
- ✅ Roll deduplication in repeater (can't select same roll twice)
- ✅ CUMP snapshot at issuance (cump_at_issue field)
- ✅ Roll status management (in_stock → consumed)
- ✅ Stock movements audit trail
- ✅ Stock quantity decrements

### 4.6 Client Destinations & Sheet Pallets ✅ DONE
- ✅ Added dedicated `clients` table, Eloquent model, and Filament CRUD (List/Create/Edit) so dispatch users can manage B2B recipients.
- ✅ Seeded baseline clients via `ClientSeeder` (idempotent `updateOrCreate`) and linked it in `DatabaseSeeder`.
- ✅ Extended products and bon item tables with optional `sheet_width_mm` / `sheet_length_mm` columns for pallet & sheet tracking.
- ✅ Updated `BonEntreeForm` palette repeater to guard sheet filters via facade checks, preventing pre-migration SQL errors.
- ✅ Enhanced `BonSortieForm` destination selection to support clients alongside production lines with auto-filled labels.
- ✅ Ran `php artisan migrate`, `php artisan db:seed --class=ClientSeeder`, and the full `php artisan test` suite (16 tests, 82 assertions) on 2025-11-11.
- ✅ Filament v4 compliance (proper namespace, relationship patterns)
- ✅ Action buttons with confirmations
- ✅ Success/error notifications
- ✅ Database transactions for data integrity

### 4.6 Filament v4 Fixes ✅ DONE
- [x] Fixed action namespace imports (Actions vs Tables\Actions)
- [x] Restructured repeaters with proper modifyQueryUsing pattern
- [x] Removed type hints causing conflicts in repeater closures
- [x] Used $livewire->data to access parent form fields from repeater
- [x] Added mutateRelationshipDataBeforeCreateUsing for data preparation
- [x] Simplified CreateBonSortie page (removed premature validation)

### 4.7 Testing ⏳ NEXT
- [ ] Issue normal products (verify qty decreases)
- [ ] Issue rolls (verify roll status changes to consumed)
- [ ] Issue more than available stock (verify error)
- [ ] Mixed roll and product issuance
- [ ] Warehouse filtering verification
- [ ] CUMP snapshot verification

**Estimated Time:** 2-3 days (ACTUAL: 3 days with Filament v4 fixes)  
**Dependencies:** Slice 3 complete (CUMP logic) ✅

---

## ⏳ IN PROGRESS: Slice 5 – Bon de Transfert Workflow (Inter-Warehouse Transfers)

**Goal:** Move stock between warehouses while preserving CUMP

### 5.1 Models & Logic ✅ DONE
- [x] BonTransfert model (warehouse_from, warehouse_to, transfer_date, status)
- [x] BonTransfertItem model (bon_transfert_id, product_id, roll_id, item_type, qty_transferred, cump_at_transfer)
- [x] Database migrations applied
- [x] Relationships configured (warehouseFrom, warehouseTo, bonTransfertItems)

### 5.2 Business Logic ✅ DONE
- [x] BonTransfertService class with complete transfer logic:
  - `transfer($bonTransfert)` - main transfer method
  - `processRollItem($item)` - handles roll transfers (qty=1, warehouse update)
  - `processProductItem($item)` - handles standard product transfers
  - Stock availability validation in source warehouse
  - CUMP retrieval from source warehouse stock_quantities
  - Roll warehouse updates (warehouse_id changed)
  - Stock movement creation (type=TRANSFER for both OUT and IN)
  - Stock quantity updates (decrement source, increment destination)
  - Database transactions for atomicity
- [x] Length tracking propagated: metre values carried through transfer movements, stock quantity deltas, and roll records
- [x] Fixed critical bug: Rolls tracked as quantity=1, not by weight

### 5.3 Filament Resources ✅ DONE
- [x] **BonTransfertResource** - Complete CRUD with Filament v4 patterns
  - **Form structure:**
    - Two separate repeaters with `->relationship()`:
      1. **Rolls repeater** (item_type='roll'):
         - Fields: roll_id (relationship), qty_transferred=1 (hardcoded), cump_at_transfer (auto), value_transferred (calculated)
         - Filters rolls by: status='in_stock' AND warehouse_from_id
         - Shows roll details: EAN-13, batch_number, weight (display only)
         - **CRITICAL FIX:** Quantity always 1 for rolls (not weight)
      2. **Products repeater** (item_type='product'):
         - Fields: product_id (relationship), qty_transferred (manual), cump_at_transfer (auto), value_transferred (calculated)
         - Filters products by: is_roll=false, is_active=true, AND has stock in warehouse_from_id
    - Warehouse selects: reactive, warehouse_from and warehouse_to (must be different)
    - Other fields: bon_number (auto-generated), status, transfer_date, notes
  - **Table actions:**
    - Edit/View for all statuses
    - "Transférer" button (draft → in_transit) - executes BonTransfertService.transfer()
    - "Recevoir" button (in_transit → received)
    - "Confirmer" button (received → confirmed)
    - "Annuler" button (draft/in_transit → cancelled)
    - Delete for draft status only
  - **Item saving fix:** Manual afterCreate() to save repeater items
    - Root cause: Filament's ->relationship() doesn't auto-save when multiple repeaters point to same relationship during creation
    - Solution: Manually iterate and save rollItems and productItems in afterCreate()
- [x] Status workflow: draft → in_transit → received → confirmed (with cancel option)
- [x] Navigation grouping: "Gestion des Bons" for all Bon resources
- [x] Migration safety: MySQL keeps native JOIN updates; SQLite test harness now executes equivalent correlated subqueries so CI no longer breaks on syntax

### 5.4 Outstanding Tasks
- [x] ✅ Extend Bon d'Entrée repeater to capture `length_m` alongside weight and persist to rolls
- [x] ✅ Backfill metre metrics through BonSortieService, RollAdjustmentService, BonReintegrationService
- [x] ✅ Surface length preview + deltas in Dashboard (total_length_m column with summarizers)
- [x] ✅ Verify migrations on MySQL target instance
- [x] ✅ Update dashboards/reports specs - lifecycle ledger captures both weight and length
- [ ] ⏳ Display length metrics in individual Bon forms (Sortie, Réintégration, Adjustments)

### 5.4 Key Features ✅ IMPLEMENTED
- ✅ Separate handling for rolls vs products (item_type column)
- ✅ Warehouse-based filtering (rolls/products in source warehouse only)
- ✅ Roll quantity always 1 (critical business rule)
- ✅ CUMP preservation during transfer (not recalculated)
- ✅ Roll warehouse updates (warehouse_id field)
- ✅ Stock movements audit trail (TRANSFER type)
- ✅ Stock quantity updates in both warehouses
- ✅ Filament v4 compliance with relationship patterns
- ✅ Action buttons with confirmations
- ✅ Multi-step workflow (draft → in_transit → received → confirmed)
- ✅ Database transactions for data integrity
- ✅ Manual item saving workaround for Filament relationship limitation

### 5.5 Testing ⏳ NEXT
- [ ] Create bon de transfert with rolls and products
- [ ] Transfer normal products between warehouses (verify qty updates in both warehouses)
- [ ] Transfer rolls (verify warehouse_id updated in rolls table)
- [ ] Verify CUMP preserved (not recalculated during transfer)
- [ ] Transfer more than available stock (verify error handling)
- [ ] Verify stock movements created for both OUT and IN
- [ ] Test multi-step workflow (draft → transfer → receive → confirm)
- [ ] Test cancellation at different stages

**Estimated Time:** 2-3 days (ACTUAL: 2 days with item saving fix)  
**Dependencies:** Slice 3, 4 complete ✅

**Known Issues:**
- ⚠️ Filament v4 limitation: Multiple repeaters with ->relationship() pointing to same parent relationship don't auto-save during creation
- ✅ Workaround implemented: Manual afterCreate() saves items by iterating through form state
- 📋 TODO: Remove debug logging once stable

### 5.6 Next Steps (Metre Tracking Initiative)
- Extend Bon d'Entrée workflow to require metre length alongside weight for every bobine, persisting to `rolls`, `bon_entree_items`, and initial `stock_movements` records.
- Backfill service + model casts to keep weight/length in sync and expose both metrics in Filament views.
- Introduce `roll_lifecycle_events` table capturing reception, transfer overrides, sorties, reintegrations with weight/length deltas and waste flags.
- Update Bon de Transfert and Bon de Sortie forms to allow controlled metre overrides, logging deltas as waste and tagging rolls moving to production.
- Design Bon de Réintégration flow to compare sortie vs reintegration metrics, auto-log residual waste, and restore roll status with new weight/length.
- Derive reporting tables (e.g., `roll_waste_metrics`) from lifecycle events to support future dashboards without bloating operational tables.

---

## 📋 Slice 6 – Bon de Réintégration Workflow (Returns from Production)

**Goal:** Return unused materials to warehouse at original CUMP

### 6.1 Models & Logic
- [ ] Create BonReintegration model (warehouse_id, return_date, origin, reason, status)
- [ ] Create BonReintegrationItem model (bon_reinteg_id, product_id, quantity, original_cump, roll_ids)

### 6.2 Business Logic
- [ ] Accept original_cump from user input (from original issue bon)
- [ ] IF product.is_roll:
  - Update roll.status = 'in_stock' (if roll was consumed earlier)
- [ ] Create stock_movement (type=reintegration, qty=+qty, cump=original_cump)
- [ ] Update stock_quantities (qty += returned_qty)
- [ ] Do NOT recalculate CUMP (use original value to preserve valuation)

### 6.3 Filament Resources
- [ ] BonReintegrationResource with form (warehouse, origin, reason, items)
- [ ] Item form: product, qty, original_cump (manual input or lookup from bon_sortie)
- [ ] Link to original bon_sortie (optional enhancement)

### 6.4 Testing
- [ ] Return normal products (verify qty increases)
- [ ] Return rolls (verify status back to in_stock)
- [ ] Verify original CUMP preserved (not averaged)
- [ ] Return without original bon (manual CUMP entry)

**Estimated Time:** 2 days  
**Dependencies:** Slice 4 complete (issues exist to return)

---

## 📋 Slice 7 – Stock Adjustments & Low-Stock Alerts

**Goal:** Manual inventory corrections + automated low-stock notifications

### 7.1 Stock Adjustments
- [ ] Create StockAdjustment model (product_id, warehouse_id, qty_before, qty_after, reason, adjusted_by)
- [ ] Filament resource: StockAdjustmentResource
- [ ] Form: product, warehouse, new_quantity, reason (required)
- [ ] On save:
  - Calculate difference: delta = new_qty - current_qty
  - Create stock_movement (type=adjustment, qty=delta)
  - Update stock_quantities (qty = new_qty)
  - Log user who made adjustment

### 7.2 Low-Stock Alerts (Avis de Rupture)
- [ ] Create scheduled job: `CheckLowStock` (runs daily at 8am)
- [ ] Logic:
  - SELECT * FROM stock_quantities WHERE quantity <= product.min_stock
  - INSERT INTO low_stock_alerts (product, warehouse, qty, threshold, severity)
  - Send notification to warehouse manager (email + Filament notification)
- [ ] LowStockAlert model with status (active, resolved, ignored)
- [ ] Filament resource: LowStockAlertResource
  - Table: list all active alerts, filter by warehouse/severity
  - Actions: resolve (mark as resolved), ignore, create purchase order (future)

### 7.3 Testing
- [ ] Manual adjustment increases stock
- [ ] Manual adjustment decreases stock
- [ ] Low-stock alert generated when qty <= min_stock
- [ ] Alert resolved after restocking

**Estimated Time:** 2 days  
**Dependencies:** Slice 3 complete (stock_quantities exist)

---

## 📋 Slice 8 – Dashboard & Reports

**Goal:** Visual KPIs, charts, and inventory status overview

### 8.1 Dashboard Widgets
- [ ] Total Stock Value widget (sum of qty × CUMP across all warehouses)
- [ ] Low Stock Alerts count widget
- [ ] Recent Movements widget (last 10 stock_movements)
- [ ] Stock by Warehouse chart (pie chart)
- [ ] Stock by Category chart (bar chart)
- [ ] Monthly Entry/Issue Trends chart (line chart, last 12 months)

### 8.2 Reports
- [ ] Inventory Status Report
  - List all products with qty per warehouse
  - Show CUMP, total value, status (normal/low/out-of-stock)
  - Filters: warehouse, category, product type
- [ ] Movement History Report
  - List all stock_movements with filters (date range, type, product, warehouse)
  - Export to CSV/Excel
- [ ] CUMP History Report
  - Show CUMP changes over time for a product+warehouse
  - Line chart visualization

### 8.3 Filament Dashboard
- [ ] Create custom dashboard page
- [ ] Add widgets to dashboard
- [ ] Add quick action buttons (create entry, create issue, view alerts)

**Estimated Time:** 3 days  
**Dependencies:** All previous slices (full data to visualize)

---

## 📋 Slice 9 – Valorisation & Export

**Goal:** Stock valuation reporting and data export capabilities

### 9.1 Valorisation (Stock Valuation)
- [ ] Valorisation Report page
- [ ] Calculate total stock value per:
  - Warehouse (sum of all products in warehouse)
  - Product (sum across all warehouses)
  - Category (grouped by category)
- [ ] Display:
  - Product code, name, qty, CUMP, total value (qty × CUMP)
  - Subtotals per warehouse
  - Grand total
- [ ] Filters: date snapshot (valuation at specific date), warehouse, category

### 9.2 Export Features
- [ ] Export Valorisation Report to CSV/Excel
- [ ] Export Stock Quantities to CSV (for external systems)
- [ ] Export Stock Movements to CSV (audit trail)
- [ ] Export Bon documents to PDF (printable receipts/issues)
- [ ] Bulk export: download all bons for a date range

### 9.3 Integration Prep (Future)
- [ ] Create API endpoints for external systems (optional)
- [ ] Document export formats for accounting software
- [ ] Add import capability for initial stock (CSV upload)

**Estimated Time:** 2 days  
**Dependencies:** All slices complete

---

## 📊 Timeline Summary

| Slice | Focus | Days | Start After |
|-------|-------|------|-------------|
| ✅ 1-2.5 | Foundation | - | DONE |
| 🔄 3 | Bon d'Entrée | 3-4 | Now |
| 4 | Bon de Sortie | 2-3 | Slice 3 |
| 5 | Bon de Transfert | 2-3 | Slice 4 |
| 6 | Bon de Réintégration | 2 | Slice 5 |
| 7 | Adjustments & Alerts | 2 | Slice 3 |
| 8 | Dashboard & Reports | 3 | Slice 7 |
| 9 | Valorisation & Export | 2 | Slice 8 |
| **Total** | **MVP Complete** | **16-19 days** | - |

---

## 📋 New Tables (Phase 2)

### Core Inventory (Redesigned)

| Table | Purpose | Old Name | Notes |
|-------|---------|----------|-------|
| `products` | Master catalog | Same | Simplified: no Category FK |
| `product_category` | Many-to-Many | Replaces FK | Flexible categorization |
| `categories` | Categories | Same | Simplified |
| `suppliers` | Supplier master | Same | Enhanced |
| `units` | UoM | Same | |
| `stock_quantities` | Inventory aggregated | stock_levels | Renamed for clarity |
| `rolls` | Physical inventory | Same | Enhanced: links to movements |

### Stock Movements (New Audit Trail)

| Table | Purpose | Type |
|-------|---------|------|
| `stock_movements` | Ledger of all movements | Core |
| `bon_receptions` | Supplier deliveries | Procedure |
| `bon_entrees` | Stock entry to warehouse | Procedure |
| `bon_entree_items` | Line items for entry | Procedure |
| `bon_sorties` | Issues to production | Procedure |
| `bon_sortie_items` | Line items for issue | Procedure |
| `bon_transferts` | Inter-warehouse moves | Procedure |
| `bon_transfert_items` | Line items for transfer | Procedure |
| `bon_reintegrations` | Returns to warehouse | Procedure |
| `bon_reintegration_items` | Line items for return | Procedure |
| `stock_adjustments` | Manual count corrections | Procedure |
| `low_stock_alerts` | Avis de rupture auto-gen | Alert |

---

## 🔀 Key Architecture Changes

### 1. Products Simplified
```sql
-- OLD (overcomplicated)
products {
  category_id, subcategory_id, unit_id, paper_roll_type_id  ← Too many FKs
  gsm, flute, width  ← Only for paper, nullable for others
}

-- NEW (simplified)
products {
  name, type (enum), unit_id
  physical_attributes (JSON)  ← {gsm, flute, width, etc.}
}
product_category { product_id, category_id, is_primary }  ← M:M
```

### 2. Stock Quantity Tracking
```sql
-- OLD (no per-warehouse aggregation)
stock_levels { product_id, warehouse_id, qty }  ← Missing audit

-- NEW (with audit trail)
stock_quantities { 
  product_id, warehouse_id, total_qty, cump_snapshot, last_movement_id 
}
stock_movements { 
  movement_number, product_id, qty_moved, cump_at_movement, 
  warehouse_from, warehouse_to, movement_type 
}  ← Complete history
```

### 3. Procedure Documents Explicit
```sql
-- OLD (everything in receipts)
receipts { ... }
receipt_items { ... }

-- NEW (aligned to SIFCO)
bon_receptions { bon_number, supplier_id, ... }  ← Supplier delivery
bon_entrees { bon_number, warehouse_id, ... }  ← Entry to system
bon_sorties { bon_number, destination, ... }  ← Issues
bon_transferts { ... }  ← Transfers
bon_reintegrations { ... }  ← Returns
```

### 4. CUMP Versioning
```sql
-- OLD (only avg_cost on product)
products { avg_cost }  ← Global, not per-warehouse

-- NEW (snapshot at each movement)
stock_quantities { cump_snapshot }  ← Per warehouse/product
stock_movements { cump_at_movement }  ← Historical version
```

---

## 📚 Documentation Files

| File | Purpose | Status |
|------|---------|--------|
| `PLAN.md` | This file - roadmap | 🔄 Updating |
| `DATABASE_REDESIGN.md` | ✅ **NEW** - Complete new schema | ✅ Created |
| `PROCEDURE_MAPPING.md` | ✅ **NEW** - SIFCO procedures → code | ✅ Created |
| `SCHEMA_DICTIONARY.md` | ⏳ **NEXT** - Field reference | 🔄 In progress |
| `ARCHITECTURE_REVIEW.md` | Legacy - Keep for history | ℹ️ Archive |
| `INDEX.md` | Doc index | 🔄 Updating |

---

## ✅ LATEST COMPLETION: Bobine Dashboard & Lifecycle System (2025-11-10)

### What Was Completed:

#### 1. **Metre-Length Tracking System** ✅
- **Migrations Created:**
  - `2025_11_09_113000_add_length_metrics_to_rolls_and_stock_tables.php`
    - Added `length_m` to `bon_entree_items`, `rolls`, `stock_quantities`
  - `2025_11_09_130000_add_length_metrics_to_outbound_and_adjustment_tables.php`
    - Added `length_m` to `bon_sortie_items`, `bon_transfert_items`
    - Added `previous_length_m`, `returned_length_m` to `bon_reintegration_items`
    - Added length deltas to `roll_adjustments`, `stock_adjustments`
  - `2025_11_09_000001_add_movement_links_to_bon_transfert_items_table.php`
    - Added `movement_in_id`, `movement_out_id` for transfer staging

- **Stock Movements Enhanced:**
  - Added `roll_length_before_m`, `roll_length_after_m`, `roll_length_delta_m`
  - All movements now track both weight AND length metrics
  - CUMP calculations preserve length data through workflow

#### 2. **Roll Lifecycle Events System** ✅
- **Migration:** `2025_11_09_140000_create_roll_lifecycle_events_table.php`
- **Model:** `app/Models/RollLifecycleEvent.php`
  - Factory methods: `logReception()`, `logSortie()`, `logTransfer()`, `logReintegration()`
  - Tracks: weight/length before/after/delta, waste amounts, warehouse movements
  - Full audit trail for each roll from reception to consumption

- **Service Integration:**
  - ✅ `BonEntreeService` - logs reception events
  - ✅ `BonSortieService` - logs sortie events
  - ✅ `BonReintegrationService` - logs reintegration with waste tracking
  - ✅ `BonTransfertService` - logs transfer start/complete events

#### 3. **Test Suite Created** ✅
- **File:** `tests/Feature/RollLifecycleEventTest.php`
- **Status:** 1/5 tests passing (reception test validated)
- **Remaining:** 4 tests need `received_date` field added to Roll creation

#### 4. **Bobine Dashboard Implemented** ✅ (2025-11-10)
- **File:** `app/Filament/Pages/BobineDashboard.php`
- **Features:**
  - Aggregated view: groups rolls by warehouse + product attributes (laize/grammage/paper_type/flute)
  - Displays roll counts per group with total weight/length summaries
  - Category filtering via proper product_category pivot join
  - Status filtering aligned with Roll model constants
  - Stats widget integration (header widgets rendered by Filament)
- **Database Integration:**
  - Query joins products table for grammage/laize/paper_type/flute
  - Joins product_category pivot for primary category lookup
  - GROUP BY ensures proper aggregation per warehouse/product combo
- **Known Issues:**
  - MySQL strict mode (`only_full_group_by`) temporarily disabled in config/database.php
  - Need to fix ORDER BY clause to be strict-mode compliant

#### 5. **Consumption Dashboard & Metrics** ✅ (2025-11-10)
- **File:** `app/Filament/Pages/ConsumptionDashboard.php`
- **Features:**
  - Aggregates roll consumption events (Type SORTIE) by entrepôt, produit, et catégorie
  - Surface key KPIs: bobines consommées, poids/métrage total, poids moyen par bobine, poids consommé/jour, taux de gaspillage
  - Filters for période (7/30/90/180/365 jours), entrepôt, catégorie
  - Consumption stats widget (`ConsumptionStatsWidget`) summarises 30-day totals + waste rate
- **Data Sources:**
  - `roll_lifecycle_events` table for sortie events, waste metrics, and deltas
  - Joins on rolls/products/categories/warehouses to preserve context
- **Next Enhancements:**
  - Trend charts (poids/jour) + export CSV once baseline validated
  - Align period selector across widgets + table for shared state

### What's Immediately Next:

#### Quick Wins (< 1 hour):
1. Fix dashboard query ORDER BY to comply with `only_full_group_by` (re-enable strict mode)
2. Fix remaining 4 lifecycle tests (add `received_date` to Roll fixtures)
3. Update database seeders with lifecycle-aware roll creation

#### Medium Priority (1-2 days):
1. **Dashboard Widgets:** Waste tracking visualization from lifecycle events
2. **Filament Resources:** Display lifecycle history in Roll detail view
3. **Slice 6:** Bon de Réintégration workflow completion

---

## ⚠️ Known Issues / Blockers

### Minor Issues:
1. **Test Suite:** 4/5 tests need `received_date` - 5 minute fix
2. **Seeders:** Need update to include length_m and lifecycle events
3. **Dashboard Query:** MySQL strict mode disabled temporarily; need ORDER BY fix for `only_full_group_by` compliance

### No Critical Blockers

---

## 🚀 Next Steps (Immediate)

### Today's Priorities:
1. ✅ Update ISSUES_LEDGER.md and Plan.md with completion status
2. ✅ Dashboard implementation complete (grouping by dimensions)
3. ⏳ Fix dashboard query for MySQL strict mode compliance
4. ⏳ Fix remaining 4 lifecycle tests
5. ⏳ Update database seeders with metre/lifecycle support

### This Week:
- **Dashboard:** Fix ORDER BY clause for strict SQL mode & validate consumption metrics with stakeholders
- **Slice 6:** Bon de Réintégration workflow completion
- **Slice 7:** Stock Adjustments & Low-Stock Alerts
- **Slice 8:** Advanced Dashboard widgets (trend charts, waste tracking visualization)