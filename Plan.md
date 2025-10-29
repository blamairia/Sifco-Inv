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
- [ ] **Slice 2: Stock storage structure** (stock_levels, rolls)
- [ ] **Slice 3: Receipts (stock in)**
- [ ] **Slice 4: Stock movement**
- [ ] **Slice 5: Manual adjustments**
- [ ] **Slice 6: Dashboard & Alerts**
- [ ] **Slice 7: Valuation + CSV export**

---

## Current Status

### Slice 1: Core Master Data - COMPLETED

**Database Tables Created:**
- `products`: `id`, `name`, `type` (enum: 'papier_roll', 'consommable', 'fini'), `gsm`, `flute`, `width`, `min_stock`, `safety_stock`, `avg_cost`, `timestamps`
- `warehouses`: `id`, `name` (unique), `is_system` (boolean), `timestamps`
- `suppliers`: `id`, `name`, `contact_person`, `phone`, `email`, `timestamps`

**Filament Resources Implemented:**
- ProductResource (with form and table views in French)
- WarehouseResource (with deletion protection for system warehouses)
- SupplierResource (with form and table views in French)

**Additional Work:**
- Seeder to automatically create the `PRODUCTION_CONSUMED` system warehouse
- Navigation labels in French for all resources
- Deletion protection for system warehouses (PRODUCTION_CONSUMED cannot be deleted)

**Test Checklist:**
- [ ] Login to Filament admin panel
- [ ] Create a Product (Papier KRAFT 120 GSM)
- [ ] View Products list with all columns
- [ ] Create a Warehouse (TEST_WH1)
- [ ] Verify PRODUCTION_CONSUMED is marked as system warehouse
- [ ] Attempt to delete PRODUCTION_CONSUMED (should be protected)
- [ ] Create a Supplier
- [ ] View all master data in the dashboard

---

## TODO / Blockers

- None currently. Ready to proceed with Slice 2.

---

## Next Step

- Execute **Slice 2: Stock storage structure**
  - Create `stock_levels` table (product_id, warehouse_id, qty)
  - Create `rolls` table for individual roll tracking with EAN-13 barcode
  - Implement views to see stock by warehouse and per roll