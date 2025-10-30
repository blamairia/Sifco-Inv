# 🔄 DATABASE REDESIGN – Scalability & Procedure Alignment

**Status:** PROPOSAL  
**Version:** 1.0  
**Date:** 2025-10-30

---

## 📋 Executive Summary

The current schema is **overcomplicated** and **mixes concerns**:

- ❌ `Product` tries to handle attributes (gsm, flute, width) AND inventory tracking
- ❌ `PaperRollType` has no real purpose beyond attributes
- ❌ Product↔Category/Subcategory relationships unclear and inflexible
- ❌ No `stock_movements` table → Can't track CUMP history, movement origins, or audit trail
- ❌ Roll quantity always = 1, but conceptually unclear
- ❌ Receipt workflow not aligned with SIFCO procedure (Bon d'entrée, Bon de réception, etc.)
- ❌ No support for Bon de sortie, Bon de transfert, Bon de réintégration
- ❌ CUMP not versioned or tracked with movements

**Solution:** **Simplified, flat, procedure-aligned schema with clear separation of concerns**

---

## ✅ New Design Principles

1. **Product = Master data only** (name, description, physical properties)
2. **Category/Subcategory = Flexible tagging** (many-to-many, removed from Product)
3. **Supplier = Separate entity** (not tied to PaperRollType or RollSpecification)
4. **Stock Quantity = warehouse + product join table** (scalable, no duplicates)
5. **Roll = Physical individual inventory** (with unique EAN-13, batch, status)
6. **stock_movements = Audit trail** (every entry, issue, transfer, return with CUMP snapshot)
7. **Procedure documents = Explicit tables** (bon_receptions, bon_entrees, bon_sorties, bon_transferts, bon_reintegrations)

---

## 📊 New Table Structure (Simplified)

### Core Master Data

#### `products` (Master Catalog)
```
id (PK)
code (UNIQUE) ← Add SKU/reference code
name (VARCHAR)
type (ENUM: papier_roll, consommable, fini)
description (TEXT nullable)
physical_attributes (JSON) ← {gsm, flute, width, length, etc.} for flexibility
unit_id (FK) ← "pcs", "roll", "kg", "m"
is_active (BOOLEAN)
created_at, updated_at
```

**Why JSON for physical_attributes?**
- Different product types have different attributes
- No need for nullable columns in schema
- Easy to add new attributes without migration
- Example: `{gsm: 80, flute: "C", width: 210}`

#### `categories`
```
id (PK)
name (UNIQUE)
description (TEXT nullable)
created_at, updated_at
```

#### `product_category` (Many-to-Many)
```
id (PK)
product_id (FK)
category_id (FK)
is_primary (BOOLEAN) ← Primary category for quick access
UNIQUE(product_id, category_id)
```

**Why M:M?**
- Product can belong to multiple categories
- Avoid subcategories table (flattens hierarchy)
- More flexible for future reorganization

#### `suppliers`
```
id (PK)
code (UNIQUE, VARCHAR 20) ← Supplier reference
name (VARCHAR)
contact_person (VARCHAR nullable)
phone (VARCHAR nullable)
email (VARCHAR nullable)
address (TEXT nullable)
payment_terms (VARCHAR nullable) ← e.g., "Net 30"
is_active (BOOLEAN)
created_at, updated_at
```

#### `units`
```
id (PK)
name (UNIQUE) ← "Piece", "Roll", "Kilogram"
symbol (UNIQUE) ← "pcs", "roll", "kg"
description (TEXT nullable)
created_at, updated_at
```

---

### Inventory Tables

#### `stock_quantities` ← **NEW: Replaces stock_levels**
```
id (PK)
product_id (FK)
warehouse_id (FK)
total_qty (DECIMAL 15,2) ← Total quantity on hand
reserved_qty (DECIMAL 15,2) ← Reserved (for future use in Slice 5+)
available_qty (DECIMAL 15,2) ← total_qty - reserved_qty
cump_snapshot (DECIMAL 12,2) ← Last known CUMP at this warehouse/product
last_movement_id (FK nullable) ← Link to last stock_movement for traceability
updated_at (TIMESTAMP)
UNIQUE(product_id, warehouse_id)
```

**Why rename to stock_quantities?**
- Clearer that it's aggregated quantity
- `stock_levels` sounds like threshold levels
- Allows for different scalability patterns

---

#### `rolls` (Individual Physical Inventory)
```
id (PK)
product_id (FK)
warehouse_id (FK)
ean_13 (VARCHAR UNIQUE) ← Physical barcode
batch_number (VARCHAR nullable) ← Supplier batch
received_date (DATE)
received_from_movement_id (FK) ← Link to stock_movement that created this roll
status (ENUM: in_stock, reserved, consumed, damaged, archived)
notes (TEXT nullable)
created_at, updated_at
```

**Note:** Quantity is implicit = 1 per roll (never shown in schema)

---

### Stock Movement Tracking (Audit Trail)

#### `stock_movements` ← **CRITICAL NEW TABLE**
```
id (PK)
movement_number (UNIQUE VARCHAR) ← "BON-MOV-2025-0001"
product_id (FK)
warehouse_from_id (FK nullable) ← NULL for receipts
warehouse_to_id (FK nullable) ← NULL for issues to PRODUCTION_CONSUMED
movement_type (ENUM: 
  - RECEPTION (Bon d'entrée: supplier → warehouse)
  - ISSUE (Bon de sortie: warehouse → PRODUCTION_CONSUMED)
  - TRANSFER (Bon de transfert: warehouse → warehouse)
  - RETURN (Bon de réintégration: warehouse → warehouse + value adjustment)
)
qty_moved (DECIMAL 15,2)
cump_at_movement (DECIMAL 12,2) ← Snapshot of CUMP at time of movement
value_moved (DECIMAL 15,2) ← qty * cump_at_movement
status (ENUM: draft, confirmed, cancelled)
reference_number (VARCHAR nullable) ← Links to bon_receptions, bon_entrees, etc.
user_id (FK) ← Who performed the movement
performed_at (TIMESTAMP)
approved_by_id (FK nullable) ← Manager approval
approved_at (TIMESTAMP nullable)
notes (TEXT nullable)
created_at, updated_at
```

**Why separate from receipts?**
- Receipts are about incoming documents (Bon de réception)
- Movements are about ledger entries (Bon d'entrée, Bon de sortie, etc.)
- Allows multiple movements per receipt
- Clear audit trail independent of workflow status

---

### Procedure Documents (Aligned with SIFCO Procedure)

#### `bon_receptions` (Supplier Delivery Note)
```
id (PK)
bon_number (UNIQUE VARCHAR) ← Bon de réception number
supplier_id (FK)
receipt_date (DATE)
delivery_note_ref (VARCHAR nullable) ← Bon de livraison fournisseur reference
purchase_order_ref (VARCHAR nullable) ← Copy of bon de commande
status (ENUM: received, verified, conformity_issue, rejected)
verified_by_id (FK nullable) ← Magasinier verification
verified_at (TIMESTAMP nullable)
notes (TEXT nullable)
conformity_issues (JSON nullable) ← {missing, surplus, damaged, other}
created_at, updated_at
```

#### `bon_entrees` (Stock Entry Note)
```
id (PK)
bon_number (UNIQUE VARCHAR) ← Bon d'entrée magasin number
bon_reception_id (FK) ← Links back to supplier delivery
warehouse_id (FK)
receipt_date (DATE)
status (ENUM: draft, entered, confirmed, archived)
entered_by_id (FK) ← Gestionnaire des stocks
entered_at (TIMESTAMP nullable)
total_amount_ttc (DECIMAL 15,2) ← Including all frais d'approche
total_amount_ht (DECIMAL 15,2) ← Before frais d'approche
frais_approche (DECIMAL 15,2) ← Transport, D3, transitaire fees
notes (TEXT nullable)
created_at, updated_at
```

#### `bon_entree_items` (Line items for Bon d'entrée)
```
id (PK)
bon_entree_id (FK)
product_id (FK)
qty_entered (DECIMAL 15,2)
price_ht (DECIMAL 12,2) ← Unit price before fees
price_ttc (DECIMAL 12,2) ← Unit price after fees distribution
line_total_ttc (DECIMAL 15,2)
created_at, updated_at
```

#### `bon_sorties` (Stock Issue Note)
```
id (PK)
bon_number (UNIQUE VARCHAR) ← Bon de sortie number
warehouse_id (FK)
issued_date (DATE)
status (ENUM: draft, issued, confirmed, archived)
issued_by_id (FK) ← Magasinier
issued_at (TIMESTAMP nullable)
destination (VARCHAR) ← e.g., "Production", "Client", department name
notes (TEXT nullable)
created_at, updated_at
```

#### `bon_sortie_items` (Line items for Bon de sortie)
```
id (PK)
bon_sortie_id (FK)
product_id (FK)
qty_issued (DECIMAL 15,2)
cump_at_issue (DECIMAL 12,2) ← Snapshot for valuation
value_issued (DECIMAL 15,2) ← qty * cump_at_issue
created_at, updated_at
```

#### `bon_transferts` (Warehouse Transfer Note)
```
id (PK)
bon_number (UNIQUE VARCHAR) ← Bon de transfert number
warehouse_from_id (FK)
warehouse_to_id (FK)
transfer_date (DATE)
status (ENUM: draft, in_transit, received, confirmed, archived)
requested_by_id (FK) ← Demandeur
transferred_at (TIMESTAMP nullable)
received_at (TIMESTAMP nullable)
received_by_id (FK nullable) ← Magasinier receiver
notes (TEXT nullable)
created_at, updated_at
```

#### `bon_transfert_items` (Line items for Bon de transfert)
```
id (PK)
bon_transfert_id (FK)
product_id (FK)
qty_transferred (DECIMAL 15,2)
cump_at_transfer (DECIMAL 12,2) ← Transfer at original cost
value_transferred (DECIMAL 15,2)
created_at, updated_at
```

#### `bon_reintegrations` (Return to Stock Note)
```
id (PK)
bon_number (UNIQUE VARCHAR) ← Bon de réintégration number
bon_sortie_id (FK) ← References original Bon de sortie
warehouse_id (FK) ← Where product goes back
return_date (DATE)
status (ENUM: draft, received, verified, confirmed, archived)
verified_by_id (FK nullable) ← Magasinier verification
verified_at (TIMESTAMP nullable)
cump_at_return (DECIMAL 12,2) ← CUMP from date of original issue
notes (TEXT nullable)
physical_condition (VARCHAR nullable) ← e.g., "unopened", "slight_damage"
created_at, updated_at
```

#### `bon_reintegration_items` (Line items for Bon de réintégration)
```
id (PK)
bon_reintegration_id (FK)
product_id (FK)
qty_returned (DECIMAL 15,2)
cump_at_return (DECIMAL 12,2)
value_returned (DECIMAL 15,2)
created_at, updated_at
```

---

### Adjustments & Alerts

#### `stock_adjustments` ← **NEW**
```
id (PK)
adjustment_number (UNIQUE VARCHAR)
product_id (FK)
warehouse_id (FK)
qty_adjustment (DECIMAL 15,2) ← Positive or negative
reason (ENUM: inventory_count, damage, loss, correction, other)
adjustment_date (DATE)
status (ENUM: draft, pending_approval, approved, archived)
created_by_id (FK)
approved_by_id (FK nullable)
approved_at (TIMESTAMP nullable)
notes (TEXT)
created_at, updated_at
```

#### `low_stock_alerts` ← **NEW**
```
id (PK)
alert_number (UNIQUE VARCHAR) ← Avis de rupture number
product_id (FK)
warehouse_id (FK nullable) ← If specific warehouse, else all
current_qty (DECIMAL 15,2)
min_stock (DECIMAL 15,2)
safety_stock (DECIMAL 15,2)
alert_type (ENUM: min_stock_reached, safety_stock_reached)
is_acknowledged (BOOLEAN)
acknowledged_by_id (FK nullable)
acknowledged_at (TIMESTAMP nullable)
reorder_requested (BOOLEAN)
reorder_qty (DECIMAL 15,2 nullable)
created_at, updated_at
```

---

## 🔄 Removed or Deprecated

| Old Table/Column | Status | Reason |
|------------------|--------|--------|
| `stock_levels` | REPLACE with `stock_quantities` | Rename for clarity |
| `subcategories` | REMOVE | Flatten into product_category M:M |
| `paper_roll_types` | KEEP but simplify | Use only if product-agnostic, else move to physical_attributes JSON |
| `roll_specifications` | SIMPLIFY | Just `product_id → supplier_id` pricing lookup |
| Receipt workflow (old) | REPLACE with Bon* tables | Explicit procedure alignment |
| Receipts without movements | NO LONGER ACCEPTABLE | Every receipt → stock_movements + bon_entrees |

---

## 🎯 Data Flow (SIFCO Procedure Aligned)

### Entrada (Reception)

```
1. Supplier delivery
   ↓
2. Magasinier checks delivery note → Creates BON_RECEPTION ✓
   - Compare bon de livraison ↔ bon de commande
   - Verify qty/quality
   - Sign bon de livraison
   ↓
3. Gestionnaire enters invoice/costs → Creates BON_ENTREE + BON_ENTREE_ITEMS ✓
   - Includes frais d'approche (transport, D3, fees)
   - Calculates unit price TTC
   - Triggers RECEPTION movement
   ↓
4. Stock movement created: RECEPTION
   - warehouse_from = NULL (from supplier)
   - warehouse_to = receiving warehouse
   - qty_moved = qty_entered
   - cump = (old_qty * old_cump + new_qty * new_price) / (old_qty + new_qty)
   - Updates stock_quantities
   ↓
5. Rolls generated (1 per unit or per batch)
   - Each roll created with unique EAN-13
   - Links back to stock_movement ID
   ↓
6. Physical placement
   - Magasinier moves products to storage zones
```

### Sorties (Issues)

```
1. Department requests via BPA (Bon d'approvisionnement) → BON_SORTIE ✓
   ↓
2. Magasinier verifies stock, prepares goods
   ↓
3. BON_SORTIE confirmed → Creates ISSUE movement
   - warehouse_from = issuing warehouse
   - warehouse_to = NULL (PRODUCTION_CONSUMED implicit)
   - qty = qty issued
   - cump_at_movement = current CUMP
   - value = qty × CUMP
   ↓
4. stock_quantities updated
   ↓
5. Rolls marked as consumed (status='consumed')
```

### Transferts (Inter-warehouse)

```
1. Request → BON_TRANSFERT created (draft)
   ↓
2. Magasinier prepares goods
   ↓
3. Transfer → TRANSFER movement created
   - warehouse_from = source
   - warehouse_to = destination
   - qty + CUMP preserved
   ↓
4. Stock decremented at source, incremented at destination
   ↓
5. Receiving warehouse confirms receipt
```

### Réintégration (Returns)

```
1. User returns goods with original BPA → BON_REINTEGRATION ✓
   ↓
2. Magasinier verifies item & physical condition
   ↓
3. Gestionnaire creates RETURN movement
   - Positive qty increase at warehouse
   - Uses CUMP from date of original ISSUE (stored in stock_movements)
   - Adjusts valuation accordingly
```

---

## 🔧 Migration Path (Minimal Downtime)

### Phase 1: Prepare New Tables (Non-Breaking)
1. Create `stock_quantities` (populate from stock_levels)
2. Create `stock_movements` (empty initially)
3. Create `bon_*` tables (empty)
4. Create `stock_adjustments`, `low_stock_alerts`

### Phase 2: Dual-Write (Transition)
- Queries read from both `stock_levels` and `stock_quantities` (with preference)
- New receipts write to both tables
- Old receipts continue to use `stock_levels`

### Phase 3: Cutover
- Stop writing to `stock_levels`
- Run final migration of any remaining data
- Verify all reports/queries use `stock_quantities`
- Archive `stock_levels` (keep for history)

### Phase 4: Cleanup
- Remove old procedure tables (old Receipt model if not used)
- Drop `stock_levels` (after backup)
- Drop `subcategories` (after data migration to M:M)

---

## 📈 Scalability Benefits

✅ **Per-Product Quantities:** Clear separation in `stock_quantities`  
✅ **Per-Warehouse Quantities:** Separate row per (product, warehouse)  
✅ **Audit Trail:** Complete `stock_movements` history  
✅ **CUMP Versioning:** Snapshot at each movement  
✅ **Flexible Categories:** M:M allows multi-tagging  
✅ **Procedure Alignment:** Explicit bon_* tables match SIFCO workflow  
✅ **Future Reservations:** Ready for `reserved_qty` in `stock_quantities`  
✅ **Multi-tenant Ready:** Easy to add `company_id` to all tables  

---

## 🚀 Next Steps

1. ✅ Review this design document
2. → Create migrations for new tables
3. → Populate `stock_quantities` from `stock_levels`
4. → Update models and add relationships
5. → Refactor Filament resources
6. → Implement procedures (BON_ENTREE workflow, etc.)
7. → Test end-to-end
8. → Commit & update PLAN.md
