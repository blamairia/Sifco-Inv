# Data Flow & Visual Architecture

## Three-Tier Hierarchy Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    RECEIPT WORKFLOW                             │
└─────────────────────────────────────────────────────────────────┘

  Step 1: Create Receipt
  ┌──────────────────────┐
  │ Receipt             │
  │ ├─ receipt_number   │──► Auto-generated: RCP-20251029-0001
  │ ├─ supplier_id      │──► Papiers Import SARL
  │ ├─ warehouse_id     │──► ENTREPOT_PAPIER
  │ ├─ receipt_date     │──► 2025-10-29
  │ └─ status: 'draft'  │
  └──────────────────────┘
           │
           ▼
  Step 2: Add Line Items (via ReceiptItem)
  ┌──────────────────────────────────────────────────────────┐
  │ ReceiptItem #1                                           │
  │ ├─ roll_specification_id: 5                              │
  │ │  └─ Resolves to: "KRAFT KL 120/1200 @ 450 DA"        │
  │ ├─ qty_received: 5                                       │
  │ └─ total_price: 2,250 DA                                │
  │                                                          │
  │ ReceiptItem #2                                           │
  │ ├─ roll_specification_id: 7                              │
  │ │  └─ Resolves to: "TELE BLANC TLB 80/1000 @ 350 DA"   │
  │ ├─ qty_received: 3                                       │
  │ └─ total_price: 1,050 DA                                │
  └──────────────────────────────────────────────────────────┘
           │
           ▼
  Step 3: Mark as Received (Backend generates rolls)
  ┌──────────────────────────────────────────────────────────┐
  │ FOR EACH ReceiptItem:                                    │
  │   FOR each qty_received (5 times):                       │
  │     ├─ Generate unique EAN-13 code                       │
  │     ├─ Create Roll record:                               │
  │     │  ├─ product_id: 1 (from spec.product_id)         │
  │     │  ├─ roll_specification_id: 5                       │
  │     │  ├─ warehouse_id: 2                                │
  │     │  ├─ ean_13: [UNIQUE]                              │
  │     │  ├─ qty: [from invoice or standard]               │
  │     │  ├─ status: 'in_stock'                            │
  │     │  └─ received_date: 2025-10-29                     │
  │     └─ Store to database                                 │
  │                                                          │
  │   Update StockLevel:                                     │
  │     └─ product_id=1, warehouse_id=2, qty += (5×500kg)  │
  │                                                          │
  │   Update Product weighted average cost                   │
  └──────────────────────────────────────────────────────────┘
           │
           ▼
  Result: Inventory Updated
  ┌──────────────────────────────────────────────────────────┐
  │ Product: Papier KRAFT 120 GSM                            │
  │ Warehouse: ENTREPOT_PAPIER                               │
  │ Total Qty: 2,500 kg                                      │
  │ Average Cost: 450.00 DA/roll                             │
  │                                                          │
  │ Individual Rolls:                                        │
  │ ├─ Roll#1: EAN=978123456001, qty=500, spec=KL/1200     │
  │ ├─ Roll#2: EAN=978123456002, qty=500, spec=KL/1200     │
  │ ├─ Roll#3: EAN=978123456003, qty=500, spec=KL/1200     │
  │ ├─ Roll#4: EAN=978123456004, qty=500, spec=KL/1200     │
  │ └─ Roll#5: EAN=978123456005, qty=500, spec=KL/1200     │
  │                                                          │
  │ Product: Papier BLANC 100 GSM                            │
  │ Warehouse: ENTREPOT_PAPIER                               │
  │ Total Qty: 1,050 kg                                      │
  │ Average Cost: 350.00 DA/roll                             │
  │                                                          │
  │ Individual Rolls:                                        │
  │ ├─ Roll#6: EAN=978123456006, qty=350, spec=TLB/1000    │
  │ ├─ Roll#7: EAN=978123456007, qty=350, spec=TLB/1000    │
  │ └─ Roll#8: EAN=978123456008, qty=350, spec=TLB/1000    │
  └──────────────────────────────────────────────────────────┘
```

---

## Database Relationship Diagram

```
                           ┌─────────────────────┐
                           │  PaperRollType      │
                           │  ────────────────   │
                           │  id (PK)            │
                           │  type_code (UNIQUE) │◄────┐
                           │  name               │     │
                           │  grammage           │     │
                           │  laise              │     │
                           │  weight             │     │
                           └─────────────────────┘     │
                                    ▲                  │
                                    │                  │
                            has many │                 │
                                    │                  │
         ┌──────────────────────────┴──────────────────────────────────┐
         │                                                              │
    ┌────┴────────────────┐                              ┌─────────────┴──────┐
    │   Product           │                              │  RollSpecification │
    │   ────────────────  │◄─────has many───────────────┤  ──────────────    │
    │   id (PK)           │                              │  id (PK)           │
    │   name              │                              │  product_id (FK)   │
    │   type              │                              │  paper_roll_type.. │
    │   category_id       │                              │  supplier_id (FK)  │
    │   subcategory_id    │                              │  purchase_price    │
    │   unit_id           │                              │  is_active         │
    │   paper_roll_type.. │───belongs to──────────────────┤  description       │
    │   min_stock         │                              │  UNIQUE(prod,type, │
    │   safety_stock      │                              │         supplier)  │
    │   avg_cost          │                              └────────┬────────────┘
    │   timestamps        │                                       │
    └────────┬────────────┘                                       │
             │                                                    │
             │                      ┌──────────────────────┐      │
             │                      │  Supplier            │      │
             │                      │  ──────────────────  │      │
             │                      │  id (PK)             │      │
             │                      │  name                │◄─────┤
             │                      │  contact_person      │  many
             │                      │  phone               │
             │                      │  email               │
             │                      │  timestamps          │
             │                      └──────────────────────┘
             │
             │
       has many
             │
             ▼
    ┌─────────────────────┐       ┌──────────────────────┐
    │  Roll               │       │  Warehouse           │
    │  ──────────────────  │──────►│  ──────────────────  │
    │  id (PK)            │belongs │  id (PK)             │
    │  product_id (FK) ───┤to      │  name                │
    │  warehouse_id (FK)──┤       │  is_system           │
    │  roll_spec_id (FK)──┼──────►│  timestamps          │
    │  ean_13 (UNIQUE)    │       │                      │
    │  qty                │       └──────────────────────┘
    │  status             │                    ▲
    │  batch_number       │                    │
    │  received_date      │              belongs
    │  timestamps         │                to
    │                     │                    │
    └─────────────────────┘         ┌─────────┴──────────┐
             ▲                       │  StockLevel        │
             │                       │  ──────────────    │
       has many                      │  id (PK)           │
             │                       │  product_id (FK)   │
             │                       │  warehouse_id (FK) │
    ┌────────┴──────────────┐       │  qty               │
    │  ReceiptItem          │       │  UNIQUE(prod, wh)  │
    │  ──────────────────── │       │  timestamps        │
    │  id (PK)              │       └────────────────────┘
    │  receipt_id (FK)      │
    │  roll_spec_id (FK) ───┼───────┐
    │  qty_received         │       │
    │  total_price          │       │
    │  notes                │       │
    │  timestamps           │       │
    └────────┬──────────────┘       │
             │              belongs  │
             │              to       │
             ▼                       │
    ┌─────────────────────┐         │
    │  Receipt            │◄────────┘
    │  ─────────────────  │
    │  id (PK)            │
    │  receipt_number     │
    │  supplier_id (FK) ──┼──► Supplier
    │  warehouse_id (FK)──┼──► Warehouse
    │  receipt_date       │
    │  total_amount       │
    │  status             │
    │  notes              │
    │  timestamps         │
    └─────────────────────┘
```

---

## Stock Aggregation Query Pattern

```sql
-- Get all rolls of a product grouped by specification attributes
SELECT 
  prt.type_code,
  prt.grammage,
  prt.laise,
  prt.weight,
  s.name as supplier_name,
  rs.purchase_price,
  COUNT(r.id) as roll_count,
  SUM(r.qty) as total_qty_kg,
  COUNT(r.id) * prt.weight as expected_weight
FROM rolls r
JOIN roll_specifications rs ON r.roll_specification_id = rs.id
JOIN paper_roll_types prt ON rs.paper_roll_type_id = prt.id
LEFT JOIN suppliers s ON rs.supplier_id = s.id
WHERE r.product_id = 1
  AND r.warehouse_id = 2
  AND r.status = 'in_stock'
GROUP BY prt.type_code, prt.grammage, prt.laise, prt.weight, s.name, rs.purchase_price
ORDER BY prt.grammage DESC, prt.laise DESC;
```

**Output Example:**
```
type_code | grammage | laise | weight | supplier_name      | purchase_price | roll_count | total_qty_kg | expected_weight
----------|----------|-------|--------|--------------------|----|----------|----------|----------|
KL        | 120      | 1200  | 500.00 | Papiers Import     | 450.00 | 5  | 2500.00 | 2500.00
KL        | 120      | 1000  | 400.00 | Fournisseur ABC    | 420.00 | 3  | 1200.00 | 1200.00
```

---

## Movement Workflow (Future - Slice 4)

```
┌──────────────────────────────────────┐
│  Scan Roll by EAN-13                 │
│  └─ Lookup: Roll#id=5, EAN='978...'  │
└──────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────┐
│  Show Roll Details                   │
│  ├─ Product: Papier KRAFT 120 GSM    │
│  ├─ Qty: 500 kg                      │
│  ├─ Current Warehouse: ENTREPOT_PAPIER
│  ├─ Specification: KL/1200 @ 450 DA  │
│  └─ Received: 2025-10-29             │
└──────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────┐
│  Move Roll                           │
│  ├─ From: ENTREPOT_PAPIER            │
│  ├─ To: [Select Warehouse]           │
│  │   └─ ENTREPOT_CONSOMMABLES        │
│  ├─ Update Roll.warehouse_id = 3    │
│  ├─ Update source StockLevel: qty-=500
│  └─ Update dest StockLevel: qty+=500 │
└──────────────────────────────────────┘
```

---

## Issue to Production (Consumption)

```
┌──────────────────────────────────────┐
│  Select Roll to Consume              │
│  ├─ Scan EAN or Select from list     │
│  └─ Show: Papier KRAFT 120 GSM, 500kg│
└──────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────┐
│  Record Consumption                  │
│  ├─ Qty to consume: 500 kg (full)    │
│  ├─ Update Roll.status: 'consumed'   │
│  ├─ Update Roll.warehouse_id:        │
│  │   → PRODUCTION_CONSUMED (system)  │
│  ├─ Update StockLevel (source)       │
│  │   qty -= 500                      │
│  └─ Audit log entry created          │
└──────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────┐
│  Result                              │
│  ├─ Roll#5 now in PRODUCTION_CONSUMED│
│  ├─ Status: 'consumed'               │
│  ├─ Original warehouse qty: 0        │
│  └─ Weighted avg cost preserved      │
└──────────────────────────────────────┘
```

---

## State Transitions for Rolls

```
┌─────────────────────────────────────────────────────┐
│                 ROLL LIFECYCLE                      │
└─────────────────────────────────────────────────────┘

Creation (Receipt Confirmation)
        │
        ▼
   ┌─────────────┐
   │  in_stock   │◄─────── Initial status when received
   │ warehouse:  │
   │ ENTREPOT_X  │
   └─────┬───────┘
         │
    Movement ◄──┐  (Slice 4)
         │      │  Can move between operational warehouses
         ▼      │
   ┌─────────────┐
   │  in_stock   │
   │ warehouse:  │
   │ ENTREPOT_Y  │
   └─────┬───────┘
         │
    Consumption
         │
         ▼
   ┌──────────────┐
   │  consumed    │  Final state when issued to production
   │ warehouse:   │
   │ PRODUCTION_  │  (System warehouse - never modified)
   │ CONSUMED     │
   └──────────────┘
         │
         ▼
   ┌──────────────┐
   │   (Deleted)  │  Purged after period (e.g., 6 months)
   │              │  Retained in audit tables
   └──────────────┘
```

---

## Key Indexes for Performance

```sql
-- In migrations (ensure fast lookups)

ALTER TABLE rolls ADD INDEX idx_ean_13 (ean_13 UNIQUE);
ALTER TABLE rolls ADD INDEX idx_product_warehouse (product_id, warehouse_id);
ALTER TABLE rolls ADD INDEX idx_specification (roll_specification_id);

ALTER TABLE roll_specifications ADD INDEX idx_product (product_id);
ALTER TABLE roll_specifications ADD INDEX idx_type (paper_roll_type_id);
ALTER TABLE roll_specifications ADD INDEX idx_supplier (supplier_id);

ALTER TABLE stock_levels ADD INDEX idx_product_warehouse (product_id, warehouse_id);

ALTER TABLE receipts ADD INDEX idx_status_date (status, created_at);
ALTER TABLE receipt_items ADD INDEX idx_receipt (receipt_id);
```

This ensures:
- Barcode scanning (EAN-13) is instant ⚡
- Product inventory lookup is instant
- Receipt filtering fast
- Specification lookups efficient

