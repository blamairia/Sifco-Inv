# 🎯 Roll Adjustment Analysis - Option 1 Implementation Plan

**Date:** November 5, 2025  
**Context:** Slice 7 - Stock Adjustments for Rolls (Bobines) vs Products  
**Decision:** Dual-form approach (separate Roll Adjustment resource)

---

## 📊 CURRENT SYSTEM UNDERSTANDING

### Roll Data Model
```php
// Roll.php - Individual physical roll tracking
Roll {
    id
    bon_entree_item_id (FK → bon_entree_items, nullable)
    product_id (FK → products)
    warehouse_id (FK → warehouses)
    ean_13 (13 chars, UNIQUE) ← CRITICAL IDENTIFIER
    batch_number (nullable)
    received_date (DATE)
    received_from_movement_id (FK → stock_movements, nullable)
    status: ENUM('in_stock', 'reserved', 'consumed', 'damaged', 'archived')
    notes (TEXT)
    timestamps
}
```

### Key Roll Characteristics
1. **1 Roll = 1 Physical Unit**
   - Each roll has unique EAN-13 barcode
   - Quantity is ALWAYS 1 per roll record
   - Never group multiple rolls in one record

2. **Weight & CUMP via BonEntreeItem**
   - `Roll.weight` (accessor) → `bonEntreeItem.qty_entered`
   - `Roll.cump` (accessor) → `bonEntreeItem.price_ttc`
   - These are **read-only** attributes from the source receipt

3. **Status-Based Tracking**
   - `in_stock` - Available for issuance
   - `reserved` - Allocated but not issued
   - `consumed` - Already used (from Bon de Sortie)
   - `damaged` - Marked as damaged/lost
   - `archived` - Historical/inactive

4. **Roll Creation Process** (from BonEntreeService)
   ```php
   // When receiving a Bon d'Entrée (bobine item):
   Roll::create([
       'bon_entree_item_id' => $item->id,
       'product_id' => $item->product_id,
       'warehouse_id' => $bonEntree->warehouse_id,
       'ean_13' => $item->ean_13,  // Manually entered by user
       'batch_number' => $item->batch_number,
       'received_date' => $bonEntree->received_date,
       'status' => 'in_stock',
   ]);
   ```

---

## 🔴 PROBLEM STATEMENT

### Current StockAdjustment Issues
The existing `StockAdjustment` resource/service only handles:
- ✅ **Products**: Adjust `qty_after` → updates `stock_quantities.total_qty`
- ❌ **Rolls**: Cannot track individual roll changes properly

### What's Missing for Rolls?
1. **Adding Rolls** (Inventory Increase):
   - Need to create new `Roll` records
   - Must generate/input unique EAN-13
   - Must specify batch_number, received_date
   - Must link to product + warehouse
   - ⚠️ **CRITICAL**: Cannot get weight/CUMP from BonEntreeItem (no receipt exists!)

2. **Removing Rolls** (Inventory Decrease):
   - Need to select specific roll(s) by EAN-13
   - Change status: `in_stock` → `consumed` or `damaged`
   - Must specify reason (damage, loss, theft, etc.)

3. **Damaging Rolls** (Mark as damaged):
   - Select roll(s) by EAN-13
   - Change status: `in_stock` → `damaged`
   - Keep in inventory but unavailable

---

## ✅ OPTION 1: DUAL-FORM APPROACH (RECOMMENDED)

### Architecture Overview
```
Stock Adjustments
├── StockAdjustmentResource (existing)
│   └── For: Normal products (ramettes, supplies)
│   └── Logic: Adjust qty_after → update stock_quantities
│
└── RollAdjustmentResource (NEW)
    ├── For: Rolls (bobines) only
    ├── Sub-workflows:
    │   ├── Add Rolls (create new Roll records)
    │   ├── Remove Rolls (status: in_stock → consumed/archived)
    │   └── Damage Rolls (status: in_stock → damaged)
    └── Logic: Individual roll tracking + stock_quantities sync
```

---

## 🏗️ PROPOSED IMPLEMENTATION

### 1. Database Structure

#### Option A: Extend StockAdjustment (Simpler)
```php
// Add to existing stock_adjustments migration
Schema::table('stock_adjustments', function (Blueprint $table) {
    $table->enum('adjustment_scope', ['product', 'roll'])->default('product')->after('adjustment_type');
    $table->foreignId('roll_id')->nullable()->after('product_id')->constrained('rolls')->nullOnDelete();
});
```

**Usage:**
- `adjustment_scope = 'product'` → Normal qty adjustment
- `adjustment_scope = 'roll'` → Individual roll operation (roll_id required)

#### Option B: Separate RollAdjustment Table (Cleaner)
```php
Schema::create('roll_adjustments', function (Blueprint $table) {
    $table->id();
    $table->string('adjustment_number')->unique();
    $table->foreignId('roll_id')->constrained('rolls')->cascadeOnDelete();
    $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
    $table->enum('adjustment_type', ['ADD', 'REMOVE', 'DAMAGE', 'RESTORE']);
    $table->enum('previous_status', ['in_stock', 'reserved', 'consumed', 'damaged', 'archived'])->nullable();
    $table->enum('new_status', ['in_stock', 'reserved', 'consumed', 'damaged', 'archived']);
    $table->text('reason');
    $table->foreignId('adjusted_by')->constrained('users');
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index(['warehouse_id', 'adjustment_type']);
    $table->index('adjusted_by');
});
```

**✅ RECOMMENDATION: Option B - Separate table**
- Cleaner separation of concerns
- Easier to query roll-specific adjustments
- No nullable columns on stock_adjustments
- Different fields needed (status changes vs qty changes)

---

### 2. Roll Operations

#### A. ADD ROLLS (Manual Entry - No Receipt)
**Use Case:** Found physical roll without paperwork, manual inventory addition

**Workflow:**
1. User enters:
   - Product (must have `is_roll = true`)
   - Warehouse
   - EAN-13 (validate uniqueness)
   - Batch number
   - Received date (defaults to today)
   - Weight (manual entry) ⚠️ **PROBLEM: No bon_entree_item!**
   - Reason

2. System creates:
   ```php
   Roll::create([
       'bon_entree_item_id' => null,  // ⚠️ No source receipt
       'product_id' => $productId,
       'warehouse_id' => $warehouseId,
       'ean_13' => $ean13,
       'batch_number' => $batchNumber,
       'received_date' => $receivedDate,
       'status' => 'in_stock',
       'notes' => "Manual adjustment: {$reason}",
   ]);
   ```

3. ⚠️ **CRITICAL ISSUE: Weight & CUMP**
   - Roll model uses accessors: `bonEntreeItem.qty_entered`, `bonEntreeItem.price_ttc`
   - **Without bon_entree_item_id**, weight & CUMP = NULL!
   
   **SOLUTION OPTIONS:**
   
   **Option 1: Add weight/cump columns to rolls table**
   ```php
   Schema::table('rolls', function (Blueprint $table) {
       $table->decimal('weight_kg', 10, 2)->nullable()->after('status');
       $table->decimal('cump_value', 10, 2)->nullable()->after('weight_kg');
   });
   ```
   - Update Roll model accessors to check local columns first:
   ```php
   public function getWeightAttribute() {
       return $this->weight_kg ?? $this->bonEntreeItem?->qty_entered ?? 0;
   }
   
   public function getCumpAttribute() {
       return $this->cump_value ?? $this->bonEntreeItem?->price_ttc ?? 0;
   }
   ```
   
   **Option 2: Create "dummy" BonEntreeItem** (not recommended - breaks audit trail)

#### B. REMOVE ROLLS (Status: in_stock → consumed/archived)
**Use Case:** Roll used without Bon de Sortie, lost, discarded

**Workflow:**
1. User selects:
   - Roll(s) by EAN-13 (multi-select table)
   - Adjustment type: REMOVE
   - Final status: `consumed` or `archived`
   - Reason

2. System updates:
   ```php
   foreach ($selectedRolls as $roll) {
       $roll->update([
           'status' => $newStatus,  // consumed or archived
           'notes' => "Adjusted: {$reason}",
       ]);
       
       // Create stock movement
       StockMovement::create([
           'movement_number' => generateNumber(),
           'movement_type' => 'ADJUSTMENT',
           'product_id' => $roll->product_id,
           'warehouse_from_id' => $roll->warehouse_id,
           'qty_moved' => 1,
           'cump_at_movement' => $roll->cump,
           'value_moved' => $roll->cump,
           'notes' => "Roll removed: {$reason}",
       ]);
       
       // Decrement stock_quantities
       StockQuantity::where('product_id', $roll->product_id)
           ->where('warehouse_id', $roll->warehouse_id)
           ->decrement('total_qty', 1);
   }
   ```

#### C. DAMAGE ROLLS (Status: in_stock → damaged)
**Use Case:** Roll physically damaged, unusable but still tracked

**Workflow:**
- Same as REMOVE but `new_status = 'damaged'`
- Roll stays in database but excluded from available stock
- Does **NOT** decrement stock_quantities (already counted as 0)

#### D. RESTORE ROLLS (Status: damaged → in_stock)
**Use Case:** Roll repaired or damage assessment was wrong

**Workflow:**
- Reverse of DAMAGE
- Change status back to `in_stock`
- Increment stock_quantities

---

### 3. Filament Resource Structure

```php
// RollAdjustmentResource.php
class RollAdjustmentResource extends Resource
{
    protected static ?string $model = RollAdjustment::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion du Stock';
    protected static ?int $navigationSort = 7;
    
    public static function getNavigationLabel(): string
    {
        return 'Ajustements Bobines';
    }
}
```

#### Form Design

**Option: Tabbed Interface**
```php
Tabs::make()
    ->tabs([
        Tab::make('Ajouter Bobines')
            ->schema([
                Select::make('product_id')
                    ->label('Produit (Bobine)')
                    ->relationship('product', 'name', fn($q) => $q->where('is_roll', true))
                    ->required(),
                    
                Select::make('warehouse_id')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->required(),
                    
                TextInput::make('ean_13')
                    ->label('Code EAN-13')
                    ->length(13)
                    ->unique('rolls', 'ean_13')
                    ->required(),
                    
                TextInput::make('batch_number')
                    ->label('Numéro de Lot'),
                    
                DatePicker::make('received_date')
                    ->label('Date de Réception')
                    ->default(now())
                    ->required(),
                    
                TextInput::make('weight_kg')
                    ->label('Poids (kg)')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
                    
                TextInput::make('cump_value')
                    ->label('CUMP (€)')
                    ->numeric()
                    ->step(0.01)
                    ->helperText('Laissez vide pour utiliser le CUMP actuel du produit'),
                    
                Textarea::make('reason')
                    ->label('Raison')
                    ->required(),
            ]),
            
        Tab::make('Retirer Bobines')
            ->schema([
                Select::make('warehouse_id')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->live()
                    ->required(),
                    
                Select::make('product_id')
                    ->label('Produit (Bobine)')
                    ->relationship('product', 'name', fn($q) => $q->where('is_roll', true))
                    ->live()
                    ->required(),
                    
                // Custom table/repeater to select rolls
                CheckboxList::make('roll_ids')
                    ->label('Sélectionner les Bobines')
                    ->options(function (Get $get) {
                        return Roll::where('warehouse_id', $get('warehouse_id'))
                            ->where('product_id', $get('product_id'))
                            ->where('status', 'in_stock')
                            ->get()
                            ->mapWithKeys(fn($roll) => [
                                $roll->id => "{$roll->ean_13} - Lot: {$roll->batch_number} - Poids: {$roll->weight}kg"
                            ]);
                    })
                    ->required(),
                    
                Select::make('new_status')
                    ->label('Nouveau Statut')
                    ->options([
                        'consumed' => 'Consommé',
                        'archived' => 'Archivé',
                    ])
                    ->default('consumed')
                    ->required(),
                    
                Textarea::make('reason')
                    ->label('Raison')
                    ->required(),
            ]),
            
        Tab::make('Endommager Bobines')
            ->schema([
                // Similar to "Retirer" but new_status = 'damaged'
            ]),
            
        Tab::make('Restaurer Bobines')
            ->schema([
                // Similar structure but for damaged → in_stock
            ]),
    ]);
```

---

## 🔧 IMPLEMENTATION CHECKLIST

### Phase 1: Database & Models
- [ ] Create `roll_adjustments` migration (Option B)
- [ ] Add `weight_kg`, `cump_value` columns to `rolls` table
- [ ] Create `RollAdjustment` model with relationships
- [ ] Update `Roll` model accessors for weight/CUMP fallback
- [ ] Add `adjustment_type = 'ADJUSTMENT'` to stock_movements enum (already done)

### Phase 2: Service Class
- [ ] Create `RollAdjustmentService.php`
  - [ ] `addRoll($data)` - Create new roll with manual weight/CUMP
  - [ ] `removeRolls($rollIds, $newStatus, $reason)` - Bulk status change
  - [ ] `damageRolls($rollIds, $reason)` - Mark as damaged
  - [ ] `restoreRolls($rollIds, $reason)` - Restore from damaged
  - [ ] `generateAdjustmentNumber()` - ADJ-ROLL-YYYYMMDD-####

### Phase 3: Filament Resource
- [ ] Create `RollAdjustmentResource`
- [ ] Create tabbed form with 4 workflows
- [ ] Create table with filters (warehouse, adjustment_type, date)
- [ ] Add actions: View details, Revert (if possible)
- [ ] Create pages: List, Create (no Edit - adjustments are immutable)

### Phase 4: Stock Sync Logic
- [ ] Ensure `stock_quantities.total_qty` updates for rolls
- [ ] Create `StockMovement` records for all adjustments
- [ ] Update `LowStockAlert` triggers for roll products

### Phase 5: Testing
- [ ] Test ADD: Create roll without bon_entree_item
- [ ] Test REMOVE: Change status + decrement stock
- [ ] Test DAMAGE: Change status without stock decrement
- [ ] Test RESTORE: Reverse damage adjustment
- [ ] Test validation: EAN-13 uniqueness, roll availability
- [ ] Test CUMP: Verify weight/CUMP fallback logic

---

## ⚠️ CRITICAL DECISIONS NEEDED

### 1. Weight & CUMP Storage
**Question:** How to store weight/CUMP for manually-added rolls without BonEntreeItem?

**Options:**
- ✅ **A: Add columns to rolls table** (weight_kg, cump_value)
  - Pro: Simple, no dummy data
  - Pro: Clear distinction (NULL = from receipt, value = manual)
  - Con: Denormalization (data in 2 places)
  
- ❌ **B: Create dummy BonEntreeItem**
  - Pro: Maintains single source of truth
  - Con: Breaks audit trail (fake receipt item)
  - Con: Complicates reporting

**RECOMMENDATION: Option A** - Add columns to rolls table

### 2. Stock Quantity Sync
**Question:** When removing rolls, should we always decrement stock_quantities?

**Answer:**
- **YES** for `consumed` and `archived` (roll no longer available)
- **NO** for `damaged` (physically exists but unusable - already excluded from counts)
- **RESTORE** reverses the operation

### 3. Adjustment Reversibility
**Question:** Can roll adjustments be reversed/undone?

**Recommendation:**
- **ADD rolls**: Can be removed later (just another adjustment)
- **REMOVE rolls**: Cannot truly reverse (status change is permanent)
- **DAMAGE rolls**: Can be restored (status: damaged → in_stock)
- Keep all `RollAdjustment` records as immutable audit trail

---

## 📊 DATA FLOW DIAGRAM

```
User Action: Add Roll
    ↓
RollAdjustmentResource Form
    ↓
RollAdjustmentService.addRoll()
    ↓
    ├─→ Roll::create() [with weight_kg, cump_value]
    ├─→ StockMovement::create() [type=ADJUSTMENT]
    ├─→ StockQuantity::increment(total_qty, 1)
    └─→ RollAdjustment::create() [audit record]

User Action: Remove Rolls
    ↓
RollAdjustmentResource Form (multi-select)
    ↓
RollAdjustmentService.removeRolls()
    ↓
    foreach $roll:
        ├─→ Roll::update(['status' => consumed])
        ├─→ StockMovement::create() [type=ADJUSTMENT]
        ├─→ StockQuantity::decrement(total_qty, 1)
        └─→ RollAdjustment::create() [audit record]
```

---

## 🎯 NEXT STEPS

1. **Decision Required:** Approve Option B (separate roll_adjustments table)?
2. **Decision Required:** Approve adding weight_kg/cump_value to rolls table?
3. **Implementation:** Start with Phase 1 (Database & Models)
4. **Testing:** Create sample data with manual rolls

---

## 📝 NOTES & CONSIDERATIONS

- **Roll adjustments are IMMUTABLE** - once created, cannot be edited (only new adjustment to reverse)
- **EAN-13 validation** must ensure uniqueness across all rolls
- **Warehouse filtering** is critical - only show rolls from selected warehouse
- **Product filtering** must enforce `is_roll = true`
- **Status transitions** need validation rules (e.g., cannot consume already consumed roll)
- **CUMP for manual rolls** can default to current product CUMP if not specified
- **Reporting** needs to distinguish receipt-based rolls vs manually-added rolls

---

**Status:** ✅ Analysis Complete - Awaiting approval to proceed with implementation
