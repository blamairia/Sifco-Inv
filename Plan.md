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

**Sample Data Seeded:**
- 4 warehouses (including PRODUCTION_CONSUMED system warehouse)
- 3 suppliers with full contact details
- 7 products covering all three types (papier_roll, consommable, fini)
- 2 admin users (admin@cartonstock.dz / admin123, test@cartonstock.dz / test123)

**Application Status:**
- ✓ Filament admin panel running at `http://127.0.0.1:8000/admin`
- ✓ Sample data visible in all resources
- ✓ Ready for Slice 2 implementation

**Test Results:**
- [x] Login to Filament admin panel
- [x] View Products list with sample data (7 products)
- [x] View Warehouses list with PRODUCTION_CONSUMED marked as system
- [x] View Suppliers list with contact details
- [x] Verify PRODUCTION_CONSUMED is protected from deletion

---

## TODO / Blockers

- None currently. Ready to proceed with Slice 2.

---

## Next Step

- Execute **Slice 2: Stock storage structure**
  - Create `stock_levels` table (product_id, warehouse_id, qty)
  - Create `rolls` table for individual roll tracking with EAN-13 barcode
  - Implement views to see stock by warehouse and per roll