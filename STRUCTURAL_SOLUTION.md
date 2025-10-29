# Structural Solution: Roll Management Architecture

## Problem Statement

You identified a critical structural incomprehension in the original design:

> "The roles and their code each role has a unique identifier EAN-13 so we can only have one in the quantity and the roles total quantity should be grouped by paper roll types attributes grammage laise and then the total quanitity should be handled in the next step in the recipts"

**The core issue:** Conflicting concepts were merged:
- **Roll concept:** Individual physical roll with unique EAN-13 code (qty should always = the actual weight/qty of that roll)
- **Product concept:** Can be linked to multiple different specifications of the same paper type (120 GSM, different laise widths, from different suppliers)
- **Receipt concept:** Need to receive multiple rolls of different specifications in one operation

---

## Solution Architecture: Three-Tier Hierarchy

### Tier 1: **PaperRollType** (Attributes)
Defines the physical characteristics of a paper specification.

```
PaperRollType {
  type_code: 'KL'           // Unique code
  name: 'KRAFT KL'
  grammage: 120             // GSM
  laise: 1200               // Width in mm
  weight: 500.00            // Standard weight per roll in kg
  description: 'Kraft paper for cardboard production'
}
```

**Purpose:** Standardize paper specifications across the system. Multiple products can reference the same type, but a receipt might need variations.

**Database Table:** `paper_roll_types`
- 4 master types: KL, TLB, TLM, FL
- Never changes unless sourcing new paper type

---

### Tier 2: **RollSpecification** (Unique Combinations)
Defines EXACTLY what combination of Product + PaperRollType + Supplier represents a receivable item.

```
RollSpecification {
  product_id: 1                           // Papier KRAFT 120 GSM
  paper_roll_type_id: 1                   // KRAFT KL (120/1200/500)
  supplier_id: 2                          // Papiers Import SARL
  purchase_price: 450.00                  // DA per roll
  is_active: true
  description: 'KL paper from Papiers Import - preferred supplier'
}
```

**Why this layer exists:**
- **Same product, different attributes:** "Papier KRAFT 120 GSM" might be receivable as:
  - KL (1200 laise) @ 450 DA from Supplier A
  - KL (1000 laise) @ 420 DA from Supplier B
  - KL (1500 laise) @ 500 DA from Supplier A
- **Supplier variations:** Different suppliers provide different pricing/attributes
- **Cost tracking:** Each specification has its own purchase_price for weighted average calculation
- **Admin-configured:** Set up once, reused in receipts

**Database Table:** `roll_specifications`
- Unique constraint: (product_id, paper_roll_type_id, supplier_id)
- Can have 5-10+ per product for flexibility
- Links all three concepts together

---

### Tier 3: **Roll** (Individual Physical Inventory)
Represents ONE individual physical roll in the warehouse.

```
Roll {
  id: 1
  product_id: 1                           // Papier KRAFT 120 GSM
  roll_specification_id: 5                // KL/1200/Supplier A @ 450 DA
  warehouse_id: 2                         // ENTREPOT_PAPIER
  ean_13: '9791234567890'                 // UNIQUE barcode for THIS roll
  qty: 500.00                             // Actual weight in kg
  status: 'in_stock'                      // or 'consumed'
  batch_number: 'PA-2025-1001'           // Optional batch/lot
  received_date: '2025-10-29'            // When received
}
```

**Key Rules:**
- Each Roll represents ONE physical roll
- EAN-13 is UNIQUE - identifies THIS specific roll
- qty = actual weight/count of THIS roll (typically from invoice)
- Linked to RollSpecification to know its exact attributes
- Never has qty > 1 for a single EAN - that's one roll
- If you receive 5 rolls of the same spec, you create 5 Roll records with 5 different EAN codes

**Database Table:** `rolls`
- Foreign key to RollSpecification
- Index on EAN-13 for quick lookup
- Status for tracking consumed vs in-stock

---

## Receipt Workflow: Unified Stock-In Process

### Single ReceiptResource handles all product types:

**Step 1: Create Receipt**
```
Receipt {
  receipt_number: 'RCP-20251029-0001'    // Auto-generated
  supplier_id: 2                          // Papiers Import SARL
  warehouse_id: 2                         // ENTREPOT_PAPIER
  receipt_date: 2025-10-29
  status: 'draft'                         // draft → received → verified
  notes: 'Regular weekly delivery'
}
```

**Step 2: Add Line Items (Repeater)**
```
ReceiptItem #1 {
  receipt_id: 1
  roll_specification_id: 5                // KL/1200/Supplier A
  qty_received: 5                         // 5 rolls of this spec
  total_price: 2250.00                    // 5 × 450 DA
  notes: 'Slight color variation, quality verified'
}

ReceiptItem #2 {
  receipt_id: 1
  roll_specification_id: 7                // TLB/1000/Supplier A
  qty_received: 3
  total_price: 1050.00                    // 3 × 350 DA
  notes: null
}
```

**UI Flow in Filament:**
```
┌─ Receipt Details ───────────────────────────────┐
│ Receipt #: RCP-20251029-0001 (auto)             │
│ Supplier: [Select] Papiers Import SARL          │
│ Warehouse: [Select] ENTREPOT_PAPIER             │
│ Receipt Date: 2025-10-29                        │
│ Status: [Draft] [Mark as Received]              │
└─────────────────────────────────────────────────┘

┌─ Line Items Repeater ───────────────────────────┐
│ [+ Add Item]                                    │
│                                                 │
│ Item #1:                                        │
│ ┌─ Select Product ────────────────────────────┐ │
│ │ [Papier KRAFT 120 GSM ▼]                   │ │
│ └────────────────────────────────────────────┘ │
│ ┌─ Available Specifications ────────────────┐ │
│ │ ○ KRAFT KL (120/1200) @ 450 DA            │ │
│ │   ├─ Grammage: 120 GSM                    │ │
│ │   ├─ Laise: 1200 mm                       │ │
│ │   ├─ Weight/roll: 500 kg                  │ │
│ │   └─ Supplier: Papiers Import SARL        │ │
│ │                                            │ │
│ │ ○ KRAFT KL (120/1000) @ 420 DA            │ │
│ │   ├─ Grammage: 120 GSM                    │ │
│ │   ├─ Laise: 1000 mm                       │ │
│ │   ├─ Weight/roll: 400 kg                  │ │
│ │   └─ Supplier: Fournisseur ABC            │ │
│ │                                            │ │
│ │ ○ KRAFT KL (120/1500) @ 500 DA            │ │
│ │   └─ (Other attributes...)                │ │
│ └────────────────────────────────────────────┘ │
│ Qty Rolls: [5]                                  │
│ Unit Price: 450.00 (auto-filled)               │
│ Total: 2,250.00 DA                             │
│ [Delete this item]                             │
│                                                 │
│ Item #2: [Similar structure]                   │
└─────────────────────────────────────────────────┘

Total Amount: 3,300 DA
[Save as Draft] [Mark as Received] [Cancel]
```

**Step 3: Confirm Receipt (Status → "Received")**
Backend logic automatically:
1. Generate 5 unique EAN-13 codes for Item #1
2. Create 5 individual Roll records:
   - Roll#1: EAN-13='978123...001', spec=5, qty=500, status=in_stock
   - Roll#2: EAN-13='978123...002', spec=5, qty=500, status=in_stock
   - ... (all 5)
3. Create 3 individual Roll records for Item #2 (different EAN codes)
4. Update `StockLevel` for product 1: qty += 2500 (5×500)
5. Update `StockLevel` for product 2: qty += 1050 (3×350)
6. Recalculate product weighted average costs
7. Set receipt status to 'received'

**Result in Inventory:**
```
Product: Papier KRAFT 120 GSM
├─ Total Qty in ENTREPOT_PAPIER: 2,500 kg
└─ Individual Rolls:
    ├─ EAN: 9791234567891 | Qty: 500 kg | Spec: KL/1200/Supplier A | Status: in_stock
    ├─ EAN: 9791234567892 | Qty: 500 kg | Spec: KL/1200/Supplier A | Status: in_stock
    ├─ EAN: 9791234567893 | Qty: 500 kg | Spec: KL/1200/Supplier A | Status: in_stock
    ├─ EAN: 9791234567894 | Qty: 500 kg | Spec: KL/1200/Supplier A | Status: in_stock
    └─ EAN: 9791234567895 | Qty: 500 kg | Spec: KL/1200/Supplier A | Status: in_stock
```

---

## Key Design Benefits

### ✅ **Uniqueness Guaranteed**
- Each Roll has ONE unique EAN-13
- If a roll has EAN-13 = X, only ONE roll exists with that code
- Perfect for barcode scanning and individual roll tracking

### ✅ **Flexible Specifications**
- Same product can have multiple receivable specifications
- Supplier can change attributes (laise, grammage) without creating new product
- Cost differences captured per specification
- Weighted average cost calculated correctly

### ✅ **Grouped by Attributes**
- Query all rolls of product by their specification attributes:
  ```sql
  SELECT rolls.* 
  FROM rolls
  JOIN roll_specifications ON rolls.roll_specification_id = roll_specifications.id
  WHERE roll_specifications.product_id = 1
    AND roll_specifications.paper_roll_type_id = 1  -- All KL-type rolls
  GROUP BY roll_specifications.id
  ```
- Get total qty by attributes:
  ```sql
  SELECT 
    prt.grammage,
    prt.laise,
    prt.weight,
    COUNT(rolls.id) as roll_count,
    SUM(rolls.qty) as total_qty
  FROM rolls
  JOIN roll_specifications ON rolls.roll_specification_id = roll_specifications.id
  JOIN paper_roll_types prt ON roll_specifications.paper_roll_type_id = prt.id
  WHERE rolls.product_id = 1
  GROUP BY prt.grammage, prt.laise, prt.weight
  ```

### ✅ **Handles Next Steps**
- **Movement (Slice 4):** Move by EAN → move entire roll or qty from a roll
- **Consumption (Issue to Production):** Pick roll by EAN → move to PRODUCTION_CONSUMED warehouse
- **Adjustments (Slice 5):** Adjust individual roll or entire product qty
- **Valuation (Slice 7):** Calculate inventory value using individual roll costs

---

## Database Relationships

```
Supplier
├─ has many RollSpecifications (for each acceptable supply combination)
└─ has many Receipts (all receipts from this supplier)

PaperRollType (Master)
├─ has many RollSpecifications (can be used by many products)
└─ has many Products (as default type)

Product
├─ belongs to PaperRollType (default, optional)
├─ has many RollSpecifications (specific receive combinations)
├─ has many Rolls (all individual rolls of this product)
└─ has many StockLevels (qty per warehouse)

RollSpecification ⭐ KEY
├─ belongs to Product
├─ belongs to PaperRollType
├─ belongs to Supplier (optional)
├─ has many ReceiptItems (line items received)
└─ has many Rolls (individual rolls of this spec)

Receipt
├─ belongs to Supplier
├─ belongs to Warehouse
└─ has many ReceiptItems (line items in receipt)

ReceiptItem
├─ belongs to Receipt
├─ belongs to RollSpecification
└─ creates many Rolls (when receipt confirmed)

Roll (Individual Inventory)
├─ belongs to Product
├─ belongs to Warehouse
├─ belongs to RollSpecification
└─ status: in_stock → consumed (when moved to PRODUCTION_CONSUMED)

StockLevel (Aggregate)
├─ belongs to Product
├─ belongs to Warehouse
└─ qty = SUM(rolls.qty) WHERE product_id=X AND warehouse_id=Y
```

---

## Implementation Checklist

### ✅ Already Completed
- [x] RollSpecification model created
- [x] Receipt model created
- [x] ReceiptItem model created
- [x] All migrations executed successfully
- [x] All model relationships configured
- [x] Filament resources scaffolded
- [x] Database structure verified

### 🔄 Next Steps (Slice 3)
- [ ] Configure RollSpecificationResource (form + table)
- [ ] Configure ReceiptResource (form + receipt number generation)
- [ ] Configure ReceiptItem repeater (inline in receipt form)
- [ ] Implement specification selector UI (radio buttons with attributes)
- [ ] Implement receipt confirmation logic (roll generation)
- [ ] Implement EAN-13 generation function
- [ ] Implement stock level updates on receipt confirmation
- [ ] Implement weighted average cost recalculation
- [ ] Add sample roll specifications to seeder
- [ ] End-to-end receipt testing

---

## Notes on "Qty = 1" Per Roll

You noted: *"each role has a unique identifier EAN-13 so we can only have one in the quantity"*

**Clarification:** Each **Roll record** doesn't mean qty=1 always. Rather:
- Each Roll represents one physical roll (could be 500 kg, 400 kg, etc.)
- qty field stores the actual weight/count of THAT roll
- EAN-13 is the unique identifier (one EAN per Roll record)
- If you receive a roll that's 500 kg, that Roll.qty = 500
- If you receive 5 rolls, you create 5 Roll records with 5 different EAN codes

This way:
- Uniqueness is preserved (each EAN = one roll)
- Attributes are grouped (by specification)
- Qty is handled properly (individual roll qty + aggregate by product)

