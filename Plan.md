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
- [ ] **Slice 3: Bon d'Entrée Workflow** ← **CURRENT: Receipts with CUMP calculation**
- [ ] **Slice 4: Bon de Sortie & Bon de Transfert** (movements)
- [ ] **Slice 5: Bon de Réintégration** (returns with CUMP preservation)
- [ ] **Slice 6: Manual adjustments + Low-stock alerts**
- [ ] **Slice 7: Dashboard & Reports**
- [ ] **Slice 8: Valuation + CSV export**

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

### Next Steps
- Implement BON_ENTREE workflow with EAN-13 generation
- Implement CUMP calculation logic
- Create Filament resources for bon_entrees, bon_entree_items
- Test end-to-end receipt workflow

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