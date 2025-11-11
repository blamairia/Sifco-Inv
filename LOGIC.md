# SIFCO-inv Logic & Workflow

This document details the application's architecture and business logic, focusing on the production line integration.

## Data Models & Relationships

### 1. `ProductionLine`
- **Purpose:** Stores manufacturing lines.
- **Fields:** `id`, `name` (e.g., "Fosber Line 1"), `code` (e.g., "FOS-01").

### 2. `Client`
- **Purpose:** Stores B2B recipients for Bon de Sortie operations.
- **Fields:** `code`, `name`, `contact_person`, `email`, `phone`, `mobile`, `tax_number`, address lines, `city`, `country`, `is_active`.
- **Relationships:** `bonSorties()` morph-many, enabling audits per client. Seeder entries (FOSBER INDUSTRIES, MACARBOX AFRICA) are added via `updateOrCreate` to remain idempotent.

### 3. `Product`
- **Purpose:** Extends the product model to classify items and capture dimensional data.
- **Key Fields:**
    - `product_type` (enum: `raw_material`, `semi_finished`, `finished_good`).
    - `sheet_width_mm` / `sheet_length_mm` (nullable decimal, precision 10, scale 2) for sheet/pallet tracking.
- **Rationale:** Type drives service branching, while sheet metrics synchronize pallet receipts and future outbound flows.

### 4. `BonEntree` (Polymorphic Source)
- **Purpose:** To record where goods came from. The original `supplier_id` will be replaced by a polymorphic relationship.
- **New Fields:**
    - `sourceable_type` (string): Stores the source model, e.g., `App\Models\Supplier` or `App\Models\ProductionLine`.
    - `sourceable_id` (unsigned big integer): Stores the ID of the supplier or production line.
- **Future-Proofing:** This allows adding new sources like `CustomerReturn` without further database schema changes.

### 5. `BonSortie` (Polymorphic Destination)
- **Purpose:** To record where goods are going.
- **New Fields:**
    - `destinationable_type` (string): Stores the destination model, e.g., `App\Models\Client` or `App\Models\ProductionLine`.
    - `destinationable_id` (unsigned big integer): Stores the ID of the client or production line.
- **Future-Proofing:** This allows adding new destinations like `WarehouseTransfer` or `InternalConsumption` seamlessly.

## Core Workflows

### Workflow 1: Goods Received from Production
- **UI:** User selects "Production Line" as the source in the `Bon d'Entrée` form, then chooses a specific line (e.g., "Macarbox").
- **Products:** User adds products of type `semi_finished` or `finished_good`.
- **Sheets/Pallets:** Optional palette repeater rows capture `sheet_width_mm`, `sheet_length_mm`, and quantity. Defaults are hydrated from the selected product, and the form guards column checks via `Schema::hasColumn` to avoid SQL errors when migrations lag.
- **Logic (`BonEntreeService`):**
    1. A `BonEntree` record is created with `sourceable_type` = `App\Models\ProductionLine` and `sourceable_id` pointing to the selected line.
    2. The stock level of the entered products is **incremented** in the target warehouse and sheet metrics persist alongside value.
- **Note:** This is treated as the creation of new inventory.

### Workflow 2: Goods Issued to Production or Clients
- **UI:** User selects "Production Line" or "Client B2B" as the destination in the `Bon de Sortie` form. When a polymorphic entity is chosen, the `destination` text is auto-filled to its name; leaving the type blank re-enables manual destination entry.
- **Products:** User adds products of type `raw_material` or `semi_finished`. Sheet repeater parity (planned) will mirror inbound pallets so dimensional data survives the outbound leg.
- **Logic (`BonSortieService`):**
    1. A `BonSortie` record is created with `destinationable_type` set to the selected entity class (`App\Models\ProductionLine` or `App\Models\Client`).
    2. The stock level of the issued products is **decremented** and valued at the stored CUMP snapshot.
- **Note:** This is treated as consumption or client dispatch. Stock is removed from inventory and is not automatically added back elsewhere.

## Filament Forms Guidance (Production Line & Client integration)

- Bon d'Entrée form:
    - Add a Select field `sourceable_type` with options `[\App\Models\Supplier::class => 'Supplier', \App\Models\ProductionLine::class => 'Production Line']`.
    - Make the select reactive and, when `ProductionLine::class` is chosen, show a second Select `sourceable_id` populated with `ProductionLine::pluck('name', 'id')`.
    - When a production line is selected, the `BonEntree` created should store `sourceable_type` and `sourceable_id`. The `BonEntreeService` will treat these the same as supplier-based entries when updating stock.
    - Palette repeater defaults sheet width/length from the selected product and guards database calls with `Schema::hasColumn` checks to avoid runtime errors before migrations run.

- Bon de Sortie form:
    - Add a Select field `destinationable_type` with options `[\App\Models\ProductionLine::class => 'Production Line', \App\Models\Client::class => 'Client B2B']` and a neutral placeholder for free text destinations.
    - Conditionally show `destinationable_id` when a polymorphic entity is chosen, supplying production lines or active clients accordingly.
    - Auto-fill the human-readable `destination` string from the chosen entity name. Leave manual entry enabled when no entity is selected.
    - Sheet repeater parity (TODO) should mirror the Bon d'Entrée configuration so sheet dimensions remain available on outbound documents.

This approach keeps the UI simple, ensures forms remain backward-compatible with suppliers and ad-hoc destinations, and persists polymorphic relations for production lines and clients alike.

## Decoupled by Design (for now)
As requested, the link between a `BonSortie` of raw materials and a `BonEntree` of finished goods is not yet implemented. The system is "disconnected."

- **Future Linking Strategy:** To connect them later, a `bon_sortie_item_id` (nullable) could be added to the `bon_entree_items` table. This would create a direct traceable link from a finished good back to the raw materials consumed, enabling advanced production tracking and efficiency analysis. The current model structure fully supports this future enhancement.

## Metrics & Reporting Foundation

- **Production Output per Line:** aggregate `bon_entree_items.quantity` (and value) where the parent `bon_entree.sourceable_type = App\Models\ProductionLine`. Filter by `sourceable_id` to get metrics for FOSBER, MACARBOX, ETERNA, or CURIONI individually.
- **Consumption per Line:** aggregate `bon_sortie_items.quantity` where `bon_sortie.destinationable_type = App\Models\ProductionLine`. This captures raw/semi-finished materials issued to a given line.
- **Dispatch per Client:** aggregate `bon_sortie_items.quantity` and valuation where `bon_sortie.destinationable_type = App\Models\Client` to track outbound volume per customer.
- **Future Enhancements:** the `metadata` JSON column on `production_lines` can store machine KPIs (shift, crew, speed). The polymorphic relationships allow joining receipts and issues for production dashboards once linking logic is introduced.
