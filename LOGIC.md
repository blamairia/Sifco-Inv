# SIFCO-inv Logic & Workflow

This document details the application's architecture and business logic, focusing on the production line integration.

## Data Models & Relationships

### 1. `ProductionLine`
- **Purpose:** Stores manufacturing lines.
- **Fields:** `id`, `name` (e.g., "Fosber Line 1"), `code` (e.g., "FOS-01").

### 2. `Product`
- **Purpose:** Extends the product model to classify items.
- **New Field:** `product_type` (enum: `raw_material`, `semi_finished`, `finished_good`).
- **Rationale:** This classification is key for future logic, such as defining bills of materials where semi-finished products are consumed to create finished goods.

### 3. `BonEntree` (Polymorphic Source)
- **Purpose:** To record where goods came from. The original `supplier_id` will be replaced by a polymorphic relationship.
- **New Fields:**
    - `sourceable_type` (string): Stores the source model, e.g., `App\Models\Supplier` or `App\Models\ProductionLine`.
    - `sourceable_id` (unsigned big integer): Stores the ID of the supplier or production line.
- **Future-Proofing:** This allows adding new sources like `CustomerReturn` without further database schema changes.

### 4. `BonSortie` (Polymorphic Destination)
- **Purpose:** To record where goods are going.
- **New Fields:**
    - `destinationable_type` (string): Stores the destination model, e.g., `App\Models\Client` or `App\Models\ProductionLine`.
    - `destinationable_id` (unsigned big integer): Stores the ID of the client or production line.
- **Future-Proofing:** This allows adding new destinations like `WarehouseTransfer` or `InternalConsumption` seamlessly.

## Core Workflows

### Workflow 1: Goods Received from Production
- **UI:** User selects "Production Line" as the source in the `Bon d'Entrée` form, then chooses a specific line (e.g., "Macarbox").
- **Products:** User adds products of type `semi_finished` or `finished_good`.
- **Logic (`BonEntreeService`):**
    1. A `BonEntree` record is created with `sourceable_type` = `App\Models\ProductionLine` and `sourceable_id` pointing to the selected line.
    2. The stock level of the entered products is **incremented** in the target warehouse.
- **Note:** This is treated as the creation of new inventory.

### Workflow 2: Goods Issued to Production
- **UI:** User selects "Production Line" as the destination in the `Bon de Sortie` form.
- **Products:** User adds products of type `raw_material` or `semi_finished`.
- **Logic (`BonSortieService`):**
    1. A `BonSortie` record is created with `destinationable_type` = `App\Models\ProductionLine`.
    2. The stock level of the issued products is **decremented**.
- **Note:** This is treated as consumption. The stock is removed from inventory and is not automatically added back elsewhere.

## Filament Forms Guidance (Production Line integration)

- Bon d'Entrée form:
    - Add a Select field `sourceable_type` with options `[\App\Models\Supplier::class => 'Supplier', \App\Models\ProductionLine::class => 'Production Line']`.
    - Make the select reactive and, when `ProductionLine::class` is chosen, show a second Select `sourceable_id` populated with `ProductionLine::pluck('name', 'id')`.
    - When a production line is selected, the `BonEntree` created should store `sourceable_type` and `sourceable_id`. The `BonEntreeService` will treat these the same as supplier-based entries when updating stock.

- Bon de Sortie form:
    - Add a Select field `destinationable_type` with options `[\App\Models\ProductionLine::class => 'Production Line', null => 'Free / External']`.
    - Conditionally show `destinationable_id` when `ProductionLine::class` is selected and auto-fill the human-readable `destination` string from the `ProductionLine->name` when needed (service auto-fills if blank).

This approach keeps the UI simple, ensures forms are backward-compatible with existing suppliers/clients, and persists polymorphic relations for accurate reporting.

## Decoupled by Design (for now)
As requested, the link between a `BonSortie` of raw materials and a `BonEntree` of finished goods is not yet implemented. The system is "disconnected."

- **Future Linking Strategy:** To connect them later, a `bon_sortie_item_id` (nullable) could be added to the `bon_entree_items` table. This would create a direct traceable link from a finished good back to the raw materials consumed, enabling advanced production tracking and efficiency analysis. The current model structure fully supports this future enhancement.

## Metrics & Reporting Foundation

- **Production Output per Line:** aggregate `bon_entree_items.quantity` (and value) where the parent `bon_entree.sourceable_type = App\Models\ProductionLine`. Filter by `sourceable_id` to get metrics for FOSBER, MACARBOX, ETERNA, or CURIONI individually.
- **Consumption per Line:** aggregate `bon_sortie_items.quantity` where `bon_sortie.destinationable_type = App\Models\ProductionLine`. This captures raw/semi-finished materials issued to a given line.
- **Future Enhancements:** the `metadata` JSON column on `production_lines` can store machine KPIs (shift, crew, speed). The polymorphic relationships allow joining receipts and issues for production dashboards once linking logic is introduced.
