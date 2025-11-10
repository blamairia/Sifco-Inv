> **MySQL vs SQLite:** Raw `UPDATE ... JOIN ...` statements execute in production (MySQL). Test suite relies on SQLite, so equivalent correlated subqueries are required in migrations/service seeds to avoid syntax errors.
# 📋 SIFCO PROCEDURE MAPPING – Code Implementation Guide

**Status:** REFERENCE  
**Version:** 1.0  
**Date:** 2025-10-30

---

## 🎯 Purpose

This document maps each SIFCO procedure (Bon de réception, Bon d'entrée, Bon de sortie, Bon de transfert, Bon de réintégration, Avis de rupture) to:
- Database tables
- Filament resources
- Workflow steps
- Validation rules

---

## 🔴 PROCEDURE A: ENTRÉES (Reception & Entry)

### Step 1: Réception (Supplier Delivery)

**SIFCO Reference:**
> Avant de procéder à l'établissement du bon d'entrée en stock, le gestionnaire des stocks doit disposer de la totalité des documents du dossier de réception

**Documents Required:**
- Bon de livraison fournisseur
- Bon de réception signé
- Copie du bon de commande
- Facture d'achat
- Factures des frais d'approche (D3, transitaire, transport)

**Database Table:** `bon_receptions`

**Filament Resource:** `BonReceptionResource`

**Fields:**
```
bon_number → Auto-generated: "BREC-{YMMDD}-{seq}"
supplier_id → Dropdown, required
delivery_note_ref → Fournisseur bon de livraison reference
purchase_order_ref → Internal PO reference
receipt_date → Date, required
conformity_issues → JSON: {missing, surplus, damaged, other}
status → {received, verified, conformity_issue, rejected}
verified_by → Magasinier user
notes → Observations
```

**Validation:**
- ✓ Supplier must exist
- ✓ All 5 documents checked in notes field
- ✓ If conformity issues detected → status = 'conformity_issue'
- ✓ Cannot proceed to Bon d'entrée if rejected

**Expected Result:**
- BON_RECEPTION created with status='received'
- Ready for Magasinier verification & physical placement

---

### Step 2: Vérification (Warehouse Receipt Check)

**SIFCO Reference:**
> Le magasinier vérifie la livraison en comparant le bon de livraison/liste de colisage avec le bon de commande. 

**Action:**
- Magasinier compares BON_RECEPTION with delivery note
- Verifies quantities match
- Checks for damage/missing items
- **Signs off on BON_RECEPTION** → status = 'verified'

**Database Action:**
```sql
UPDATE bon_receptions 
SET verified_by_id = ?, 
    verified_at = NOW(),
    status = 'verified'
WHERE id = ?
```

**Expected Result:**
- BON_RECEPTION now verified ✓
- Magasinier moves physical goods to storage zones
- Ready for entry to system (Bon d'entrée)

---

### Step 3: Enregistrement / Entrée en Stock (System Entry)

**SIFCO Reference:**
> Le gestionnaire des stocks enregistre le bon d'entrée dans le logiciel de gestion... La valorisation des entrées en stocks s'effectue au coût d'achat.

**Action:**
- Gestionnaire creates BON_ENTREE (system entry)
- Includes all costs: achat + frais d'approche

**Database Tables:**
- `bon_entrees` (master)
- `bon_entree_items` (line items)
- `rolls` (for bobines)
- `stock_quantities` (updated)
- `stock_movements` (created)

**Filament Resource:** `BonEntreeResource`

**Workflow (Two-Step Validation):**
1.  **Création (Statut: `draft`)**
    *   User fills in supplier, warehouse, and adds items to two separate repeaters:
  *   **Bobines:** For products where `is_roll` = true. User enters `ean_13`, `batch_number`, `weight_kg`, and the new `length_m` for each bobine. Quantity is always 1.
        *   **Produits:** For standard products. User enters `qty_entered`.
    *   User enters `frais_approche`.
    *   The bon is saved as a `draft`.

2.  **Validation (Statut: `draft` → `pending`)**
    *   User clicks the "Valider" action.
    *   The `BonEntreeService::validate()` method is called.
    *   **Logic:**
        *   The `frais_approche` are distributed proportionally across all items (bobines and produits).
        *   The `price_ttc` of each `bon_entree_item` is updated to include its share of the fees.
        *   The bon status is updated to `pending`.
    *   The bon is now locked for editing and ready for final reception.

3.  **Réception (Statut: `pending` → `received`)**
    *   User clicks the "Recevoir" action.
    *   The `BonEntreeService::receive()` method is called.
    *   **Logic for each `bon_entree_item`:**
        *   **If `item_type` is 'bobine':**
            1.  A new `Roll` record is created using the `ean_13`, `batch_number`, captured `weight_kg`, and `length_m` from the item.
            2.  The new `roll_id` is saved back to the `bon_entree_item`.
            3.  `CumpCalculator` calculates the new CUMP for the product.
            4.  A `StockMovement` is created for the entry of 1 unit with both weight (kg) and length (m) attributes captured in metadata.
            5.  The `StockQuantity` for the product/warehouse is updated (quantity incremented by 1, CUMP updated, metre totals adjusted).
        *   **If `item_type` is 'product':**
            1.  `CumpCalculator` calculates the new CUMP.
            2.  A `StockMovement` is created for the entry of `qty_entered`.
            3.  The `StockQuantity` is updated (quantity incremented, CUMP updated).
    *   The bon status is updated to `received`.

**Expected Result:**
- `Rolls` are created for each bobine.
- `StockQuantity` is updated for all products.
- `StockMovement` provides an audit trail for the entry.
- The `BonEntree` is finalized.
- `stock_movements` (ledger)
- `rolls` (physical inventory)
- `stock_quantities` (aggregated)

**Bon d'Entrée Form (Filament):**
- `length_m` per bobine is required once metre tracking is deployed; display helper text clarifying measurement expectations.

```
bon_number → Auto: "BENT-{YMMDD}-{seq}"
bon_reception_id → Lookup (must be verified)
warehouse_id → Dropdown, required
receipt_date → Date
status → 'draft' initially

Line Items (Repeater):
├─ product_id → Dropdown
├─ qty_entered → Decimal, required
├─ price_ht → Unit price before fees
├─ frais_approche_allocation → Calculated share of total fees
└─ price_ttc → auto = price_ht + (frais_approche_allocation / qty_entered)

Total calculations:
├─ total_amount_ht → Sum of (qty × price_ht)
├─ frais_approche → Line for total fees (transport, D3, etc.)
└─ total_amount_ttc → total_ht + frais_approche
```

**Validation:**
- ✓ BON_RECEPTION must exist and be verified
- ✓ All items must map to BON_RECEPTION or be additions
- ✓ Qty must be positive
- ✓ Prices must include VAT (TTC)

**On BON_ENTREE Confirmation (status='received'):**

1. **Create stock_movement(s):**
```
INSERT INTO stock_movements
  (movement_number, product_id, warehouse_from_id, warehouse_to_id, movement_type, 
   qty_moved, cump_at_movement, value_moved, status, reference_number, user_id, performed_at, 
   roll_weight_delta_kg, roll_length_delta_m)
VALUES
  ('SMOV-{YMMDD}-{seq}', product_id, NULL, warehouse_id, 'RECEPTION',
   qty_entered, new_cump, qty_entered * new_cump, 'confirmed', bon_entree_id, user_id, NOW(),
   weight_entered_kg, length_entered_m)
```

2. **Calculate new CUMP (Coût Unitaire Moyen Pondéré):**
```
new_cump = (old_qty × old_cump + new_qty × price_ttc) / (old_qty + new_qty)
```

3. **Update stock_quantities:**
```
INSERT INTO stock_quantities (product_id, warehouse_id, total_qty, cump_snapshot, last_movement_id)
VALUES (product_id, warehouse_id, new_qty, new_cump, movement_id)
ON DUPLICATE KEY UPDATE
  total_qty = total_qty + qty_entered,
  cump_snapshot = new_cump,
  last_movement_id = movement_id
```

4. **Create Roll records (1 per unit or per batch) with weight and length:**
```
For each roll in bon_entree_item:
  INSERT INTO rolls 
    (product_id, warehouse_id, ean_13, batch_number, received_date, 
     received_from_movement_id, status, weight_kg, length_m)
  VALUES 
    (product_id, warehouse_id, manual_ean_13, batch_ref, receipt_date, movement_id, 'in_stock',
     weight_kg_from_item, length_m_from_item)
```

5. **Log Roll Lifecycle Event:**
```
INSERT INTO roll_lifecycle_events
  (roll_id, event_type, warehouse_from_id, warehouse_to_id, 
   weight_before_kg, weight_after_kg, weight_delta_kg,
   length_before_m, length_after_m, length_delta_m,
   stock_movement_id, bon_entree_item_id, performed_at)
VALUES
  (roll_id, 'reception', NULL, warehouse_id,
   0, weight_kg, weight_kg,
   0, length_m, length_m,
   movement_id, bon_entree_item_id, NOW())
```

6. **Link BON_RECEPTION to BON_ENTREE:**
```
UPDATE bon_receptions 
SET bon_entree_id = bon_entree.id
WHERE id = bon_entree.bon_reception_id
```

**Expected Result:**
- BON_ENTREE status = 'received'
- stock_movements created with RECEPTION type
- stock_quantities updated with new CUMP and total weight/length metrics
- Rolls generated with manual EAN-13 codes, capturing weight_kg and length_m
- Roll lifecycle events logged for audit trail
- Physical goods available in warehouse

---

## 🟠 PROCEDURE B: SORTIES (Warehouse Issues)

### Step 1: Sorties Magasin (Stock Request)

**SIFCO Reference:**
> Le demandeur transmet un bon d'approvisionnement signé au magasinier, ce dernier vérifie le stock, prépare la commande et procède à la mise à disposition.

**Action:**
- Department submits BPA (bon d'approvisionnement)
- Magasinier verifies availability
- Magasinier creates BON_SORTIE (system record)

**Database Table:** `bon_sorties`

**Filament Resource:** `BonSortieResource`

**Form:**
```
bon_number → Auto: "BSRT-{YMMDD}-{seq}"
warehouse_id → Dropdown (source warehouse)
issued_date → Date
destination → Text (e.g., "Production", department name)
status → 'draft' initially

Line Items (Repeater):
├─ product_id → Dropdown
├─ qty_issued → Decimal, required
├─ cump_at_issue → Auto-lookup from stock_quantities
└─ value_issued → qty_issued × cump_at_issue
```

**Validation:**
- ✓ Warehouse must exist and not be system-only
- ✓ Product qty available must be ≥ qty_issued
- ✓ Cannot issue from PRODUCTION_CONSUMED warehouse

**On BON_SORTIE Confirmation (status='confirmed'):**

1. **Create stock_movement:**
```
INSERT INTO stock_movements
  (movement_number, product_id, warehouse_from_id, warehouse_to_id, movement_type,
   qty_moved, cump_at_movement, value_moved, status, reference_number, user_id, performed_at, metadata_json)
VALUES
  ('SMOV-{YMMDD}-{seq}', product_id, warehouse_id, NULL, 'ISSUE',
   qty_issued, cump_snapshot, qty_issued * cump_snapshot, 'confirmed', bon_sortie_id, user_id, NOW(), JSON_OBJECT('weight_kg', weight_issued_kg, 'length_m', length_issued_m))
```

2. **Update stock_quantities:**
```
UPDATE stock_quantities 
SET total_qty = total_qty - qty_issued,
    last_movement_id = movement_id
WHERE product_id = ? AND warehouse_id = ?
```

3. **Mark Rolls as consumed:**
```
UPDATE rolls
SET status = 'consumed'
WHERE product_id = ? AND warehouse_id = ?
LIMIT qty_issued  ← Take oldest rolls first (FIFO)
```
→ Persist per-roll metre deltas via `RollAdjustmentService` or equivalent event hooks; avoid widening base roll table until design confirmed.

**Expected Result:**
- BON_SORTIE confirmed
- stock_movements created with ISSUE type
- stock_quantities decremented
- Rolls marked as consumed
- Goods physically removed from warehouse

---

### Step 2: Enregistrement Électronique (System Update)

**SIFCO Reference:**
> Le gestionnaire des stocks met à jour tous les mouvements de sortie (consommations) des stocks sur le logiciel.

**Action:**
- Gestionnaire views all BON_SORTIES with status='confirmed'
- System automatically reflects in reports
- No additional manual step needed (handled by confirmation step)

---

### Step 3: Réapprovisionnement (Reorder Trigger)

**SIFCO Reference:**
> Le gestionnaire des stocks édite l'état des stocks avec observations et le transmet au responsable approvisionnements.

**Action:**
- System detects low stock (qty < min_stock or safety_stock)
- Generates AVIS_DE_RUPTURE (low stock alert)
- Notifies responsible parties

**Implemented in:** LOW-STOCK ALERTS section below

---

## 🟡 PROCEDURE C: TRANSFERTS (Inter-Warehouse)

**SIFCO Reference:** (Procedure Annexe – Bon de Transfert)

**Action:**
- Warehouse A needs to transfer qty to Warehouse B
- Magasinier creates BON_TRANSFERT

**Database Table:** `bon_transferts`

**Filament Resource:** `BonTransfertResource`

**Form:**
```
bon_number → Auto: "BTRN-{YMMDD}-{seq}"
warehouse_from_id → Source warehouse
warehouse_to_id → Destination warehouse
transfer_date → Date
status → 'draft' initially

Line Items (Repeater):
├─ product_id → Dropdown
├─ qty_transferred → Decimal
├─ cump_at_transfer → Auto-lookup from stock_quantities (source)
└─ value_transferred → qty_transferred × cump_at_transfer
```

**Validation:**
- ✓ warehouse_from ≠ warehouse_to
- ✓ warehouse_from must have sufficient qty
- ✓ Neither warehouse can be system-only

**On BON_TRANSFERT Confirmation (status='in_transit'):**

1. **Create 2 stock_movements (linked):**

```sql
-- OUT movement
INSERT INTO stock_movements 
  (movement_number, product_id, warehouse_from_id, warehouse_to_id, movement_type,
   qty_moved, cump_at_movement, status, reference_number, user_id, performed_at, metadata_json)
VALUES ('SMOV-{seq}', product_id, warehouse_from, NULL, 'TRANSFER_OUT',
  qty_transferred, cump_snapshot, 'confirmed', bon_transfert_id, user_id, NOW(), JSON_OBJECT('weight_kg', weight_out_kg, 'length_m', length_out_m));

-- IN movement (pending until received)
INSERT INTO stock_movements 
  (movement_number, product_id, warehouse_from_id, warehouse_to_id, movement_type,
   qty_moved, cump_at_movement, status, reference_number, user_id, performed_at, metadata_json)
VALUES ('SMOV-{seq}', product_id, warehouse_from, warehouse_to, 'TRANSFER_IN',
  qty_transferred, cump_snapshot, 'pending', bon_transfert_id, user_id, NOW(), JSON_OBJECT('weight_kg', weight_out_kg, 'length_m', length_out_m));
```

2. **Decrement source warehouse:**
```
UPDATE stock_quantities
SET total_qty = total_qty - qty_transferred
WHERE product_id = ? AND warehouse_id = warehouse_from
```

3. **Move Rolls:**
```
UPDATE rolls
SET warehouse_id = warehouse_to,
    received_from_movement_id = transfer_in_movement_id
WHERE product_id = ? AND warehouse_id = warehouse_from
LIMIT qty_transferred
```

**On BON_TRANSFERT Reception (status='received'):**

1. **Confirm IN movement:**
```
UPDATE stock_movements
SET status = 'confirmed'
WHERE movement_type = 'TRANSFER_IN' 
  AND reference_number = bon_transfert_id
```

2. **Increment destination warehouse:**
```
UPDATE stock_quantities
SET total_qty = total_qty + qty_transferred
WHERE product_id = ? AND warehouse_id = warehouse_to
```

**Expected Result:**
- Qty decremented at source
- Qty incremented at destination
- CUMP preserved during transfer
- Rolls moved with full traceability

---

## 🟢 PROCEDURE D: RÉINTÉGRATION (Returns)

**SIFCO Reference (Annexe):**
> L'utilisateur présente le bon d'approvisionnement de l'article retourné au magasinier.
> Le gestionnaire des stocks valorise la réintégration sur la base du CUMP de la date de sortie.

**Action:**
- Department returns unused goods
- Magasinier verifies item condition
- Gestionnaire records return with original CUMP

**Database Table:** `bon_reintegrations`

**Filament Resource:** `BonReintegrationResource`

**Form:**
```
bon_number → Auto: "BRIN-{YMMDD}-{seq}"
bon_sortie_id → Lookup (original issue)
warehouse_id → Return destination
return_date → Date
physical_condition → Dropdown: {unopened, slight_damage, major_damage}
status → 'draft' initially

Line Items (Repeater):
├─ product_id → Auto-fetch from bon_sortie (read-only)
├─ qty_returned → Decimal
├─ cump_at_return → Auto-fetch from stock_movements (original ISSUE)
└─ value_returned → qty_returned × cump_at_return
```

**Validation:**
- ✓ BON_SORTIE must exist and be confirmed
- ✓ qty_returned ≤ qty_issued in original BON_SORTIE
- ✓ physical_condition must be specified
- ✓ Cannot return from PRODUCTION_CONSUMED

**On BON_REINTEGRATION Confirmation (status='confirmed'):**

1. **Create stock_movement (RETURN type):**
```
INSERT INTO stock_movements
  (movement_number, product_id, warehouse_from_id, warehouse_to_id, movement_type,
   qty_moved, cump_at_movement, value_moved, status, reference_number, user_id, performed_at)
VALUES
  ('SMOV-{seq}', product_id, NULL, warehouse_id, 'RETURN',
   qty_returned, cump_at_return, qty_returned * cump_at_return, 'confirmed', bon_reintegration_id, user_id, NOW())
```

2. **Update stock_quantities:**
```
UPDATE stock_quantities
SET total_qty = total_qty + qty_returned
WHERE product_id = ? AND warehouse_id = ?
```

3. **Restore Rolls:**
```
INSERT INTO rolls (product_id, warehouse_id, ean_13, status, received_from_movement_id)
SELECT product_id, ?, auto_ean_13(), 'in_stock', movement_id
FROM bon_reintegration_items
WHERE bon_reintegration_id = ?
LIMIT qty_returned
```

**Expected Result:**
- Qty restored to warehouse
- Valuation uses original CUMP (preserved in stock_movements)
- Rolls restored to in_stock status
- Full audit trail maintained

---

## 🔵 PROCEDURE E: AVIS DE RUPTURE (Low Stock Alerts)

**SIFCO Reference (Annexe 2):**
> Nous vous informons que le stock minimum/stock de sécurité est atteint pour les articles

**Trigger Condition:**
```
stock_quantities.total_qty < product.min_stock
   OR
stock_quantities.total_qty < product.safety_stock
```

**Database Table:** `low_stock_alerts`

**Filament Resource:** `LowStockAlertResource` (Read-only admin view)

**Auto-Generation:**
```php
// After every stock_movement confirmation
$product = $movement->product;
$quantities = $product->stockQuantities; // All warehouses

foreach ($quantities as $qty) {
    if ($qty->total_qty < $product->min_stock 
        || $qty->total_qty < $product->safety_stock) {
        
        LowStockAlert::create([
            'product_id' => $product->id,
            'warehouse_id' => $qty->warehouse_id,
            'current_qty' => $qty->total_qty,
            'min_stock' => $product->min_stock,
            'safety_stock' => $product->safety_stock,
            'alert_type' => $qty->total_qty < $product->min_stock 
                ? 'min_stock_reached' 
                : 'safety_stock_reached',
        ]);
    }
}
```

**Alert Display:**
- Dashboard widget: Red banner if ANY alert exists
- Dedicated resource page: List all active alerts
- Notification email to gestionnaire des stocks
- Print option to generate physical "Avis de Rupture" form

**Alert Acknowledgment:**
- Gestionnaire clicks "Reconnaître" (Acknowledge)
- Sets `is_acknowledged = true`
- Adds reorder request (qty + date)

**Expected Result:**
- Alerts generated automatically
- No manual tracking needed
- Clear visibility for reordering

---

## 🔶 PROCEDURE F: VALORISATION (Valuation)

**SIFCO Reference:**
> La valorisation des entrées en stocks s'effectue au coût d'achat. Le coût d'achat est constitué du prix d'achat auquel sont additionnés les frais d'approches.

**Implementation:**

**CUMP Calculation:**
```
CUMP = (old_qty × old_cump + new_qty × new_price_ttc) / (old_qty + new_qty)
```

**Stored In:**
- `stock_quantities.cump_snapshot` ← Current CUMP at (product, warehouse)
- `stock_movements.cump_at_movement` ← Historical CUMP at time of movement

**Valuation Report:**
```
Per warehouse + product:
├─ Total quantity
├─ Average cost (CUMP)
├─ Total value = qty × CUMP
└─ Last updated
```

**CSV Export:**
```
product_code, product_name, warehouse, qty, avg_cost, total_value
```

---

## 📊 Quick Reference: Movement Types

| Movement Type | From | To | Triggers | CUMP |
|---------------|------|-----|----------|------|
| RECEPTION | Supplier | Warehouse | BON_ENTREE confirmed | Recalc |
| ISSUE | Warehouse | NULL | BON_SORTIE confirmed | Snapshot |
| TRANSFER | Warehouse A | Warehouse B | BON_TRANSFERT confirmed | Preserved |
| RETURN | NULL | Warehouse | BON_REINTEGRATION confirmed | Snapshot (from original) |
| ADJUSTMENT | Warehouse | Warehouse | STOCK_ADJUSTMENT confirmed | Adjusted |

---

## ✅ Implementation Checklist

- [ ] Create all `bon_*` tables
- [ ] Create `stock_movements` table
- [ ] Create `stock_adjustments`, `low_stock_alerts` tables
- [ ] Create models for each bon_* table
- [ ] Create Filament resources for each bon_* table
- [ ] Implement BON_ENTREE workflow (with CUMP calculation)
- [ ] Implement BON_SORTIE workflow (with Rolls update)
- [ ] Implement BON_TRANSFERT workflow (dual movements)
- [ ] Implement BON_REINTEGRATION workflow (return CUMP)
- [ ] Implement low-stock alert auto-generation
- [ ] Write tests for CUMP calculations
- [ ] Write tests for movement flows
- [ ] Create user manual (FR) for each procedure
- [ ] Train staff on new workflow
- [ ] Deploy to production

---

## 🎓 User Training (French)

### Pour Magasiniers:
1. **Réception:** Verify BON_RECEPTION, sign off, move goods
2. **Sortie:** Check BON_SORTIE, prepare goods, sign
3. **Transfert:** Create BON_TRANSFERT, move goods between warehouses

### Pour Gestionnaire des Stocks:
1. **Entrée:** Collect all documents, create BON_ENTREE with frais d'approche
2. **Valorisation:** System auto-calculates CUMP
3. **Ruptures:** Review low-stock alerts, trigger reorders

### Pour Direction:
1. **Dashboard:** View stock levels, movements, valuations
2. **Reports:** Export CSV for accounting
3. **Alerts:** Review reorder requests

---

**Last Updated:** 2025-10-30  
**Version:** 1.0  
**Status:** Reference Document (Implement per this specification)
