# CartonStock MVP Plan – v2.0 (Restructured)

## 🎯 Executive Summary

**Phase 1 COMPLETED:** Slices 1-2 created basic data structure.  
**Phase 2 CURRENT:** Complete architectural redesign for **SIFCO procedure alignment** + **scalability**.

**Key Changes:**
- ❌ Deprecated: Overcomplicated Product/Roll/PaperRollType hierarchy
- ✅ Introduced: Explicit procedure tables (Bon d'Entrée, Bon de Sortie, Bon de Transfert, Bon de Réintégration)
- ✅ Introduced: `stock_movements` table for complete audit trail + CUMP versioning
- ✅ Renamed: `stock_levels` → `stock_quantities` for clarity
- ✅ Flattened: Categories/Subcategories → Many-to-Many model
- ✅ Scalable: Per-product + per-warehouse quantity tracking with separate tables

---

## 🔄 High-Level Scope (v2.0 Aligned to SIFCO Procedure)

**Core:** Track products per warehouse with complete audit trail (Bon de réception, Bon d'entrée, Bon de sortie, Bon de transfert, Bon de réintégration).

**Features:**
- **Réception/Entrée:** Receive materials + calculate CUMP (coût moyen pondéré / weighted average)
- **Sorties:** Issue to production with CUMP valuation
- **Transferts:** Move between warehouses preserving CUMP
- **Réintégration:** Return goods at original CUMP
- **Avis de Rupture:** Low-stock alerts based on min_stock + safety_stock
- **Valorisation:** Valuation report with CUMP snapshot at each warehouse/product

**Procedure Documents:** Bon de réception → Bon d'entrée → stock_movements → Rolls + stock_quantities

**Tech:** Laravel 11 + Filament v4 on Windows/MySQL. UI in French.

---

## 📊 Slice Roadmap (v2.0)

- [x] **Slice 1: Core master data** (Products, Warehouses, Suppliers) ✅ DONE
- [x] **Slice 2: Stock storage structure** (stock_levels, rolls, hierarchy) ✅ DONE
- [x] **Slice 2.5: Architectural Refactor** ✅ **COMPLETE**
  - [x] Create new tables: stock_movements, stock_quantities, bon_* (all 4 types), rolls
  - [x] Update models and relationships
  - [x] Create Filament resources (Products, Rolls, Categories, Suppliers, Warehouses, Units, Users)
  - [x] Add is_roll flag for product filtering
  - [x] Migrate to MySQL 8.0.44
  - [x] Seed test data
- [ ] **Slice 3: Bon d'Entrée Workflow** ← **CURRENT: Receipts with CUMP calculation** (3-4 days)
- [ ] **Slice 4: Bon de Sortie Workflow** (Issues to production) (2-3 days)
- [ ] **Slice 5: Bon de Transfert Workflow** (Inter-warehouse transfers) (2-3 days)
- [ ] **Slice 6: Bon de Réintégration Workflow** (Returns with CUMP preservation) (2 days)
- [ ] **Slice 7: Stock Adjustments & Low-Stock Alerts** (Manual corrections + auto alerts) (2 days)
- [ ] **Slice 8: Dashboard & Reports** (KPIs, charts, inventory status) (3 days)
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

## 🔄 CURRENT: Slice 3 – Bon d'Entrée Workflow (Phase 3)

**Goal:** Complete receipt-to-stock workflow with CUMP calculation and EAN-13 generation

### 3.1 Models & Relationships ⏳ NEXT
- [ ] Create BonReception model with relationships (supplier, user)
- [ ] Create BonEntree model with relationships (warehouse, reception, user)
- [ ] Create BonEntreeItem model with relationships (bon_entree, product)
- [ ] Add status enum casts (pending, validated, cancelled)
- [ ] Add computed properties (total_value, items_count)

### 3.2 Business Logic Services 📦
- [ ] Create `CumpCalculator` service class
  - Method: `calculateNewCump($productId, $warehouseId, $newQty, $unitPrice)`
  - Formula: `(oldQty * oldCump + newQty * unitPrice) / (oldQty + newQty)`
  - Returns: new CUMP value
- [ ] Create `EanGenerator` service class
  - Method: `generateEan13($productId)` 
  - Format: Product code + sequence + checksum
  - Ensures uniqueness
- [ ] Create `StockMovementService` class
  - Method: `createEntryMovement($bonEntree, $item)`
  - Creates stock_movements record with CUMP snapshot
  - Updates stock_quantities
  - Links to bon_entree for audit

### 3.3 Filament Resources 🎨
- [ ] **BonReceptionResource**
  - Form: supplier_id, reception_date, delivery_note, notes
  - Table: list all receptions, filter by status/date
  - Actions: create, validate, cancel
  - Show details page with validation button
- [ ] **BonEntreeResource**
  - Form: warehouse_id, entry_date, reception_id (optional), notes
  - Repeater for items: product_id, quantity, unit_price
  - Table: list all entries, filter by warehouse/date/status
  - Actions: create, validate, cancel, print
  - Custom page: validation workflow
- [ ] Form validations:
  - Quantity > 0
  - Unit price > 0
  - Product exists and is active
  - Warehouse exists and is active

### 3.4 Validation Workflow 🔄
- [ ] On BonEntree validation (status: pending → validated):
  1. Calculate CUMP for each item
  2. IF product.is_roll = true:
     - Generate EAN-13 for each roll (qty times)
     - Insert into `rolls` table
     - Store roll_ids in bon_entree_items.roll_ids (JSON)
  3. Create stock_movement for each item
  4. Update/insert stock_quantities (qty +=, cump_snapshot)
  5. Update products.avg_cost (global CUMP)
  6. Set validated_by, validated_at
  7. Transaction: rollback on any error

### 3.5 Testing & Validation ✅
- [ ] Test Case 1: Normal product entry (non-roll)
  - Create reception + entry with 1 normal product
  - Validate → verify stock_quantities updated
  - Verify stock_movements created
  - Verify CUMP calculated correctly
- [ ] Test Case 2: Roll product entry
  - Create entry with 3 rolls of same product
  - Validate → verify 3 EAN-13 codes generated
  - Verify 3 roll records created
  - Verify roll_ids stored in bon_entree_items
- [ ] Test Case 3: Multiple products in one entry
  - Mix of rolls and non-rolls
  - Verify each handled correctly
- [ ] Test Case 4: CUMP calculation edge cases
  - First entry (no previous stock)
  - Second entry (with existing stock)
  - Different unit prices
- [ ] Test Case 5: Validation errors
  - Invalid quantity (0 or negative)
  - Invalid price
  - Inactive product
  - Verify rollback on error

### 3.6 UI/UX Polish 🎨
- [ ] Add breadcrumbs navigation
- [ ] Add success notifications
- [ ] Add error handling with clear messages
- [ ] Add loading states during validation
- [ ] Add confirmation dialog before validation
- [ ] Display generated EAN-13 codes after validation
- [ ] Add print/PDF export for bon_entrees

**Estimated Time:** 3-4 days  
**Dependencies:** None (all tables exist)  
**Blocker Risk:** Low

---

## 📋 Slice 4 – Bon de Sortie Workflow (Issues to Production)

**Goal:** Issue materials from warehouse to production with CUMP-based valuation

### 4.1 Models & Logic
- [ ] Create BonSortie model (warehouse_id, destination, issue_date, purpose, status)
- [ ] Create BonSortieItem model (bon_sortie_id, product_id, quantity, cump_value, roll_ids)
- [ ] Add status transitions (pending → validated → completed)

### 4.2 Business Logic
- [ ] Stock availability check before issue
- [ ] Retrieve current CUMP from stock_quantities
- [ ] IF product.is_roll:
  - Select rolls WHERE status='in_stock' LIMIT quantity
  - Update roll.status = 'consumed'
  - Store consumed roll_ids in bon_sortie_items
- [ ] Create stock_movement (type=issue, qty_moved=-qty, reference=BON_SOR_XXX)
- [ ] Update stock_quantities (qty -= issued_qty)

### 4.3 Filament Resources
- [ ] BonSortieResource with form (warehouse, destination, purpose, items repeater)
- [ ] Table with filters (warehouse, date, status, destination)
- [ ] Validation action with stock check
- [ ] Error handling: insufficient stock alert

### 4.4 Testing
- [ ] Issue normal products (verify qty decreases)
- [ ] Issue rolls (verify roll status changes to consumed)
- [ ] Issue more than available stock (verify error)
- [ ] Issue from multiple warehouses (separate bons)

**Estimated Time:** 2-3 days  
**Dependencies:** Slice 3 complete (CUMP logic)

---

## 📋 Slice 5 – Bon de Transfert Workflow (Inter-Warehouse Transfers)

**Goal:** Move stock between warehouses while preserving CUMP

### 5.1 Models & Logic
- [ ] Create BonTransfert model (warehouse_from, warehouse_to, transfer_date, status)
- [ ] Create BonTransfertItem model (bon_transfert_id, product_id, quantity, cump_value, roll_ids)

### 5.2 Business Logic
- [ ] Check stock availability in source warehouse
- [ ] Retrieve CUMP from source warehouse stock_quantities
- [ ] IF product.is_roll:
  - Update roll.warehouse_id to destination warehouse
- [ ] Create 2 stock_movements:
  - Movement 1: type=transfer_out, warehouse_from, qty=-qty
  - Movement 2: type=transfer_in, warehouse_to, qty=+qty, same CUMP
- [ ] Update stock_quantities:
  - Source warehouse: qty -= transferred_qty
  - Destination warehouse: qty += transferred_qty, preserve CUMP

### 5.3 Filament Resources
- [ ] BonTransfertResource with form (warehouse_from, warehouse_to, items)
- [ ] Validation: check source has sufficient stock
- [ ] Show transfer in-transit status (optional)

### 5.4 Testing
- [ ] Transfer normal products between warehouses
- [ ] Transfer rolls (verify warehouse_id updated)
- [ ] Verify CUMP preserved (not recalculated)
- [ ] Transfer more than available (verify error)

**Estimated Time:** 2-3 days  
**Dependencies:** Slice 3, 4 complete

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

## ⚠️ Known Issues / Blockers

None currently. Ready to begin migrations.

---

## 🚀 Next Steps (Immediate)

### Phase 2 Continuation:
1. ✅ Design new schema (DONE → DATABASE_REDESIGN.md)
2. ✅ Map procedures (DONE → PROCEDURE_MAPPING.md)
3. 🔄 Update documentation (CURRENT)
4. ⏳ Create migrations
5. ⏳ Create models + relationships
6. ⏳ Create Filament resources
7. ⏳ Implement BON_ENTREE workflow
8. ⏳ Test and validate
9. ⏳ Commit

### Post-Phase 2:
- **Slice 3:** BON_ENTREE workflow with full EAN-13 + CUMP implementation
- **Slice 4:** BON_SORTIE & BON_TRANSFERT workflows
- **Slice 5:** BON_REINTEGRATION + manual adjustments
- **Slice 6:** Low-stock alerts + dashboard
- **Slice 7:** Valuation + CSV export