# CartonStock MVP Plan

## High-Level Scope (SRS v1.0)

- **Core:** Track products, stock levels per warehouse, and individual paper rolls.
- **Features:** Receive materials from suppliers, move stock between warehouses, issue stock to production (via a virtual `PRODUCTION_CONSUMED` warehouse), and perform manual stock adjustments.
- **Reporting:** A dashboard showing key metrics, a low stock alert page, and an inventory valuation view with CSV export.
- **Data:** Includes a suppliers directory and uses weighted average costing.
- **Tech:** Laravel 11 + Filament v4 on Windows/MySQL. UI in French.

---

## Slices

-   [ ] **Slice 1: Core master data** (Products, Warehouses, Suppliers)
-   [ ] **Slice 2: Stock storage structure** (stock_levels, rolls)
-   [ ] **Slice 3: Receipts (stock in)**
-   [ ] **Slice 4: Stock movement**
-   [ ] **Slice 5: Manual adjustments**
-   [ ] **Slice 6: Dashboard & Alerts**
-   [ ] **Slice 7: Valuation + CSV export**

---

## Current Status

-   **Done:** Initial `PLAN.md` created.
-   **TODO / Blockers:** None.

---

## Next Step

-   Execute **Slice 1**: Implement Filament resources and database migrations for Products, Warehouses, and Suppliers. Create a placeholder dashboard.