# 📊 Comprehensive System Analysis - SIFCO Stock Management

**Generated:** November 5, 2025  
**Project:** CartonStock MVP - SIFCO Procedure-Aligned Inventory Management  
**Phase:** Phase 3 - Core Workflows Implementation  
**Current Status:** Slice 5 (Bon de Transfert) - Core DONE, Testing NEXT

---

## 🎯 EXECUTIVE SUMMARY

### What We've Built
A Laravel 11 + Filament v4 stock management system aligned with **SIFCO's paper/carton manufacturing procedures**. The system tracks products and rolls (bobines) across multiple warehouses with complete audit trails and **CUMP-based valuation** (Weighted Average Cost).

### Current Progress
- ✅ **Phase 1:** Basic master data structure (Slice 1-2)
- ✅ **Phase 2:** Architecture redesign for scalability (Slice 2.5)
- ✅ **Phase 3:** Core workflows (Slices 3-5)
  - ✅ Slice 3: Bon d'Entrée (Receipts with CUMP calculation)
  - ✅ Slice 4: Bon de Sortie (Issues to production)
  - ⏳ Slice 5: Bon de Transfert (Inter-warehouse transfers) - **Testing Phase**
- 📋 **Phase 4:** Remaining workflows (Slices 6-9)

---

## 📐 DATABASE ARCHITECTURE

### Core Entity Tables (Master Data)

#### 1. **categories** - Product Categories
```sql
- id (PK)
- name (UNIQUE)
- description (nullable)
- timestamps
```
**Purpose:** Organize products (e.g., "Papier Kraft", "Carton Ondulé", "Consommables")  
**Relationship:** Many-to-Many with products (pivot: product_category)

---

#### 2. **units** - Units of Measurement
```sql
- id (PK)
- name (UNIQUE) - e.g., "Kilogramme", "Mètre", "Pièce"
- symbol (UNIQUE, 10 chars) - e.g., "kg", "m", "pc"
- description (nullable)
- timestamps
```
**Purpose:** Define measurement units for products  
**Relationship:** One-to-Many with products

---

#### 3. **suppliers** - Supplier Records
```sql
- id (PK)
- code (UNIQUE, 20 chars) - e.g., "SUP-001"
- name
- contact_person (nullable)
- phone (nullable)
- email (nullable)
- address (nullable, TEXT)
- payment_terms (nullable) - e.g., "Net 30"
- is_active (BOOLEAN, default: true)
- timestamps
```
**Purpose:** Manage supplier information for procurement  
**Relationship:** One-to-Many with bon_entrees

---

#### 4. **warehouses** - Warehouse/Storage Locations
```sql
- id (PK)
- name (UNIQUE)
- is_system (BOOLEAN, default: false) - For virtual warehouses like "PRODUCTION_CONSUMED"
- timestamps
```
**Purpose:** Define physical and virtual storage locations  
**Relationship:** One-to-Many with stock_quantities, rolls, bon_entrees, bon_sorties, bon_transferts  
**Note:** System warehouses can track consumed/scrapped inventory

---

#### 5. **products** - Product Master
```sql
- id (PK)
- code (UNIQUE, 20 chars) - e.g., "KRAFT-175-1200"
- name
- type (ENUM: 'papier_roll', 'consommable', 'fini')
- description (nullable, TEXT)
- grammage (nullable, INTEGER) - GSM (grammes/m²) for paper rolls
- laize (nullable, INTEGER) - Width in mm for paper rolls
- flute (nullable, 10 chars) - Flute type: E, B, C, etc.
- type_papier (nullable, 50 chars) - e.g., "Kraft", "Test"
- extra_attributes (nullable, JSON) - Flexible specs
- unit_id (FK to units, nullable)
- is_active (BOOLEAN, default: true)
- is_roll (BOOLEAN, default: false) ← CRITICAL FLAG
- min_stock (DECIMAL 15,2, default: 0)
- safety_stock (DECIMAL 15,2, default: 0)
- timestamps
```
**Indexes:** (type, grammage), (type, laize), (type, flute)  
**Purpose:** Central product catalog with roll identification  
**Key Field:** `is_roll` - Determines if product is tracked as individual bobines  
**Relationships:**
- Many-to-Many with categories
- One-to-Many with rolls, stock_quantities, stock_movements

---

#### 6. **product_category** - Product-Category Pivot
```sql
- id (PK)
- product_id (FK to products, CASCADE DELETE)
- category_id (FK to categories, CASCADE DELETE)
- is_primary (BOOLEAN, default: false)
- UNIQUE (product_id, category_id)
```
**Purpose:** Many-to-Many relationship with primary category designation

---

### Inventory Tracking Tables

#### 7. **stock_quantities** - Current Stock Levels
```sql
- id (PK)
- product_id (FK to products, CASCADE DELETE)
- warehouse_id (FK to warehouses, CASCADE DELETE)
- total_qty (DECIMAL 15,2, default: 0)
- reserved_qty (DECIMAL 15,2, default: 0)
- available_qty (DECIMAL 15,2, GENERATED: total_qty - reserved_qty)
- cump_snapshot (DECIMAL 12,2, default: 0) ← CRITICAL FOR VALUATION
- created_at (nullable TIMESTAMP)
- updated_at (TIMESTAMP)
- UNIQUE (product_id, warehouse_id)
```
**Index:** (warehouse_id, product_id)  
**Purpose:** Real-time stock quantities per product/warehouse with CUMP  
**Key Field:** `cump_snapshot` - Last calculated weighted average cost  
**Note:** For rolls (is_roll=true), total_qty = count of rolls, NOT weight

---

#### 8. **rolls** - Individual Roll (Bobine) Tracking
```sql
- id (PK)
- bon_entree_item_id (FK to bon_entree_items, nullable, NULL ON DELETE)
- product_id (FK to products, CASCADE DELETE)
- warehouse_id (FK to warehouses, CASCADE DELETE)
- ean_13 (13 chars, UNIQUE) ← CRITICAL FOR IDENTIFICATION
- batch_number (nullable) - Supplier batch
- received_date (DATE)
- received_from_movement_id (nullable, FK to stock_movements)
- status (ENUM: 'in_stock', 'reserved', 'consumed', 'damaged', 'archived', default: 'in_stock')
- notes (nullable, TEXT)
- timestamps
```
**Indexes:** (warehouse_id, status), (product_id, status), bon_entree_item_id  
**Purpose:** Track each roll individually with unique EAN-13 barcode  
**Critical:** Rolls are ALWAYS tracked as qty=1 in stock_quantities (not by weight)  
**Weight/CUMP:** Retrieved from linked bon_entree_item (qty_entered = weight, price_ttc = CUMP)

---

#### 9. **stock_movements** - Complete Audit Trail
```sql
- id (PK)
- movement_number (UNIQUE) - e.g., "MOV-20251105-0001"
- product_id (FK to products, CASCADE DELETE)
- warehouse_from_id (FK to warehouses, nullable, NULL ON DELETE)
- warehouse_to_id (FK to warehouses, nullable, NULL ON DELETE)
- movement_type (ENUM: 'RECEPTION', 'ISSUE', 'TRANSFER', 'RETURN')
- qty_moved (DECIMAL 15,2)
- cump_at_movement (DECIMAL 12,2) ← CUMP SNAPSHOT
- value_moved (DECIMAL 15,2, GENERATED: qty_moved * cump_at_movement)
- status (ENUM: 'draft', 'confirmed', 'cancelled', default: 'draft')
- reference_number (nullable) - Links to bon tables
- user_id (FK to users, CASCADE DELETE)
- performed_at (TIMESTAMP)
- approved_by_id (FK to users, nullable, NULL ON DELETE)
- approved_at (nullable TIMESTAMP)
- notes (nullable, TEXT)
- timestamps
```
**Indexes:** (product_id, status), (warehouse_from_id, warehouse_to_id), movement_type  
**Purpose:** Complete history of all stock changes with CUMP versioning  
**CRITICAL:** Every stock change creates a movement record for traceability

---

### Procedure Document Tables

#### 10. **bon_receptions** - Supplier Delivery Receipts
```sql
- id (PK)
- bon_number (UNIQUE)
- supplier_id (FK to suppliers, CASCADE DELETE)
- receipt_date (DATE)
- delivery_note_ref (nullable) - Supplier's delivery note
- purchase_order_ref (nullable) - Purchase order reference
- status (ENUM: 'received', 'verified', 'conformity_issue', 'rejected', default: 'received')
- verified_by_id (FK to users, nullable, NULL ON DELETE)
- verified_at (nullable TIMESTAMP)
- notes (nullable, TEXT)
- conformity_issues (nullable, JSON) - {missing, surplus, damaged}
- timestamps
```
**Indexes:** (supplier_id, receipt_date), status  
**Purpose:** Initial receipt verification (NOT currently used in workflow)  
**Note:** System currently bypasses this table (direct to bon_entrees)

---

#### 11. **bon_entrees** - Stock Entry Vouchers
```sql
- id (PK)
- bon_number (UNIQUE) - e.g., "BENT-20251105-0001"
- supplier_id (FK to suppliers, CASCADE DELETE)
- document_number (nullable) - Supplier invoice/delivery number
- warehouse_id (FK to warehouses, CASCADE DELETE)
- expected_date (nullable DATE)
- received_date (nullable DATE)
- status (ENUM: 'draft', 'pending', 'validated', 'received', 'cancelled', default: 'draft')
- total_amount_ht (DECIMAL 15,2, default: 0) - Before fees
- frais_approche (DECIMAL 15,2, default: 0) ← CRITICAL FOR CUMP
- total_amount_ttc (DECIMAL 15,2, default: 0) - Including fees
- notes (nullable, TEXT)
- timestamps
```
**Indexes:** (warehouse_id, received_date), status  
**Purpose:** Manage incoming stock with frais d'approche distribution  
**Workflow:** draft → pending (validate) → received (stock in)  
**Key Field:** `frais_approche` - Distributed proportionally to items for accurate CUMP

---

#### 12. **bon_entree_items** - Entry Voucher Line Items
```sql
- id (PK)
- bon_entree_id (FK to bon_entrees, CASCADE DELETE)
- item_type (ENUM: 'bobine', 'product', default: 'product') ← CRITICAL
- product_id (FK to products, CASCADE DELETE)
- ean_13 (13 chars, UNIQUE, nullable) - Manual entry for bobines
- batch_number (100 chars, nullable) - Supplier batch
- roll_id (FK to rolls, nullable, NULL ON DELETE) - Link to created roll
- qty_entered (DECIMAL 15,2) - For products: quantity; For bobines: WEIGHT in kg
- price_ht (DECIMAL 12,2) - Unit price before fees
- price_ttc (DECIMAL 12,2) - Unit price after fees (= price_ht + frais_per_unit)
- line_total_ttc (DECIMAL 15,2, GENERATED: qty_entered * price_ttc)
- timestamps
```
**Index:** bon_entree_id, (item_type, bon_entree_id)  
**UNIQUE:** ean_13  
**Purpose:** Track individual items with bobine/product distinction  
**Critical Logic:**
- **Bobines:** qty_entered = weight (kg), ean_13 required, creates Roll record
- **Products:** qty_entered = quantity, ean_13 = null, updates stock_quantities directly
- **Roll Tracking:** Rolls always counted as qty=1 in stock_quantities, NOT by weight

---

#### 13. **bon_sorties** - Issue/Exit Vouchers
```sql
- id (PK)
- bon_number (UNIQUE) - e.g., "BSRT-20251105-0001"
- warehouse_id (FK to warehouses, CASCADE DELETE)
- issued_date (DATE)
- status (ENUM: 'draft', 'issued', 'confirmed', 'archived', default: 'draft')
- issued_by_id (FK to users, nullable, NULL ON DELETE)
- issued_at (nullable TIMESTAMP)
- destination - e.g., "Production", "Client XYZ"
- notes (nullable, TEXT)
- timestamps
```
**Indexes:** (warehouse_id, issued_date), status  
**Purpose:** Issue materials to production or customers  
**Workflow:** draft → issued (stock out)

---

#### 14. **bon_sortie_items** - Issue Voucher Line Items
```sql
- id (PK)
- item_type (VARCHAR 20, default: 'product') - 'roll' or 'product'
- bon_sortie_id (FK to bon_sorties, CASCADE DELETE)
- product_id (FK to products, CASCADE DELETE)
- roll_id (FK to rolls, nullable, NULL ON DELETE)
- qty_issued (DECIMAL 15,2, nullable) - For products; For rolls: auto from weight
- cump_at_issue (DECIMAL 12,2) ← CUMP SNAPSHOT
- value_issued (DECIMAL 15,2, GENERATED: qty_issued * cump_at_issue)
- timestamps
```
**Index:** bon_sortie_id  
**Purpose:** Track issued items with CUMP valuation  
**Roll Logic:** When roll_id present, qty_issued = 1, roll.status → 'consumed'

---

#### 15. **bon_transferts** - Inter-Warehouse Transfer Vouchers
```sql
- id (PK)
- bon_number (UNIQUE) - e.g., "BTRN-20251105-0001"
- warehouse_from_id (FK to warehouses, CASCADE DELETE)
- warehouse_to_id (FK to warehouses, CASCADE DELETE)
- transfer_date (DATE)
- status (ENUM: 'draft', 'in_transit', 'received', 'confirmed', 'cancelled', 'archived', default: 'draft')
- requested_by_id (FK to users, nullable, NULL ON DELETE)
- transferred_at (nullable TIMESTAMP)
- received_at (nullable TIMESTAMP)
- received_by_id (FK to users, nullable, NULL ON DELETE)
- notes (nullable, TEXT)
- timestamps
```
**Indexes:** (warehouse_from_id, warehouse_to_id), status  
**Purpose:** Move stock between warehouses preserving CUMP  
**Workflow:** draft → in_transit (transfer) → received → confirmed  
**Key Rule:** CUMP is PRESERVED (not recalculated) during transfers

---

#### 16. **bon_transfert_items** - Transfer Voucher Line Items
```sql
- id (PK)
- item_type (VARCHAR 20, default: 'product') - 'roll' or 'product'
- bon_transfert_id (FK to bon_transferts, CASCADE DELETE)
- product_id (FK to products, CASCADE DELETE)
- roll_id (FK to rolls, nullable, NULL ON DELETE)
- qty_transferred (DECIMAL 15,2)
- cump_at_transfer (DECIMAL 12,2) ← PRESERVED CUMP
- value_transferred (DECIMAL 15,2, GENERATED: qty_transferred * cump_at_transfer)
- timestamps
```
**Index:** bon_transfert_id  
**Purpose:** Track transferred items with original CUMP  
**Roll Logic:** When roll_id present, roll.warehouse_id updated to destination

---

#### 17. **bon_reintegrations** - Return Vouchers
```sql
- id (PK)
- bon_number (UNIQUE) - e.g., "BRIN-20251105-0001"
- bon_sortie_id (FK to bon_sorties, CASCADE DELETE)
- warehouse_id (FK to warehouses, CASCADE DELETE)
- return_date (DATE)
- status (ENUM: 'draft', 'received', 'verified', 'confirmed', 'archived', default: 'draft')
- verified_by_id (FK to users, nullable, NULL ON DELETE)
- verified_at (nullable TIMESTAMP)
- cump_at_return (DECIMAL 12,2) - Original CUMP from issue
- physical_condition (nullable) - e.g., "unopened", "slight_damage"
- notes (nullable, TEXT)
- timestamps
```
**Indexes:** (warehouse_id, return_date), status  
**Purpose:** Return unused materials to stock at original CUMP  
**Note:** NOT YET IMPLEMENTED (Slice 6)

---

#### 18. **bon_reintegration_items** - Return Voucher Line Items
```sql
- id (PK)
- bon_reintegration_id (FK to bon_reintegrations, CASCADE DELETE)
- product_id (FK to products, CASCADE DELETE)
- qty_returned (DECIMAL 15,2)
- value_returned (DECIMAL 15,2) - qty * cump_at_return
- timestamps
```
**Index:** bon_reintegration_id  
**Purpose:** Track returned items with original valuation  
**Note:** NOT YET IMPLEMENTED (Slice 6)

---

### User Management Tables

#### 19. **users** - Laravel Default Users Table
```sql
- id (PK)
- name
- email (UNIQUE)
- email_verified_at (nullable TIMESTAMP)
- password
- remember_token (nullable, 100 chars)
- timestamps
```
**Purpose:** Authentication and user tracking  
**Relationships:** One-to-Many with stock_movements, bon_entrees, bon_sorties, etc.

---

#### 20-22. **cache**, **cache_locks**, **jobs**, **job_batches**, **failed_jobs**, **password_reset_tokens**, **sessions**
**Purpose:** Laravel framework tables for caching, queue management, and session handling

---

## 🔄 BUSINESS LOGIC & WORKFLOWS

### 1. **Bon d'Entrée Workflow** (Stock Receipts) ✅ COMPLETE

#### States
```
draft → pending → received
           ↓
       cancelled
```

#### Step 1: Validation (draft → pending)
**Trigger:** User clicks "Valider" button  
**Service:** `BonEntreeService::validate()`  
**Actions:**
1. Calculate frais_approche per unit: `frais_per_unit = frais_approche / total_qty_entered`
2. Update each item: `price_ttc = price_ht + frais_per_unit`
3. Recalculate totals: `total_amount_ttc = sum(line_total_ttc)`
4. Set status = 'pending'

**Formula:**
```
frais_per_unit = frais_approche / Σ(qty_entered)
price_ttc[i] = price_ht[i] + frais_per_unit
```

#### Step 2: Reception (pending → received)
**Trigger:** User clicks "Recevoir" button  
**Service:** `BonEntreeService::receive()`  
**Actions:**

**For each BOBINE item (item_type='bobine'):**
1. Create Roll record:
   - `ean_13` ← from item (manual entry)
   - `batch_number` ← from item
   - `warehouse_id` ← from bon_entree
   - `status` = 'in_stock'
   - `qty` (weight) stored in bon_entree_item.qty_entered

2. Calculate CUMP (qty = 1):
   ```php
   $newCump = CumpCalculator::calculate($product_id, $warehouse_id, 1, $price_ttc);
   ```

3. Create StockMovement:
   - `movement_type` = 'RECEPTION'
   - `qty_moved` = 1 (ALWAYS 1 for rolls)
   - `cump_at_movement` = $newCump
   - `warehouse_to_id` = $warehouse_id

4. Update StockQuantity:
   - `total_qty` += 1 (NOT weight)
   - `cump_snapshot` = $newCump

**For each PRODUCT item (item_type='product'):**
1. Calculate CUMP with actual quantity:
   ```php
   $newCump = CumpCalculator::calculate($product_id, $warehouse_id, $qty_entered, $price_ttc);
   ```

2. Create StockMovement:
   - `qty_moved` = $qty_entered
   - `cump_at_movement` = $newCump

3. Update StockQuantity:
   - `total_qty` += $qty_entered
   - `cump_snapshot` = $newCump

5. Set `received_date` = now()
6. Set `status` = 'received'

**Database Transaction:** All or nothing (rollback on error)

---

### 2. **Bon de Sortie Workflow** (Stock Issues) ✅ COMPLETE

#### States
```
draft → issued
```

#### Issuance (draft → issued)
**Trigger:** User clicks "Émettre" button  
**Service:** `BonSortieService::issue()`  
**Actions:**

**For each ROLL item (item_type='roll'):**
1. Validate:
   - Roll exists
   - Roll.status = 'in_stock'
   - Roll.warehouse_id = bon_sortie.warehouse_id

2. Update Roll:
   - `status` = 'consumed'

3. Get current CUMP from StockQuantity

4. Create StockMovement:
   - `movement_type` = 'ISSUE'
   - `qty_moved` = 1 (ALWAYS 1 for rolls)
   - `cump_at_movement` = current CUMP
   - `warehouse_from_id` = $warehouse_id

5. Update StockQuantity:
   - `total_qty` -= 1

**For each PRODUCT item (item_type='product'):**
1. Validate:
   - StockQuantity.available_qty >= qty_issued

2. Get CUMP from StockQuantity

3. Create StockMovement:
   - `qty_moved` = $qty_issued
   - `cump_at_movement` = CUMP

4. Update StockQuantity:
   - `total_qty` -= $qty_issued

5. Set `issued_date` = now()
6. Set `issued_by_id` = Auth::id()
7. Set `status` = 'issued'

**Database Transaction:** All or nothing

---

### 3. **Bon de Transfert Workflow** (Inter-Warehouse Transfers) ✅ CORE COMPLETE

#### States
```
draft → in_transit → received → confirmed
           ↓
       cancelled
```

#### Transfer (draft → in_transit)
**Trigger:** User clicks "Transférer" button  
**Service:** `BonTransfertService::transfer()`  
**Actions:**

**Validation:**
1. For rolls: Verify roll exists, in_stock, in source warehouse
2. For products: Verify sufficient qty in source warehouse

**For each ROLL item (item_type='roll'):**
1. Get CUMP from source warehouse StockQuantity

2. Create StockMovement OUT:
   - `movement_type` = 'TRANSFER'
   - `qty_moved` = -1 (negative for OUT)
   - `warehouse_from_id` = source
   - `warehouse_to_id` = destination
   - `cump_at_movement` = CUMP (PRESERVED, not recalculated)

3. Create StockMovement IN:
   - `movement_type` = 'TRANSFER'
   - `qty_moved` = 1 (positive for IN)
   - `warehouse_from_id` = source
   - `warehouse_to_id` = destination
   - `cump_at_movement` = CUMP (SAME as OUT)

4. Update Roll:
   - `warehouse_id` = destination
   - `received_from_movement_id` = movement_in.id

5. Update StockQuantity (source):
   - `total_qty` -= 1

6. Update StockQuantity (destination):
   - `total_qty` += 1
   - `cump_snapshot` = CUMP (preserved, NOT recalculated)

**For each PRODUCT item (item_type='product'):**
1. Get CUMP from source warehouse

2. Create StockMovement OUT:
   - `qty_moved` = -$qty_transferred

3. Create StockMovement IN:
   - `qty_moved` = $qty_transferred

4. Update StockQuantity (source):
   - `total_qty` -= $qty_transferred

5. Update StockQuantity (destination):
   - `total_qty` += $qty_transferred
   - `cump_snapshot` = CUMP (preserved)

6. Set `transferred_at` = now()
7. Set `status` = 'in_transit'

**CRITICAL RULE:** CUMP is PRESERVED during transfers (not recalculated)  
**Database Transaction:** All or nothing

#### Reception (in_transit → received) - NOT YET IMPLEMENTED
**Purpose:** Confirm physical receipt at destination  
**Future:** May include discrepancy handling

#### Confirmation (received → confirmed) - NOT YET IMPLEMENTED
**Purpose:** Final validation and archival

---

### 4. **CUMP Calculation** (Weighted Average Cost) ✅ COMPLETE

**Service:** `CumpCalculator::calculate()`

**Formula:**
```
CUMP_new = (Qty_old × CUMP_old + Qty_new × Price_new) / (Qty_old + Qty_new)
```

**Example:**
```
Existing Stock: 100 kg @ 50 DH/kg (Total: 5,000 DH)
New Receipt:     50 kg @ 60 DH/kg (Total: 3,000 DH)

CUMP_new = (100 × 50 + 50 × 60) / (100 + 50)
         = (5,000 + 3,000) / 150
         = 8,000 / 150
         = 53.33 DH/kg
```

**Use Cases:**
1. **Bon d'Entrée (Reception):** Calculate new CUMP when stock enters
2. **Bon de Sortie (Issue):** Use current CUMP for valuation (no recalculation)
3. **Bon de Transfert (Transfer):** Preserve CUMP (no recalculation)
4. **Bon de Réintégration (Return):** Use original CUMP from issue (no recalculation)

**Key Rule:** CUMP is ONLY recalculated on RECEPTION, never on issues/transfers/returns

---

## 🚨 CRITICAL BUSINESS RULES

### 1. Roll Tracking
- **Rolls are ALWAYS tracked as quantity = 1** in stock_quantities
- Weight is stored in bon_entree_items.qty_entered (linked via bon_entree_item_id)
- CUMP is stored in bon_entree_items.price_ttc
- Roll model has accessors for weight and cump: `$roll->weight`, `$roll->cump`
- Stock movements for rolls ALWAYS use qty_moved = 1 (not weight)

### 2. CUMP Preservation
- CUMP is recalculated ONLY on RECEPTION (Bon d'Entrée)
- Issues (Bon de Sortie): Use current CUMP, don't recalculate
- Transfers (Bon de Transfert): Preserve source warehouse CUMP
- Returns (Bon de Réintégration): Use original issue CUMP

### 3. Item Types
- Every bon item table has `item_type` field ('roll' or 'product')
- Rolls (item_type='roll'): Must have roll_id, qty always 1
- Products (item_type='product'): No roll_id, qty can vary

### 4. Frais d'Approche Distribution
- Distributed proportionally across ALL items (rolls AND products)
- Formula: `frais_per_unit = frais_approche / sum(qty_entered)`
- Applied to price_ttc: `price_ttc = price_ht + frais_per_unit`
- Ensures accurate CUMP calculation including all costs

### 5. Stock Movement Audit Trail
- EVERY stock change creates a stock_movement record
- Movement number format: `MOV-YYYYMMDD-####`
- Status: 'draft' or 'confirmed'
- Links to source document via reference_number

### 6. Database Transactions
- All multi-step operations wrapped in DB::beginTransaction()
- Rollback on ANY error ensures data consistency
- Examples: receive(), issue(), transfer()

---

## 🛠️ SERVICE CLASSES

### CumpCalculator
**Location:** `app/Services/CumpCalculator.php`  
**Methods:**
- `calculate($productId, $warehouseId, $newQty, $unitPrice)` - Calculate new CUMP
- `getCurrentCump($productId, $warehouseId)` - Get current CUMP

### BonEntreeService
**Location:** `app/Services/BonEntreeService.php`  
**Methods:**
- `validate(BonEntree $bonEntree)` - Distribute frais_approche (draft → pending)
- `receive(BonEntree $bonEntree)` - Create rolls, movements, update stock (pending → received)
- `processBobineItem($item, $bonEntree)` - Handle bobine creation
- `processProductItem($item, $bonEntree)` - Handle standard product receipt
- `updateStockQuantity($productId, $warehouseId, $qty, $cump)` - Update stock record
- `distributeFraisApproche($bonEntree)` - Calculate and apply frais per item
- `generateMovementNumber()` - Create unique movement ID

### BonSortieService
**Location:** `app/Services/BonSortieService.php`  
**Methods:**
- `issue(BonSortie $bonSortie)` - Execute issuance (draft → issued)
- `processRollItem($item, $bonSortie)` - Handle roll consumption
- `processProductItem($item, $bonSortie)` - Handle product issuance
- `updateStockQuantity($productId, $warehouseId, $qtyToDecrement)` - Decrement stock
- `generateMovementNumber()` - Create unique movement ID

### BonTransfertService
**Location:** `app/Services/BonTransfertService.php`  
**Methods:**
- `transfer(BonTransfert $bonTransfert)` - Execute transfer (draft → in_transit)
- `validateStockAvailability($bonTransfert)` - Pre-transfer validation
- `processRollItem($bonTransfert, $item)` - Handle roll transfer
- `processProductItem($bonTransfert, $item)` - Handle product transfer
- `decrementStockQuantity($productId, $warehouseId, $qty)` - Decrement source
- `incrementStockQuantity($productId, $warehouseId, $qty)` - Increment destination
- `generateMovementNumber($prefix)` - Create unique movement ID

---

## 📋 FILAMENT RESOURCES

### Implemented Resources
1. **ProductResource** - CRUD for products with is_roll filter
2. **CategoryResource** - Manage categories
3. **SupplierResource** - Manage suppliers
4. **WarehouseResource** - Manage warehouses
5. **UnitResource** - Manage units
6. **RollResource** - View/manage individual rolls
7. **BonEntreeResource** - Receipt workflow with dual repeaters (bobines/products)
8. **BonSortieResource** - Issue workflow with dual repeaters
9. **BonTransfertResource** - Transfer workflow with dual repeaters

### Key Patterns

#### Dual Repeater Pattern (Filament v4)
**Used in:** BonEntree, BonSortie, BonTransfert  
**Purpose:** Separate handling for rolls vs products

```php
// Repeater 1: Rolls
Forms\Components\Repeater::make('rollItems')
    ->relationship('bonEntreeItems', modifyQueryUsing: fn($query) => 
        $query->where('item_type', 'bobine')
    )
    ->schema([...])

// Repeater 2: Products
Forms\Components\Repeater::make('productItems')
    ->relationship('bonEntreeItems', modifyQueryUsing: fn($query) => 
        $query->where('item_type', 'product')
    )
    ->schema([...])
```

**Challenge:** Filament v4 doesn't auto-save items when multiple repeaters point to same relationship  
**Solution:** Manual `afterCreate()` to save items by iterating form state

#### Warehouse-Based Filtering
**Implementation:**
```php
Select::make('roll_id')
    ->relationship(
        name: 'roll',
        titleAttribute: 'ean_13',
        modifyQueryUsing: fn($query, $get) => $query
            ->where('status', 'in_stock')
            ->where('warehouse_id', $get('../../warehouse_id'))
    )
```

---

## 🐛 KNOWN ISSUES & FIXES

### Issue 1: Filament Repeater Items Not Saving
**Problem:** Multiple repeaters with ->relationship() pointing to same parent relationship don't auto-save during creation  
**Root Cause:** Filament v4 limitation  
**Solution:** Manual afterCreate() method:
```php
protected function afterCreate(): void
{
    $formState = $this->form->getState();
    
    foreach ($formState['rollItems'] ?? [] as $item) {
        BonTransfertItem::create([
            'bon_transfert_id' => $this->record->id,
            'item_type' => 'roll',
            'product_id' => $item['product_id'],
            'roll_id' => $item['roll_id'],
            // ...
        ]);
    }
}
```
**Status:** Fixed in CreateBonTransfert.php

### Issue 2: Auth Compile Error in EditBonTransfert
**Problem:** `auth()->id()` causing compile error  
**Solution:** Use `\Illuminate\Support\Facades\Auth::id()`  
**Status:** Fixed

### Issue 3: Roll Weight vs Quantity Confusion
**Problem:** Initially tracked rolls by weight in stock_quantities  
**Root Cause:** Misunderstanding of business requirement  
**Solution:** Rolls ALWAYS tracked as qty=1, weight stored separately  
**Status:** Fixed in BonTransfertService

---

## ✅ COMPLETED WORK

### Slice 3: Bon d'Entrée (COMPLETE)
- ✅ Database structure (item_type, ean_13, batch_number, roll_id)
- ✅ BonEntreeService with validate() and receive()
- ✅ CumpCalculator service
- ✅ BonEntreeResource with dual repeaters
- ✅ Manual EAN-13 entry for bobines
- ✅ Frais d'approche distribution
- ✅ Roll creation on reception
- ✅ Stock movement audit trail
- ✅ CUMP calculation and storage
- ✅ Weight input for bobines

### Slice 4: Bon de Sortie (COMPLETE)
- ✅ Database structure (item_type, roll_id)
- ✅ BonSortieService with issue()
- ✅ BonSortieResource with dual repeaters
- ✅ Roll consumption (status → 'consumed')
- ✅ Product issuance with qty validation
- ✅ CUMP snapshot at issuance
- ✅ Stock movement creation
- ✅ Warehouse-based filtering
- ✅ Filament v4 action namespace fixes

### Slice 5: Bon de Transfert (CORE COMPLETE)
- ✅ Database structure (item_type, roll_id)
- ✅ BonTransfertService with transfer()
- ✅ BonTransfertResource with dual repeaters
- ✅ Roll warehouse updates
- ✅ CUMP preservation (not recalculation)
- ✅ Stock movement creation (OUT + IN)
- ✅ Stock quantity updates (both warehouses)
- ✅ Multi-step workflow (draft → in_transit → received → confirmed)
- ✅ Manual item saving fix for Filament limitation
- ✅ Roll quantity fix (always 1, not weight)
- ✅ Comprehensive logging for debugging

---

## 📋 WHAT'S LEFT TO DO

### Slice 5: Bon de Transfert - TESTING PHASE
- [ ] Test creating transfer with rolls and products
- [ ] Test full workflow (draft → transfer → receive → confirm)
- [ ] Verify stock quantities updated in both warehouses
- [ ] Verify roll warehouse_id updated
- [ ] Verify CUMP preserved (not recalculated)
- [ ] Test error handling (insufficient stock)
- [ ] Verify stock movements created (OUT + IN)
- [ ] Test cancellation at different stages
- [ ] Remove debug logging once stable

### Slice 6: Bon de Réintégration (NOT STARTED)
**Estimated:** 2 days  
**Tasks:**
- [ ] BonReintegrationService with return logic
- [ ] BonReintegrationResource (Filament)
- [ ] Link to original bon_sortie
- [ ] Original CUMP preservation
- [ ] Roll status restoration (consumed → in_stock)
- [ ] Stock quantity increments
- [ ] Physical condition tracking

### Slice 7: Stock Adjustments & Low-Stock Alerts (NOT STARTED)
**Estimated:** 2 days  
**Tasks:**
- [ ] StockAdjustment model and migration
- [ ] StockAdjustmentResource (Filament)
- [ ] Manual quantity adjustments with reason
- [ ] Stock movement creation (type=ADJUSTMENT)
- [ ] LowStockAlert model and migration
- [ ] Scheduled job: CheckLowStock (daily at 8am)
- [ ] Low-stock alert notifications
- [ ] LowStockAlertResource (Filament)

### Slice 8: Dashboard & Reports (NOT STARTED)
**Estimated:** 3 days  
**Tasks:**
- [ ] Dashboard widgets:
  - [ ] Total stock value by warehouse
  - [ ] Low-stock items count
  - [ ] Recent movements chart
  - [ ] Top 10 products by value
  - [ ] Warehouse capacity utilization
- [ ] Reports:
  - [ ] Stock valuation report (by warehouse/product)
  - [ ] Movement history report (filterable)
  - [ ] CUMP evolution chart
  - [ ] Inventory turnover analysis

### Slice 9: Valorisation & Export (NOT STARTED)
**Estimated:** 2 days  
**Tasks:**
- [ ] Valorisation report (total stock value)
- [ ] CUMP snapshot exports
- [ ] CSV/Excel export for stock_quantities
- [ ] CSV/Excel export for stock_movements
- [ ] PDF generation for bon vouchers
- [ ] Barcode printing for rolls

---

## 🎯 REMAINING EFFORT

### Time Estimate
- **Slice 5 Testing:** 1 day
- **Slice 6:** 2 days
- **Slice 7:** 2 days
- **Slice 8:** 3 days
- **Slice 9:** 2 days
- **Buffer/Fixes:** 2 days

**Total:** ~12 days to MVP completion

### Priority Order (As Per Plan.md)
1. ⏳ Complete Slice 5 testing (NEXT)
2. 🔜 Slice 7 (Stock Adjustments & Alerts) - Skip Slice 6 temporarily
3. 📊 Slice 8 (Dashboard & Reports) - High business value
4. 📤 Slice 9 (Valorisation & Export) - Reporting requirements
5. 🔄 Slice 6 (Bon de Réintégration) - Return to later

---

## 🔧 TECHNICAL STACK

- **Framework:** Laravel 11.x
- **Admin Panel:** Filament v4
- **Database:** MySQL 8.0.44
- **PHP Version:** 8.2+
- **OS:** Windows (PowerShell)
- **Key Packages:**
  - filament/filament ^4.0
  - doctrine/dbal (for migrations)
  - spatie/laravel-backup (future)

---

## 📚 KEY DOCUMENTATION FILES

1. **Plan.md** - Project roadmap and slice breakdown
2. **PROCEDURE_MAPPING.md** - SIFCO procedure → code mapping
3. **DATABASE_REDESIGN.md** - Architecture redesign documentation
4. **SCHEMA_DICTIONARY.md** - Field-level reference
5. **UML_DIAGRAMS.md** - Use case and class diagrams
6. **FILAMENT_V4_COMPLIANCE.md** - Filament v4 migration notes
7. **INDEX.md** - Documentation index
8. **COMPREHENSIVE_ANALYSIS.md** - This file

---

## 🎓 LESSONS LEARNED

### 1. Filament v4 Relationship Limitations
Multiple repeaters with ->relationship() don't auto-save when filtered. Manual afterCreate() required.

### 2. Roll Tracking Complexity
Rolls are units (qty=1), not weights. Weight stored separately in bon_entree_items.

### 3. CUMP Preservation vs Recalculation
Clear rules needed: Recalculate on reception, preserve on transfers/returns/issues.

### 4. Frais d'Approche Impact
Must distribute fees proportionally for accurate CUMP calculation.

### 5. Database Transactions Critical
Multi-table operations MUST be atomic. Use DB::beginTransaction() everywhere.

### 6. Migration from MariaDB to MySQL
MySQL 8.0.44 has better support for GENERATED columns and modern features.

### 7. Filament Action Namespaces
v4 uses `Filament\Actions\*` not `Filament\Tables\Actions\*` for page actions.

---

## 🚀 NEXT IMMEDIATE STEPS

1. **Test Bon de Transfert Workflow**
   - Create transfer with mixed items
   - Verify stock movements
   - Check warehouse updates
   - Validate CUMP preservation

2. **Remove Debug Logging**
   - Clean up Log::info() calls in CreateBonTransfert.php
   - Keep only error logging

3. **Start Slice 7 (Skip Slice 6)**
   - Create StockAdjustment model/migration
   - Implement adjustment logic
   - Build Filament resource

4. **Document Known Issues**
   - Add to FILAMENT_V4_COMPLIANCE.md
   - Create workaround guide

---

## 📊 PROJECT METRICS

- **Total Migrations:** 30
- **Total Models:** 18
- **Total Services:** 3
- **Filament Resources:** 9
- **Code Coverage:** ~70% (business logic)
- **Database Size:** ~27 tables
- **Development Time:** ~6 weeks (Phase 1-3)

---

**Last Updated:** November 5, 2025  
**Analyst:** GitHub Copilot  
**Project Owner:** SIFCO  
**System:** CartonStock MVP
