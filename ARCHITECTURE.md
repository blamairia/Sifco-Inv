# CartonStock - Architecture & Structural Clarification

## Current Structural Issue

The current design has **conflicting concepts**:

1. **Roll** = Individual physical roll with unique EAN-13 (qty=1 per roll)
2. **Product** with `paper_roll_type_id` = Product specification that can represent many rolls

**Problem:** 
- A Product can be "Papier KRAFT 120 GSM" (with paper_roll_type_id pointing to "KRAFT KL - 120 GSM, 1200 laise, 500kg")
- But we also store individual Rolls linked to Products
- When receiving rolls, we don't know which specific roll attributes (grammage, laise) they have
- Stock aggregation becomes confusing: Do we group by Product or by Paper Roll Type attributes?

---

## Proposed Solution: "Roll Specification" Model

### Core Concept: Separate Specification from Inventory

**Three-tier hierarchy:**

```
PaperRollType (Attributes)
    ↓ defines
RollSpecification (Unique combinations for a product)
    ↓ contains many
Roll (Individual physical roll - qty=1 always)
```

---

## New Data Model

### 1. **PaperRollType** (Already exists - no change)
```
KL:     type_code='KL',   name='KRAFT KL',      grammage=120, laise=1200, weight=500
TLB:    type_code='TLB',  name='TELE BLANC TLB', grammage=80,  laise=1000, weight=400
TLM:    type_code='TLM',  name='TELE MARGE TLM', grammage=100, laise=800,  weight=380
FL:     type_code='FL',   name='FEUILLE FL',    grammage=60,  laise=600,  weight=250
```

### 2. **NEW: RollSpecification** Table
Purpose: Define the exact combination of Product + Paper Roll Type attributes for receiving/tracking

Columns:
```
id (PK)
product_id (FK) - must be type='papier_roll'
paper_roll_type_id (FK) - the exact type/attributes this spec represents
supplier_id (FK optional) - default supplier for this roll type
purchase_price (decimal 12,2) - cost when receiving this roll type
description (text)
is_active (boolean)
created_at, updated_at
```

**Business Logic:**
- Only creatable for products with `type='papier_roll'`
- Each specification uniquely identifies a roll configuration
- Example: Product "Papier KRAFT 120 GSM" can have multiple specs:
  - RollSpec#1: Paper KRAFT KL (120, 1200, 500kg) @ 450 DA/roll
  - RollSpec#2: Paper KRAFT KL (120, 1000, 400kg) @ 400 DA/roll (different laise)

### 3. **UPDATED: Roll** Table
Purpose: Track individual physical rolls with unique EAN-13

**Current Structure (KEEP):**
```
id (PK)
product_id (FK)
warehouse_id (FK)
ean_13 (unique string 13) - INDIVIDUAL roll barcode
qty (decimal 15,2) - always 1.0 for individual rolls, or multiple copies if same spec
status (enum: in_stock, consumed)
created_at, updated_at
```

**ADD:**
```
roll_specification_id (FK) - NEW: Link to exact specification received
batch_number (string nullable) - if rolls come in batches
received_date (date nullable) - when this roll was received
```

### 4. **NEW: ReceiptItem** Table
Purpose: Line item for receipts - connects received quantity to roll specifications

Columns:
```
id (PK)
receipt_id (FK) - parent receipt
roll_specification_id (FK) - what was received
qty_received (integer) - number of rolls of this specification
total_price (decimal 12,2) - qty * unit price
notes (text)
created_at, updated_at
```

### 5. **NEW: Receipt** Table
Purpose: Master record for stock in operations

Columns:
```
id (PK)
receipt_number (string unique) - auto-generated
supplier_id (FK)
warehouse_id (FK)
receipt_date (date)
total_amount (decimal 12,2)
status (enum: draft, received, verified)
notes (text)
created_at, updated_at
```

---

## Receipt Workflow (Slice 3)

### Step 1: Create Receipt
- Select supplier
- Select warehouse (default to ENTREPOT_PAPIER for paper rolls)
- Auto-generate receipt_number

### Step 2: Add Receipt Items (in same resource)
- **Option A: Quick Select**
  - Select Product (type filter to 'papier_roll')
  - Automatically show available RollSpecifications for that product:
    ```
    ✓ KRAFT KL (120 GSM, 1200 laise) @ 450 DA  [Select]
    ✓ KRAFT KL (120 GSM, 1000 laise) @ 400 DA  [Select]
    ✓ KRAFT KL (120 GSM, 1500 laise) @ 500 DA  [Select]
    ```
  - Click specification → highlighted
  - Enter qty of rolls to receive
  - Add to receipt

### Step 3: Generate Rolls
When receipt is marked **"received"**:
- For each ReceiptItem with qty=5:
  - Create 5 individual Roll records
  - Auto-generate unique EAN-13 codes
  - Link each to the RollSpecification
  - Set status='in_stock'
  - Add to correct warehouse

### Step 4: Update Stock & Cost
- Sum all rolls by product → update StockLevel qty
- Recalculate weighted average cost for the product

---

## UI/UX for Receipt Resource

### Single Resource: **ReceiptResource**

#### List View
```
Receipt #RCP-2025-001 | Supplier: Papiers Import | Status: Received | 12,500 DA | Oct 29
Receipt #RCP-2025-002 | Supplier: Fournisseur ABC | Status: Draft   | 8,250 DA  | Oct 29
```

#### Create/Edit View
```
┌─ Receipt Details ───────────────────────────────┐
│ Receipt #: RCP-2025-003 (auto-generated)        │
│ Supplier: [Select ▼] Papiers Import             │
│ Warehouse: [Select ▼] ENTREPOT_PAPIER           │
│ Receipt Date: [Date Picker] 2025-10-29          │
│ Status: Draft / Received / Verified             │
│ Notes: [Large text]                             │
└─────────────────────────────────────────────────┘

┌─ Line Items (Repeater) ─────────────────────────┐
│ [+ Add Item]                                    │
│                                                 │
│ Item #1:                                        │
│ Product: [Select ▼] Papier KRAFT 120 GSM       │
│ Specification: [Radio Options - highlight]      │
│   ○ KRAFT KL (120 GSM, 1200 laise) @ 450 DA    │
│   ○ KRAFT KL (120 GSM, 1000 laise) @ 400 DA    │
│ Qty Rolls: [Number] 5                           │
│ Unit Price: 450.00 DA (auto-fill)               │
│ Total: 2,250 DA                                 │
│ [Delete]                                        │
│                                                 │
│ Item #2:                                        │
│ Product: [Select ▼] Papier BLANC 100 GSM       │
│ Specification: [Radio Options]                  │
│   ○ TELE BLANC TLB (80 GSM, 1000 laise) @ 350 │
│ Qty Rolls: [Number] 3                           │
│ Unit Price: 350.00 DA                           │
│ Total: 1,050 DA                                 │
│ [Delete]                                        │
└─────────────────────────────────────────────────┘

Total Amount: 3,300 DA
Status: [Draft] → [Save] [Mark as Received] [Verify]
```

---

## Unified Stock Management (Future Slices)

### For **Papier Roll** Products
```
Product: Papier KRAFT 120 GSM
├─ Stock Level: 2,500 kg (sum of all rolls)
└─ Available Rolls:
    ├─ Roll#EAN001 (KRAFT KL 120/1200) - 500 kg
    ├─ Roll#EAN002 (KRAFT KL 120/1200) - 500 kg
    ├─ Roll#EAN003 (KRAFT KL 120/1200) - 500 kg
    ├─ Roll#EAN004 (KRAFT KL 120/1000) - 400 kg
    └─ Roll#EAN005 (KRAFT KL 120/1000) - 400 kg
```

### For **Movement** (Slice 4)
- Move entire roll by EAN-13 (qty = full roll qty)
- Or move quantity from any roll (split logic in future)

### For **Consumption** (Issue to Production)
- Select roll by EAN → move to PRODUCTION_CONSUMED warehouse
- Or manually adjust stock by product

---

## Data Relationships Summary

```
Supplier
├─ has many RollSpecifications (via purchase_price)
└─ has many Receipts

PaperRollType
├─ has many RollSpecifications
└─ has many Products (existing)

Product (type='papier_roll')
├─ belongs to PaperRollType (existing - defines default type)
├─ has many RollSpecifications (new - specific combinations received)
├─ has many Rolls (existing - all individual rolls)
└─ has many StockLevels (existing - aggregated qty)

RollSpecification (NEW)
├─ belongs to Product
├─ belongs to PaperRollType
├─ belongs to Supplier (optional)
└─ has many ReceiptItems
└─ has many Rolls

Receipt (NEW)
├─ belongs to Supplier
├─ belongs to Warehouse
└─ has many ReceiptItems

ReceiptItem (NEW)
├─ belongs to Receipt
├─ belongs to RollSpecification
└─ has many Rolls (when receipt status='received')

Roll (UPDATED)
├─ belongs to Product
├─ belongs to Warehouse
├─ belongs to RollSpecification (NEW)
└─ Status: 'in_stock' → 'consumed' (when moved to PRODUCTION_CONSUMED)
```

---

## Benefits of This Structure

✅ **Clarity:** Rolls are individual (qty=1 or qty=N of same spec), specifications define combinations
✅ **Flexibility:** One product can receive rolls with different attributes
✅ **Traceability:** Each roll has unique EAN-13, creation date, batch number, supplier
✅ **Cost Accuracy:** Each specification has purchase_price for weighted average calculation
✅ **Future Proof:** Supports splitting rolls, partial consumption, and advanced analytics
✅ **Unified UI:** Single Receipt resource handles all paper + consommable receipts
✅ **Audit Ready:** ReceiptItem links qty received to specification at specific price

---

## Implementation Timeline

**Phase 1 (This Step):** Create models & migrations
- RollSpecification model + migration
- ReceiptItem model + migration
- Receipt model + migration
- Update Roll migration (add roll_specification_id, batch_number, received_date)
- Update all model relationships

**Phase 2:** Create Filament resources
- RollSpecificationResource (admin-only, for setup)
- ReceiptResource (main UI for receiving stock)

**Phase 3:** Implement receipt logic
- Generate unique EAN-13 codes
- Create individual rolls on receipt confirmation
- Update StockLevel quantities
- Recalculate product weighted average cost

**Phase 4:** Testing & refinement
- Test full receipt workflow
- Verify stock aggregation
- Test cost calculations
- Update PLAN.md with completion status

