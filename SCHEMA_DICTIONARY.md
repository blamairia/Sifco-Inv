# 📖 SCHEMA DICTIONARY – Field Reference v2.0

**Purpose:** Quick lookup for all table fields, types, constraints, and purpose.  
**Version:** 1.0  
**Last Updated:** 2025-10-30

---

## ✅ Master Data Tables

### `products`
```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,                    -- SKU/Reference code
    name VARCHAR(255) NOT NULL,
    type ENUM('papier_roll', 'consommable', 'fini') NOT NULL,
    description TEXT NULL,
    physical_attributes JSON NULL,              -- {gsm, flute, width, etc.}
    unit_id BIGINT NOT NULL FOREIGN KEY,        -- UoM: roll, pcs, kg, etc.
    min_stock DECIMAL(15,2) DEFAULT 0,          -- Minimum stock level
    safety_stock DECIMAL(15,2) DEFAULT 0,       -- Safety stock threshold
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose | Example |
|-------|------|---------|---------|
| `id` | BIGINT | Primary key | 1 |
| `code` | VARCHAR(50) UNIQUE | Internal SKU | "SKU-001" |
| `name` | VARCHAR(255) | Product name | "A4 80gsm Paper" |
| `type` | ENUM | Category | papier_roll \| consommable \| fini |
| `description` | TEXT | Long description | "Premium white paper" |
| `physical_attributes` | JSON | Dynamic attributes | `{"gsm": 80, "flute": "C"}` |
| `unit_id` | BIGINT FK | Unit of measure | 1 (roll) |
| `min_stock` | DECIMAL(15,2) | Min qty before alert | 100 |
| `safety_stock` | DECIMAL(15,2) | Safety threshold | 50 |
| `is_active` | BOOLEAN | Active/inactive | true |
| `created_at` | TIMESTAMP | Creation date | 2025-10-30 10:00:00 |
| `updated_at` | TIMESTAMP | Last update | 2025-10-30 10:00:00 |

---

### `product_category` (Many-to-Many)
```sql
CREATE TABLE product_category (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL FOREIGN KEY,
    category_id BIGINT NOT NULL FOREIGN KEY,
    is_primary BOOLEAN DEFAULT false,           -- Primary category for quick access
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(product_id, category_id)
);
```

| Field | Type | Purpose | Notes |
|-------|------|---------|-------|
| `product_id` | BIGINT FK | Product | |
| `category_id` | BIGINT FK | Category | |
| `is_primary` | BOOLEAN | Main category | For quick lookup |

---

### `categories`
```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose | Example |
|-------|------|---------|---------|
| `name` | VARCHAR(255) UNIQUE | Category name | "Papiers" |
| `description` | TEXT | Description | "Paper products" |

---

### `units` (Units of Measure)
```sql
CREATE TABLE units (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    symbol VARCHAR(10) UNIQUE NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose | Example |
|-------|------|---------|---------|
| `name` | VARCHAR(100) UNIQUE | Full name | "Piece" |
| `symbol` | VARCHAR(10) UNIQUE | Abbreviation | "pcs" |
| `description` | TEXT | Description | "Individual piece" |

---

### `warehouses`
```sql
CREATE TABLE warehouses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_system BOOLEAN DEFAULT false,            -- System warehouses (e.g., PRODUCTION_CONSUMED)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose | Example |
|-------|------|---------|---------|
| `code` | VARCHAR(50) UNIQUE | Warehouse code | "WH-001" |
| `name` | VARCHAR(255) | Warehouse name | "Main Warehouse" |
| `is_system` | BOOLEAN | System warehouse | true for PRODUCTION_CONSUMED |

---

### `suppliers`
```sql
CREATE TABLE suppliers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    address TEXT NULL,
    payment_terms VARCHAR(100) NULL,           -- e.g., "Net 30"
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose | Example |
|-------|------|---------|---------|
| `code` | VARCHAR(50) UNIQUE | Supplier code | "SUP-001" |
| `name` | VARCHAR(255) | Supplier name | "Paper Co. Ltd" |
| `contact_person` | VARCHAR(255) | Contact name | "Ahmed Mansour" |
| `phone` | VARCHAR(20) | Phone | "+213 555 1234" |
| `email` | VARCHAR(100) | Email | "contact@paperco.dz" |
| `address` | TEXT | Address | "Rue de Paris, Alger" |
| `payment_terms` | VARCHAR(100) | Payment terms | "Net 30" |
| `is_active` | BOOLEAN | Active supplier | true |

---

## 📦 Inventory Tables

### `stock_quantities` (Replaces stock_levels)
```sql
CREATE TABLE stock_quantities (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL FOREIGN KEY,
    warehouse_id BIGINT NOT NULL FOREIGN KEY,
    total_qty DECIMAL(15,2) DEFAULT 0,          -- Total on hand
    reserved_qty DECIMAL(15,2) DEFAULT 0,       -- Reserved (future use)
    available_qty DECIMAL(15,2) GENERATED ALWAYS AS (total_qty - reserved_qty),
    cump_snapshot DECIMAL(12,2) DEFAULT 0,      -- Current CUMP (Coût Unitaire Moyen Pondéré)
    last_movement_id BIGINT NULL FOREIGN KEY,   -- Link to last stock_movement
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(product_id, warehouse_id)
);
```

| Field | Type | Purpose | Notes |
|-------|------|---------|-------|
| `product_id` | BIGINT FK | Product | |
| `warehouse_id` | BIGINT FK | Warehouse | |
| `total_qty` | DECIMAL(15,2) | Physical qty | Updated by movements |
| `reserved_qty` | DECIMAL(15,2) | Reserved qty | For future reservations |
| `available_qty` | DECIMAL GENERATED | Available = total - reserved | Calculated field |
| `cump_snapshot` | DECIMAL(12,2) | Current CUMP | Updated on each RECEPTION |
| `last_movement_id` | BIGINT FK | Last movement | For traceability |

---

### `rolls` (Physical Inventory)
```sql
CREATE TABLE rolls (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL FOREIGN KEY,
    warehouse_id BIGINT NOT NULL FOREIGN KEY,
    ean_13 VARCHAR(13) UNIQUE NOT NULL,         -- Barcode (globally unique)
    batch_number VARCHAR(100) NULL,             -- Supplier batch reference
    received_date DATE NOT NULL,
    received_from_movement_id BIGINT NULL FK,   -- Links to stock_movement
    status ENUM('in_stock', 'reserved', 'consumed', 'damaged', 'archived') DEFAULT 'in_stock',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose | Notes |
|-------|------|---------|-------|
| `ean_13` | VARCHAR(13) UNIQUE | Barcode | Never duplicated, globally unique |
| `batch_number` | VARCHAR(100) | Supplier batch | For traceability |
| `received_date` | DATE | When received | |
| `received_from_movement_id` | BIGINT FK | Source movement | RECEPTION that created this |
| `status` | ENUM | Current status | in_stock, consumed, etc. |

---

## 📝 Stock Movement Tables

### `stock_movements` (Audit Trail)
```sql
CREATE TABLE stock_movements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    movement_number VARCHAR(50) UNIQUE NOT NULL, -- "SMOV-{YMMDD}-{seq}"
    product_id BIGINT NOT NULL FOREIGN KEY,
    warehouse_from_id BIGINT NULL FOREIGN KEY,   -- NULL for RECEPTION
    warehouse_to_id BIGINT NULL FOREIGN KEY,     -- NULL for ISSUE
    movement_type ENUM(
        'RECEPTION',           -- Supplier → Warehouse
        'ISSUE',              -- Warehouse → NULL (production)
        'TRANSFER',           -- Warehouse → Warehouse
        'RETURN',             -- NULL → Warehouse (reintegration)
        'ADJUSTMENT'          -- Manual adjustment
    ) NOT NULL,
    qty_moved DECIMAL(15,2) NOT NULL,
    cump_at_movement DECIMAL(12,2) NOT NULL,     -- CUMP snapshot
    value_moved DECIMAL(15,2) NOT NULL,          -- qty × cump
    status ENUM('draft', 'confirmed', 'cancelled') DEFAULT 'draft',
    reference_number VARCHAR(100) NULL,          -- Links to bon_*
    user_id BIGINT NOT NULL FOREIGN KEY,         -- Who performed
    performed_at TIMESTAMP NOT NULL,
    approved_by_id BIGINT NULL FOREIGN KEY,      -- Manager approval
    approved_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(movement_number),
    INDEX(product_id),
    INDEX(status)
);
```

| Field | Type | Purpose | Example |
|-------|------|---------|---------|
| `movement_number` | VARCHAR(50) UNIQUE | Unique ID | "SMOV-20251030-0001" |
| `movement_type` | ENUM | Type of movement | RECEPTION, ISSUE, TRANSFER, RETURN |
| `qty_moved` | DECIMAL(15,2) | Quantity moved | 100 |
| `cump_at_movement` | DECIMAL(12,2) | CUMP snapshot | 25.50 |
| `value_moved` | DECIMAL(15,2) | qty × cump | 2550.00 |
| `status` | ENUM | State | draft, confirmed, cancelled |
| `reference_number` | VARCHAR(100) | Links to bon_* | "BENT-20251030-0001" |

---

## 📋 Procedure Document Tables

### `bon_receptions` (Supplier Delivery)
```sql
CREATE TABLE bon_receptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_number VARCHAR(50) UNIQUE NOT NULL,      -- "BREC-{YMMDD}-{seq}"
    supplier_id BIGINT NOT NULL FOREIGN KEY,
    delivery_note_ref VARCHAR(100) NULL,        -- Fournisseur reference
    purchase_order_ref VARCHAR(100) NULL,       -- PO reference
    receipt_date DATE NOT NULL,
    status ENUM('received', 'verified', 'conformity_issue', 'rejected') DEFAULT 'received',
    verified_by_id BIGINT NULL FOREIGN KEY,     -- Magasinier
    verified_at TIMESTAMP NULL,
    conformity_issues JSON NULL,                -- {missing, surplus, damaged}
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose |
|-------|------|---------|
| `bon_number` | VARCHAR(50) UNIQUE | Bon de réception number |
| `supplier_id` | BIGINT FK | Supplier |
| `status` | ENUM | received, verified, conformity_issue, rejected |
| `conformity_issues` | JSON | Issues encountered |

---

### `bon_entrees` (Stock Entry)
```sql
CREATE TABLE bon_entrees (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_number VARCHAR(50) UNIQUE NOT NULL,      -- "BENT-{YMMDD}-{seq}"
    bon_reception_id BIGINT NOT NULL FOREIGN KEY,
    warehouse_id BIGINT NOT NULL FOREIGN KEY,
    receipt_date DATE NOT NULL,
    status ENUM('draft', 'entered', 'confirmed', 'archived') DEFAULT 'draft',
    entered_by_id BIGINT NOT NULL FOREIGN KEY,   -- Gestionnaire
    entered_at TIMESTAMP NULL,
    total_amount_ht DECIMAL(15,2) DEFAULT 0,     -- Before frais d'approche
    frais_approche DECIMAL(15,2) DEFAULT 0,      -- Transport, D3, fees
    total_amount_ttc DECIMAL(15,2) DEFAULT 0,    -- After frais
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

| Field | Type | Purpose |
|-------|------|---------|
| `bon_number` | VARCHAR(50) UNIQUE | Bon d'entrée number |
| `bon_reception_id` | BIGINT FK | Links to supplier delivery |
| `warehouse_id` | BIGINT FK | Destination warehouse |
| `total_amount_ht` | DECIMAL(15,2) | Before fees |
| `frais_approche` | DECIMAL(15,2) | Transport, D3, transitaire |
| `total_amount_ttc` | DECIMAL(15,2) | After fees (includes VAT) |

---

### `bon_entree_items` (Line Items)
```sql
CREATE TABLE bon_entree_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_entree_id BIGINT NOT NULL FOREIGN KEY,
    product_id BIGINT NOT NULL FOREIGN KEY,
    qty_entered DECIMAL(15,2) NOT NULL,
    price_ht DECIMAL(12,2) NOT NULL,            -- Unit price before fees
    price_ttc DECIMAL(12,2) NOT NULL,           -- Unit price after fees
    line_total_ttc DECIMAL(15,2) NOT NULL,      -- qty × price_ttc
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### `bon_sorties` (Stock Issues)
```sql
CREATE TABLE bon_sorties (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_number VARCHAR(50) UNIQUE NOT NULL,      -- "BSRT-{YMMDD}-{seq}"
    warehouse_id BIGINT NOT NULL FOREIGN KEY,
    issued_date DATE NOT NULL,
    destination VARCHAR(255) NOT NULL,          -- "Production", department, etc.
    status ENUM('draft', 'issued', 'confirmed', 'archived') DEFAULT 'draft',
    issued_by_id BIGINT NOT NULL FOREIGN KEY,    -- Magasinier
    issued_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### `bon_sortie_items` (Line Items)
```sql
CREATE TABLE bon_sortie_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_sortie_id BIGINT NOT NULL FOREIGN KEY,
    product_id BIGINT NOT NULL FOREIGN KEY,
    qty_issued DECIMAL(15,2) NOT NULL,
    cump_at_issue DECIMAL(12,2) NOT NULL,       -- Snapshot for valuation
    value_issued DECIMAL(15,2) NOT NULL,        -- qty × cump
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### `bon_transferts` (Warehouse Transfers)
```sql
CREATE TABLE bon_transferts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_number VARCHAR(50) UNIQUE NOT NULL,      -- "BTRN-{YMMDD}-{seq}"
    warehouse_from_id BIGINT NOT NULL FOREIGN KEY,
    warehouse_to_id BIGINT NOT NULL FOREIGN KEY,
    transfer_date DATE NOT NULL,
    status ENUM('draft', 'in_transit', 'received', 'confirmed', 'archived') DEFAULT 'draft',
    requested_by_id BIGINT NOT NULL FOREIGN KEY,
    transferred_at TIMESTAMP NULL,
    received_at TIMESTAMP NULL,
    received_by_id BIGINT NULL FOREIGN KEY,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### `bon_transfert_items` (Line Items)
```sql
CREATE TABLE bon_transfert_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_transfert_id BIGINT NOT NULL FOREIGN KEY,
    product_id BIGINT NOT NULL FOREIGN KEY,
    qty_transferred DECIMAL(15,2) NOT NULL,
    cump_at_transfer DECIMAL(12,2) NOT NULL,    -- Preserve cost
    value_transferred DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### `bon_reintegrations` (Returns)
```sql
CREATE TABLE bon_reintegrations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_number VARCHAR(50) UNIQUE NOT NULL,      -- "BRIN-{YMMDD}-{seq}"
    bon_sortie_id BIGINT NOT NULL FOREIGN KEY,  -- Original issue
    warehouse_id BIGINT NOT NULL FOREIGN KEY,   -- Return destination
    return_date DATE NOT NULL,
    status ENUM('draft', 'received', 'verified', 'confirmed', 'archived') DEFAULT 'draft',
    verified_by_id BIGINT NULL FOREIGN KEY,
    verified_at TIMESTAMP NULL,
    physical_condition VARCHAR(100) NULL,       -- unopened, slight_damage, etc.
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### `bon_reintegration_items` (Line Items)
```sql
CREATE TABLE bon_reintegration_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bon_reintegration_id BIGINT NOT NULL FOREIGN KEY,
    product_id BIGINT NOT NULL FOREIGN KEY,
    qty_returned DECIMAL(15,2) NOT NULL,
    cump_at_return DECIMAL(12,2) NOT NULL,      -- From original ISSUE
    value_returned DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## ⚙️ Adjustment & Alert Tables

### `stock_adjustments`
```sql
CREATE TABLE stock_adjustments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    adjustment_number VARCHAR(50) UNIQUE NOT NULL,
    product_id BIGINT NOT NULL FOREIGN KEY,
    warehouse_id BIGINT NOT NULL FOREIGN KEY,
    qty_adjustment DECIMAL(15,2) NOT NULL,      -- Positive or negative
    reason ENUM('inventory_count', 'damage', 'loss', 'correction', 'other'),
    adjustment_date DATE NOT NULL,
    status ENUM('draft', 'pending_approval', 'approved', 'archived') DEFAULT 'draft',
    created_by_id BIGINT NOT NULL FOREIGN KEY,
    approved_by_id BIGINT NULL FOREIGN KEY,
    approved_at TIMESTAMP NULL,
    notes TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### `low_stock_alerts`
```sql
CREATE TABLE low_stock_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    alert_number VARCHAR(50) UNIQUE NOT NULL,    -- "ALERT-{YMMDD}-{seq}"
    product_id BIGINT NOT NULL FOREIGN KEY,
    warehouse_id BIGINT NULL FOREIGN KEY,        -- NULL = all warehouses
    current_qty DECIMAL(15,2) NOT NULL,
    min_stock DECIMAL(15,2) NOT NULL,
    safety_stock DECIMAL(15,2) NOT NULL,
    alert_type ENUM('min_stock_reached', 'safety_stock_reached'),
    is_acknowledged BOOLEAN DEFAULT false,
    acknowledged_by_id BIGINT NULL FOREIGN KEY,
    acknowledged_at TIMESTAMP NULL,
    reorder_requested BOOLEAN DEFAULT false,
    reorder_qty DECIMAL(15,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(product_id, is_acknowledged)
);
```

---

## 🔑 Key Constraints

| Constraint | Table | Meaning |
|-----------|-------|---------|
| UNIQUE(product_id, warehouse_id) | stock_quantities | One aggregated qty per product/warehouse |
| UNIQUE(ean_13) | rolls | Each physical roll has unique barcode |
| UNIQUE(product_id, category_id) | product_category | Avoid duplicate assignments |
| UNIQUE(bon_number) | bon_* tables | Each bon has unique number |
| UNIQUE(movement_number) | stock_movements | Each movement uniquely identified |

---

## 🔄 Relationships (Entity Diagram)

```
products ←→ product_category ←→ categories
   ↓
   └─→ stock_quantities (per warehouse)
   │      └─→ stock_movements (audit)
   │         ├─→ rolls (individual physical)
   │         └─→ bon_* documents
   │
   └─→ rolls (direct)
   │      └─→ warehouse
   │
   └─→ suppliers (via bon_receptions)
   │
   └─→ units
```

---

**Last Updated:** 2025-10-30  
**Version:** 1.0
