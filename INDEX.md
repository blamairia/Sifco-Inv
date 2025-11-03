# 📚 Documentation Index – v4.0

**Status:** Phase 3 Complete, Phase 4 (Bon de Sortie) Starting �  
**Updated:** 2025-11-03  
**Database:** MySQL 8.0.44 | 27 tables | Laravel 11 | Filament v4.0.0  
**Files:** 4 core docs

---

## 📋 Core Documentation Files

1. **README.md** – Project overview + tech stack
2. **PLAN.md** ⭐ **START HERE** – Current roadmap & status (Phase 4)
3. **PROCEDURE_MAPPING.md** ⭐ **ESSENTIAL** – SIFCO procedures → code mapping
4. **DATABASE_REDESIGN.md** – Complete schema (27 tables)
5. **INDEX.md** – This file

---

## 📊 Phase Progress Tracker

### ✅ Phase 3 Complete (Slice 3 & 4 – Bon d'Entrée Workflow)

- [x] **Analysis & Design** ✅ DONE
  - [x] Defined two-step validation workflow (draft → pending → received)
  - [x] Designed separate repeaters for "bobines" and "produits"
  - [x] Specified CUMP calculation logic
  - [x] Added `item_type`, `ean_13`, `batch_number` to `bon_entree_items`

- [x] **Implementation** ✅ DONE
  - [x] Created `BonEntreeService` to handle validation and reception logic
  - [x] Implemented `validate()` method (distributes frais d'approche)
  - [x] Implemented `receive()` method (creates `Rolls`, updates `StockQuantity`, creates `StockMovement`)
  - [x] Created `CumpCalculator` service
  - [x] Updated `BonEntree` Filament resource with two repeaters (`bobineItems`, `productItems`)
  - [x] Added "Valider" and "Recevoir" actions to `BonEntreesTable` and `EditBonEntree` page
  - [x] Added `bon_entree_item_id` to `rolls` table
  - [x] Fixed all database schema and logic bugs preventing Roll creation

- [x] **Testing & Validation** ✅ DONE
  - [x] Created test seeders for Bon d'Entrée scenarios
  - [x] Verified that receiving a bon correctly creates `Roll` records
  - [x] Verified that stock quantities and CUMP are updated correctly
  - [x] Verified that `StockMovement` records are created for all items

### � Phase 4 Starting (Slice 5 – Bon de Sortie Workflow)

- [ ] **Review & Plan:** Re-evaluate `BonSortie` logic based on new `Roll` and stock system
- [ ] **Implement Bobine Selection:** Allow users to select specific `Rolls` (by EAN-13) for a `BonSortie`
- [ ] **Update Stock:** On sortie confirmation, update `Roll` status to 'consumed' and decrement `StockQuantity`
- [ ] **Create Movements:** Generate `StockMovement` records for each sortie
- [ ] **Test:** End-to-end testing of the sortie workflow

---

## 📖 Old System Prompt (Deprecated)

The old system prompt below is superseded by the content in **PROCEDURE_MAPPING.md** and **DATABASE_REDESIGN.md**.

**For new context:** Use information from:
- **PLAN.md** (current phase & status)
- **PROCEDURE_MAPPING.md** (workflows)
- **DATABASE_REDESIGN.md** (data model)

═══════════════════════════════════════════════════════════════════

CORE ARCHITECTURE (Three-Tier Hierarchy)
═══════════════════════════════════════════════════════════════════

1. PaperRollType (Attributes Only)
   - Defines: size, grammage, laise, FL, weight
   - Single source of truth: "What is an A4 80gsm roll?"
   - No quantity or ownership
   - Used for grouping and querying
   
2. RollSpecification (Purchasing Decision)
   - Links: Product + PaperRollType + Supplier + Price
   - Means: "We buy THIS product, WITH these specs, FROM this supplier"
   - Multiple suppliers can provide same product/type combo
   - Each has different pricing, lead times
   - Referenced when receipts arrive
   
3. Roll (Individual Physical Inventory)
   - One Roll record = ONE physical roll
   - UNIQUE EAN-13 (globally unique, never duplicated)
   - Has: warehouse, batch, expiry, status
   - Quantity ALWAYS = 1
   - Created during receipt confirmation

═══════════════════════════════════════════════════════════════════

REQUIREMENTS SOLVED
═══════════════════════════════════════════════════════════════════

✓ Each roll has unique EAN-13
  → UNIQUE constraint on Roll.ean_13
  → Cannot create duplicates
  
✓ One quantity per roll
  → 1 Roll record = 1 physical roll
  → Never group units in one record
  
✓ Group quantities by attributes
  → PaperRollType defines attributes
  → Query through Roll → RollSpecification → PaperRollType
  
✓ Unified receipt workflow
  → Single Receipt table for all types
  → Single ReceiptItem table for details
  → One code path processes everything
  
✓ Complete audit trail
  → All transactions tracked
  → Historical pricing preserved
  → Never delete, just mark inactive

═══════════════════════════════════════════════════════════════════

RECEIPT WORKFLOW
═══════════════════════════════════════════════════════════════════

Step 1: Receipt Created
  - receipt_number: "REC-2025-001"
  - supplier_id: 5
  - warehouse_id: 1
  - status: "pending"
  
Step 2: ReceiptItems Added
  - roll_specification_id: 42
  - quantity_received: 500
  
Step 3: Receipt Confirmed (Status → "confirmed")
  - Triggers Roll creation
  
Step 4: 500 Roll Records Created
  - Each with unique EAN-13
  - Each with roll_specification_id: 42
  - Each with warehouse_id: 1
  - All with status: "active"
  
Step 5: StockLevel Updated
  - Aggregates count by warehouse + paper_roll_type
  - Quick queries: "How many A4 80gsm in warehouse 1?"
  
Step 6: Audit Trail Complete
  - Can trace any roll back to receipt
  - Can see what was paid
  - Can see which supplier provided it

═══════════════════════════════════════════════════════════════════

CORE DATABASE TABLES
═══════════════════════════════════════════════════════════════════

PaperRollType (Attributes)
├─ id, size, grammage, laise, fl, weight
├─ UNIQUE(size, grammage, laise, fl)
└─ Purpose: Single definition of roll type

RollSpecification (Purchasing Decision)
├─ id, product_id, paper_roll_type_id, supplier_id, unit_price
├─ UNIQUE(product_id, paper_roll_type_id, supplier_id)
└─ Purpose: "Who provides what, at what price"

Receipt (Transaction Header)
├─ id, receipt_number, receipt_date, supplier_id, warehouse_id
├─ receipt_type: purchase/internal/return
├─ UNIQUE(receipt_number)
└─ Purpose: Top-level receipt document

ReceiptItem (Transaction Details)
├─ id, receipt_id, roll_specification_id, quantity_received, quantity_accepted
├─ UNIQUE(receipt_id, roll_specification_id)
└─ Purpose: Individual line items

Roll (Physical Inventory)
├─ id, ean_13, roll_specification_id, warehouse_id
├─ batch_number, received_date, expiry_date, status
├─ quantity: always 1
├─ UNIQUE(ean_13)
└─ Purpose: Physical roll tracking

StockLevel (Aggregated View)
├─ warehouse_id, paper_roll_type_id, quantity
├─ UNIQUE(warehouse_id, paper_roll_type_id)
└─ Purpose: Fast stock queries

Product, Warehouse, Supplier (Master Data)
└─ Referenced by other tables

═══════════════════════════════════════════════════════════════════

QUERY PATTERNS (Examples)
═══════════════════════════════════════════════════════════════════

"How many A4 80gsm rolls in Warehouse 1?"
SELECT COUNT(*) FROM rolls r
JOIN roll_specifications rs ON r.roll_specification_id = rs.id
JOIN paper_roll_types prt ON rs.paper_roll_type_id = prt.id
WHERE prt.size = 'A4' AND prt.grammage = 80 AND r.warehouse_id = 1;

"What did we pay for roll with EAN 5901234123457?"
SELECT rs.unit_price FROM rolls r
JOIN roll_specifications rs ON r.roll_specification_id = rs.id
WHERE r.ean_13 = '5901234123457';

"Where did this roll come from?"
SELECT r.receipt_number, s.name, ri.quantity_received
FROM rolls r
JOIN roll_specifications rs ON r.roll_specification_id = rs.id
JOIN receipt_items ri ON rs.id = ri.roll_specification_id
JOIN receipts rec ON ri.receipt_id = rec.id
JOIN suppliers s ON rec.supplier_id = s.id
WHERE r.ean_13 = '5901234123457';

═══════════════════════════════════════════════════════════════════

IMPORTANT PRINCIPLES
═══════════════════════════════════════════════════════════════════

1. Separate Concerns
   - Don't mix attributes (PaperRollType) with purchasing (RollSpecification)
   - Don't mix purchasing with inventory (Roll)
   - Each tier has one responsibility

2. Uniqueness
   - EAN-13 ONLY goes on Roll (individual physical items)
   - Never put EAN-13 on RollSpecification (purchasing decision)
   - Never duplicate EAN-13 values

3. Quantity Handling
   - 1 Roll record = 1 physical roll
   - Never group quantities in one record
   - Count Roll records to get total quantity

4. Audit Trail
   - Never delete, just mark status as inactive
   - All historical data preserved
   - Can trace any roll back to receipt
   - Can see pricing at time of purchase

5. Flexibility
   - Multiple suppliers can provide same product/type
   - Same product can have different suppliers
   - Pricing per supplier combination
   - Easy to compare suppliers

═══════════════════════════════════════════════════════════════════

WHEN MAKING CHANGES
═══════════════════════════════════════════════════════════════════

DO:
✓ Preserve three-tier hierarchy
✓ Maintain UNIQUE constraints
✓ Keep audit trail (add status field vs delete)
✓ Consider receipt workflow impact
✓ Test queries through all three tiers

DON'T:
✗ Put EAN-13 on RollSpecification
✗ Group quantity in one Roll record
✗ Delete historical data
✗ Mix concerns between tiers
✗ Duplicate EAN-13 codes

═══════════════════════════════════════════════════════════════════

CURRENT IMPLEMENTATION STATUS
═══════════════════════════════════════════════════════════════════

✅ Completed:
  - Slice 1: Master data (Products, Warehouses, Suppliers)
  - Slice 2: Stock storage with architectural refactor
  - All 12 models created with relationships
  - All 14 migrations executed successfully
  - All 11 Filament resources created
  - Database fully tested

⏳ Next (Slice 3):
  - Configure RollSpecificationResource UI
  - Configure ReceiptResource UI
  - Test complete receipt workflow

═══════════════════════════════════════════════════════════════════

REFERENCE DOCUMENTS
═══════════════════════════════════════════════════════════════════

- ARCHITECTURE_REVIEW.md: Complete explanation (20 min read)
- VISUAL_ARCHITECTURE.md: Diagrams & SQL patterns (15 min read)
- README.md: Quick overview (5 min read)
- Plan.md: Current status & roadmap (5 min read)

═══════════════════════════════════════════════════════════════════
```

---

## 🎯 Quick Navigation

| Goal | Read |
|------|------|
| New to project? | README.md → ARCHITECTURE_REVIEW.md → Plan.md |
| Need status? | Plan.md |
| Understand architecture? | ARCHITECTURE_REVIEW.md |
| Building Slice 3? | ARCHITECTURE_REVIEW.md + VISUAL_ARCHITECTURE.md |
| Show me queries/diagrams? | VISUAL_ARCHITECTURE.md |
| I'm a manager? | README.md + Plan.md |

---

## 📄 Five Core Documentation Files

### README.md
- **Purpose:** Project overview & quick start
- **Read Time:** 5 min
- **Contains:** Status, tech stack, architecture, getting started
- **Status:** ✅ Current

### ARCHITECTURE_REVIEW.md ⭐ CENTRAL HUB
- **Purpose:** In-depth architecture explanation
- **Read Time:** 20 min
- **Contains:** Problem & solution, three-tier hierarchy, database schema, queries, FAQ, Slice 3 roadmap
- **Status:** ✅ Complete

### VISUAL_ARCHITECTURE.md
- **Purpose:** Diagrams & implementation patterns
- **Read Time:** 15 min
- **Contains:** ASCII diagrams, database relationships, SQL patterns, workflows, state transitions
- **Status:** ✅ Complete

### Plan.md
- **Purpose:** Project roadmap & progress
- **Read Time:** 5 min
- **Contains:** Slice tracking, status, TODO items
- **Status:** ✅ Current

### INDEX.md (THIS FILE)
- **Purpose:** Navigation guide
- **Contains:** Quick paths, file summaries, GPT system prompt, next steps
- **Status:** ✅ Streamlined

---

## 📊 Coverage

| Topic | File |
|-------|------|
| Project Overview | README.md |
| Architecture Design | ARCHITECTURE_REVIEW.md |
| Database Schema | ARCHITECTURE_REVIEW.md |
| Queries & Patterns | VISUAL_ARCHITECTURE.md |
| Diagrams | VISUAL_ARCHITECTURE.md |
| Progress | Plan.md |
| Implementation | ARCHITECTURE_REVIEW.md |

---

## ✅ Cleanup Done

**Removed (redundant/old):**
- ❌ ARCHITECTURE.md
- ❌ STRUCTURAL_SOLUTION.md
- ❌ SOLUTION_SUMMARY.md
- ❌ ARCHITECTURE_STATUS.md
- ❌ SESSION_SUMMARY.md

**Kept (essential):**
- ✅ README.md
- ✅ ARCHITECTURE_REVIEW.md
- ✅ VISUAL_ARCHITECTURE.md
- ✅ Plan.md
- ✅ INDEX.md

---

## 🗂️ Project Structure

```
Docs (5 files):
├─ README.md ..................... Entry point
├─ ARCHITECTURE_REVIEW.md ........ Design hub ⭐
├─ VISUAL_ARCHITECTURE.md ........ Diagrams & queries
├─ Plan.md ....................... Roadmap
└─ INDEX.md ...................... You are here (with system prompt)

Code:
app/Models/ ...................... 12 models
app/Filament/Resources/ .......... 11 resources
database/migrations/ ............. 14 migrations

Database:
14 tables, 30+ foreign keys, 10+ indexes
```

---

## 🚀 Ready for Slice 3

Next: Implement Receipt Workflow
- Configure RollSpecificationResource
- Configure ReceiptResource
- Test complete flow

See ARCHITECTURE_REVIEW.md "Slice 3 Implementation" section.

---

**Last Updated:** 2025-10-30
Bookmark this file for quick navigation & system prompt.
