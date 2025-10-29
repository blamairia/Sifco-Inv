# CartonStock MVP Plan

## High-Level Scope (SRS v1.0)

- **Core:** Track products, stock levels per warehouse, and individual paper rolls.
- **Features:** Receive materials from suppliers, move stock between warehouses, issue stock to production (via a virtual `PRODUCTION_CONSUMED` warehouse), and perform manual stock adjustments.
- **Reporting:** A dashboard showing key metrics, a low stock alert page, and an inventory valuation view with CSV export.
- **Data:** Includes a suppliers directory and uses weighted average costing.
- **Tech:** Laravel 11 + Filament v4 on Windows/MySQL. UI in French.

---

## Slices

- [x] **Slice 1: Core master data** (Products, Warehouses, Suppliers)
- [x] **Slice 2: Stock storage structure** (stock_levels, rolls) - COMPLETED with architectural refactor
- [ ] **Slice 3: Receipts (stock in)** - RollSpecification & Receipt infrastructure ready
- [ ] **Slice 4: Stock movement**
- [ ] **Slice 5: Manual adjustments**
- [ ] **Slice 6: Dashboard & Alerts**
- [ ] **Slice 7: Valuation + CSV export**

---

## Current Status

### Slice 1: Core Master Data - COMPLETED ✅

**Database Tables Created:**
- `products`: `id`, `name`, `type` (enum: 'papier_roll', 'consommable', 'fini'), `gsm`, `flute`, `width`, `min_stock`, `safety_stock`, `avg_cost`, `category_id`, `subcategory_id`, `unit_id`, `paper_roll_type_id`, `timestamps`
- `warehouses`: `id`, `name` (unique), `is_system` (boolean), `timestamps`
- `suppliers`: `id`, `name`, `contact_person`, `phone`, `email`, `timestamps`

**Filament Resources Implemented:**
- ProductResource (with form and table views in French)
- WarehouseResource (with deletion protection for system warehouses)
- SupplierResource (with form and table views in French)

**Sample Data Seeded:**
- 4 warehouses (including PRODUCTION_CONSUMED system warehouse)
- 3 suppliers with full contact details
- 7 products covering all three types with relationships
- 2 admin users (admin@cartonstock.dz / admin123, test@cartonstock.dz / test123)

---

### Slice 2: Stock Storage Structure - COMPLETED ✅

**Critical Architectural Refactor**

**Problem Solved:**
The original design conflated two separate concepts:
- Individual physical rolls with unique EAN-13 barcodes (qty=1 per roll)
- Product specifications that could represent many rolls

**Solution Implemented:**
Three-tier hierarchy for roll management:

1. **PaperRollType** (Attributes): Define paper characteristics (KL, TLB, TLM, FL) with grammage, laise, weight
2. **RollSpecification** (Specifications): Define unique combinations of Product + PaperRollType + Supplier with purchase_price
3. **Roll** (Individual): Track individual rolls with unique EAN-13 code

**Database Tables Created:**
- `units`: `id`, `name` (unique), `symbol` (unique), `description`, `timestamps` 
- `categories`: `id`, `name` (unique), `description`, `timestamps`
- `subcategories`: `id`, `category_id` (FK), `name`, `description`, `timestamps`
- `paper_roll_types`: `id`, `type_code` (unique), `name`, `grammage`, `laise`, `weight`, `description`, `timestamps`
- `stock_levels`: `id`, `product_id` (FK), `warehouse_id` (FK), `qty` (decimal), unique(product_id, warehouse_id), `timestamps`
- `rolls`: `id`, `product_id` (FK), `warehouse_id` (FK), `roll_specification_id` (FK new), `ean_13` (unique), `qty`, `status` (enum), `batch_number`, `received_date`, `timestamps`
- `roll_specifications`: `id`, `product_id` (FK), `paper_roll_type_id` (FK), `supplier_id` (FK nullable), `purchase_price`, `description`, `is_active`, unique(product_id, paper_roll_type_id, supplier_id), `timestamps`
- `receipts`: `id`, `receipt_number` (unique), `supplier_id` (FK), `warehouse_id` (FK), `receipt_date`, `total_amount`, `status` (enum: draft/received/verified), `notes`, `timestamps`
- `receipt_items`: `id`, `receipt_id` (FK), `roll_specification_id` (FK), `qty_received`, `total_price`, `notes`, `timestamps`

**Filament Resources Implemented:**
- UnitResource (Unités) - ✓ Configured
- CategoryResource (Catégories) - ✓ Configured
- SubcategoryResource (Sous-catégories) - ✓ Configured
- PaperRollTypeResource (Types de Rouleau) - ✓ Configured
- StockLevelResource (Niveaux de Stock) - ✓ Configured
- RollResource (Rouleaux) - ✓ Configured with EAN-13 tracking
- RollSpecificationResource (Spécifications de Rouleau) - 🔄 In progress (to be configured)
- ReceiptResource (Réceptions) - 🔄 In progress (to be configured)

**Sample Data Seeded:**
- 4 units (kg, pcs, roll, L)
- 3 categories (Papiers, Consommables, Produits Finis)
- 5 subcategories (Papier KRAFT, Papier BLANC, Adhésifs, Encres, Cartons)
- 4 paper roll types (KL, TLB, TLM, FL) with specifications
- 7 stock levels distributed across warehouses
- 4 sample rolls with EAN-13 barcodes in in_stock status

**Application Status:**
- ✓ Database fully migrated with all relationships
- ✓ All models updated with proper relationships
- ✓ Filament resources created (core resources configured, receipt infrastructure in progress)
- ✓ Sample data visible in admin panel
- 🔄 Receipt UI/workflows to be implemented in Slice 3

---

### Slice 3: Receipts (Stock In) - UPCOMING

**Infrastructure Ready:**
- ✓ RollSpecification model (separates product attributes from inventory)
- ✓ Receipt model (master receipt record)
- ✓ ReceiptItem model (line items per specification)
- ✓ Roll model updated with specification link

**To Be Implemented:**
1. **RollSpecificationResource Configuration**
   - Admin-only resource to define acceptable roll combinations
   - Form: Product select → PaperRollType select → Supplier select → Purchase price input
   - Pre-filtered to show only combinations that make sense for papier_roll products

2. **ReceiptResource Implementation**
   - Main receipt entry point
   - Inline repeater for line items
   - Product selector with quick specification preview
   - Radio button selection for available specifications
   - Qty input for number of rolls
   - Auto-generation of unique EAN-13 codes on receipt confirmation
   - Individual roll creation from receipt items
   - Stock level updates and weighted average cost recalculation

3. **Receipt Workflow**
   - Draft → Received → Verified status progression
   - Roll generation logic on "Received" status
   - Cost calculation and product weighted average update
   - Audit trail of receipt activities

---

## TODO / Blockers

- 🔄 Configure RollSpecificationResource (admin resource for setup)
- 🔄 Configure ReceiptResource (main receipt workflow)
- 🔄 Implement receipt line item repeater UI
- 🔄 Implement EAN-13 code generation logic
- 🔄 Implement roll creation on receipt confirmation
- 🔄 Implement stock level and cost updates

---

## Next Steps

**Immediate (Slice 3 Part 1):**
1. Configure RollSpecificationResource form and table
2. Add sample roll specifications to seeder
3. Verify specifications appear correctly in admin

**Then (Slice 3 Part 2):**
1. Configure ReceiptResource with Receipt form
2. Implement ReceiptItem repeater for line items
3. Add specification selection UI (radio buttons with attributes)
4. Test receipt entry workflow

**Finally (Slice 3 Part 3):**
1. Implement receipt confirmation logic (generate rolls)
2. Implement EAN-13 generation
3. Implement stock level updates
4. Implement cost recalculation
5. Full end-to-end receipt testing

---

## TODO / Blockers

- None currently. Ready to proceed with Slice 2.

---

## Next Step

- Execute **Slice 2: Stock storage structure**
  - Create `stock_levels` table (product_id, warehouse_id, qty)
  - Create `rolls` table for individual roll tracking with EAN-13 barcode
  - Implement views to see stock by warehouse and per roll