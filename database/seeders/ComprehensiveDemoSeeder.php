<?php

namespace Database\Seeders;

use App\Models\BonEntree;
use App\Models\BonEntreeItem;
use App\Models\BonReintegration;
use App\Models\BonReintegrationItem;
use App\Models\BonSortie;
use App\Models\BonSortieItem;
use App\Models\BonTransfert;
use App\Models\BonTransfertItem;
use App\Models\Category;
use App\Models\Client;
use App\Models\LowStockAlert;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\Roll;
use App\Models\RollAdjustment;
use App\Models\RollLifecycleEvent;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use App\Models\Subcategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * ComprehensiveDemoSeeder
 * 
 * Creates 12 months of realistic demo data for all tables
 * covering every possible scenario in the SIFCO inventory system.
 * 
 * This seeder should only be run ONCE on a fresh database.
 */
class ComprehensiveDemoSeeder extends Seeder
{
    protected $warehouses;
    protected $suppliers;
    protected $clients;
    protected $productionLines;
    protected $rollProducts;
    protected $standardProducts;
    protected $users;
    protected $adminUser;
    
    protected $monthlyEntries = [];
    protected $rolls = [];
    
    public function run(): void
    {
        $this->command?->info('🚀 Starting Comprehensive 12-Month Demo Data Generation...');
        $this->command?->newLine();
        
        // Load existing reference data
        $this->loadReferenceData();
        
        // Generate 12 months of demo data
        $this->generateDemoData();
        
        $this->command?->newLine();
        $this->command?->info('✅ Comprehensive demo data generation complete!');
        $this->printSummary();
    }
    
    protected function loadReferenceData(): void
    {
        $this->command?->info('📦 Loading reference data...');
        
        $this->warehouses = Warehouse::all();
        $this->suppliers = Supplier::all();
        $this->clients = Client::all();
        $this->productionLines = ProductionLine::all();
        $this->users = User::all();
        $this->adminUser = User::first();
        
        $this->rollProducts = Product::where('form_type', Product::FORM_ROLL)->get();
        $this->standardProducts = Product::where('form_type', '!=', Product::FORM_ROLL)->get();
        
        if ($this->warehouses->isEmpty() || $this->suppliers->isEmpty()) {
            $this->command?->error('❌ Please run base seeders first: php artisan db:seed');
            throw new \Exception('Missing base data');
        }
        
        $this->command?->info("   Found {$this->warehouses->count()} warehouses, {$this->suppliers->count()} suppliers");
        $this->command?->info("   Found {$this->rollProducts->count()} roll products, {$this->standardProducts->count()} standard products");
    }
    
    protected function generateDemoData(): void
    {
        $startDate = Carbon::now()->subMonths(12)->startOfMonth();
        $endDate = Carbon::now();
        
        $this->command?->info("📅 Generating data from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->command?->newLine();
        
        // Process each month
        $currentDate = $startDate->copy();
        $monthNumber = 1;
        
        while ($currentDate->lte($endDate)) {
            $this->command?->info("📆 Processing month {$monthNumber}: " . $currentDate->format('F Y'));
            
            $this->generateMonthData($currentDate->copy(), $monthNumber);
            
            $currentDate->addMonth();
            $monthNumber++;
        }
    }
    
    protected function generateMonthData(Carbon $monthStart, int $monthNumber): void
    {
        $monthEnd = $monthStart->copy()->endOfMonth();
        $isCurrentMonth = $monthStart->isCurrentMonth();
        
        // Scale activity based on "business growth" - more activity in recent months
        $activityMultiplier = min(1 + ($monthNumber * 0.08), 2.0);
        
        // 1. BON D'ENTRÉES (Receptions from suppliers) - 4-8 per month
        $entryCount = rand(4, 8) * $activityMultiplier;
        $this->createBonEntrees($monthStart, $monthEnd, (int)$entryCount);
        
        // 2. BON SORTIES (Issues to production/clients) - 6-12 per month
        $issueCount = rand(6, 12) * $activityMultiplier;
        $this->createBonSorties($monthStart, $monthEnd, (int)$issueCount);
        
        // 3. BON TRANSFERS (Between warehouses) - 2-4 per month
        if ($this->warehouses->count() > 1) {
            $transferCount = rand(2, 4);
            $this->createBonTransferts($monthStart, $monthEnd, $transferCount);
        }
        
        // 4. BON REINTEGRATIONS (Returns) - 1-3 per month
        $returnCount = rand(1, 3);
        $this->createBonReintegrations($monthStart, $monthEnd, $returnCount);
        
        // 5. STOCK ADJUSTMENTS - 1-2 per month
        $adjustmentCount = rand(1, 2);
        $this->createStockAdjustments($monthStart, $monthEnd, $adjustmentCount);
        
        // 6. ROLL ADJUSTMENTS (weight/length corrections) - 0-2 per month
        $rollAdjCount = rand(0, 2);
        $this->createRollAdjustments($monthStart, $monthEnd, $rollAdjCount);
        
        // 7. LOW STOCK ALERTS - Random occurrences
        if (rand(1, 3) === 1) {
            $this->createLowStockAlerts($monthStart);
        }
        
        $this->command?->info("   ✓ Month processed");
    }
    
    protected function createBonEntrees(Carbon $monthStart, Carbon $monthEnd, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $entryDate = $this->randomDateBetween($monthStart, $monthEnd);
            $warehouse = $this->warehouses->random();
            $supplier = $this->suppliers->random();
            
            // Vary the status - most should be received for historical data
            $statuses = ['received', 'received', 'received', 'pending', 'draft'];
            $status = $statuses[array_rand($statuses)];
            
            $bonEntree = BonEntree::create([
                'bon_number' => 'BENT-' . $entryDate->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'sourceable_type' => Supplier::class,
                'sourceable_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'document_number' => 'FACT-' . $supplier->code . '-' . $entryDate->format('Ym') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'expected_date' => $entryDate,
                'received_date' => $status === 'received' ? $entryDate : null,
                'status' => $status,
                'frais_approche' => rand(100, 800) * 10,
                'total_amount_ht' => 0,
                'total_amount_ttc' => 0,
                'notes' => $this->getRandomNote('entry'),
                'created_at' => $entryDate,
                'updated_at' => $entryDate,
            ]);
            
            // Add items - 1-4 items per entry
            $itemCount = rand(1, 4);
            $totalHT = 0;
            
            for ($j = 0; $j < $itemCount; $j++) {
                // 60% chance for bobines, 40% for standard products
                $isBobine = rand(1, 10) <= 6 && $this->rollProducts->isNotEmpty();
                
                if ($isBobine) {
                    $product = $this->rollProducts->random();
                    $weight = rand(200, 400) + (rand(0, 99) / 100);
                    $length = rand(1200, 2500) + (rand(0, 99) / 100);
                    $price = rand(1200, 2200) + (rand(0, 99) / 100);
                    
                    $priceTTC = round($price * 1.19, 2);
                    $item = BonEntreeItem::create([
                        'bon_entree_id' => $bonEntree->id,
                        'item_type' => 'bobine',
                        'product_id' => $product->id,
                        'ean_13' => '299' . rand(1000000000, 9999999999),
                        'batch_number' => 'LOT-' . $entryDate->format('ymd') . '-' . strtoupper(Str::random(2)),
                        'qty_entered' => 1,
                        'weight_kg' => $weight,
                        'length_m' => $length,
                        'price_ht' => $price,
                        'price_ttc' => $priceTTC,
                        'line_total_ttc' => $priceTTC, // qty=1, so line_total = price_ttc
                        'created_at' => $entryDate,
                        'updated_at' => $entryDate,
                    ]);
                    
                    $totalHT += $price;
                    
                    // Create roll if entry is received
                    if ($status === 'received') {
                        $this->createRollFromEntry($item, $warehouse, $entryDate, $bonEntree);
                    }
                } else {
                    $product = $this->standardProducts->random();
                    $qty = rand(20, 200);
                    $price = rand(10, 50) + (rand(0, 99) / 100);
                    
                    $unitPriceTTC = round($price * 1.19, 2);
                    $lineTotalTTC = round($qty * $unitPriceTTC, 2);
                    BonEntreeItem::create([
                        'bon_entree_id' => $bonEntree->id,
                        'item_type' => 'product',
                        'product_id' => $product->id,
                        'ean_13' => '399' . rand(1000000000, 9999999999), // Unique EAN for products
                        'qty_entered' => $qty,
                        'price_ht' => $price,
                        'price_ttc' => $unitPriceTTC,
                        'line_total_ttc' => $lineTotalTTC, // qty * price_ttc
                        'created_at' => $entryDate,
                        'updated_at' => $entryDate,
                    ]);
                    
                    $totalHT += $price * $qty;
                    
                    // Create/update stock quantity if received
                    if ($status === 'received') {
                        $this->updateStockQuantity($product, $warehouse, $qty, $price, $entryDate, 'RECEPTION');
                    }
                }
            }
            
            // Update totals
            $bonEntree->update([
                'total_amount_ht' => $totalHT,
                'total_amount_ttc' => $totalHT * 1.19,
            ]);
        }
    }
    
    protected function createRollFromEntry($item, $warehouse, $date, $bonEntree): void
    {
        $roll = Roll::create([
            'bon_entree_item_id' => $item->id,
            'product_id' => $item->product_id,
            'warehouse_id' => $warehouse->id,
            'ean_13' => $item->ean_13,
            'batch_number' => $item->batch_number,
            'weight_kg' => $item->weight_kg,
            'length_m' => $item->length_m,
            'cump_value' => $item->price_ttc,
            'received_date' => $date->toDateString(),
            'status' => 'in_stock',
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        
        $this->rolls[] = $roll;
        
        // Create stock movement
        $this->createStockMovement($item->product_id, null, $warehouse->id, 'RECEPTION', 1, 
            $item->weight_kg, $item->length_m, $item->price_ttc, $date);
        
        // Create lifecycle event
        $this->createRollLifecycleEvent($roll, RollLifecycleEvent::TYPE_RECEPTION, 
            $item->weight_kg, $item->length_m, $date, "Réception via {$bonEntree->bon_number}");
    }
    
    protected function createBonSorties(Carbon $monthStart, Carbon $monthEnd, int $count): void
    {
        $availableRolls = Roll::where('status', 'in_stock')->get();
        
        for ($i = 0; $i < $count; $i++) {
            $issueDate = $this->randomDateBetween($monthStart, $monthEnd);
            $warehouse = $this->warehouses->random();
            
            // Destination - production line, client, or free text
            $destType = rand(1, 3);
            $dest = null;
            $destable_type = null;
            $destable_id = null;
            
            if ($destType === 1 && $this->productionLines->isNotEmpty()) {
                $line = $this->productionLines->random();
                $dest = $line->name;
                $destable_type = ProductionLine::class;
                $destable_id = $line->id;
            } elseif ($destType === 2 && $this->clients->isNotEmpty()) {
                $client = $this->clients->random();
                $dest = $client->name;
                $destable_type = Client::class;
                $destable_id = $client->id;
            } else {
                $destinations = ['Atelier A', 'Atelier B', 'Zone de Coupe', 'Expédition', 'Qualité'];
                $dest = $destinations[array_rand($destinations)];
            }
            
            $statuses = ['issued', 'issued', 'issued', 'draft'];
            $status = $statuses[array_rand($statuses)];
            
            $bonSortie = BonSortie::create([
                'bon_number' => 'BSOR-' . $issueDate->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'warehouse_id' => $warehouse->id,
                'destination' => $dest,
                'destinationable_type' => $destable_type,
                'destinationable_id' => $destable_id,
                'issued_date' => $issueDate,
                'status' => $status,
                'notes' => $this->getRandomNote('issue'),
                'created_at' => $issueDate,
                'updated_at' => $issueDate,
            ]);
            
            // Add 1-3 items
            $itemCount = rand(1, 3);
            
            for ($j = 0; $j < $itemCount; $j++) {
                $isRoll = rand(1, 10) <= 5 && $availableRolls->isNotEmpty();
                
                if ($isRoll) {
                    $roll = $availableRolls->random();
                    $weight = $roll->weight_kg;
                    $length = $roll->length_m ?? 0;
                    $rollCump = $roll->cump ?? $roll->cump_value ?? 0;
                    
                    BonSortieItem::create([
                        'bon_sortie_id' => $bonSortie->id,
                        'item_type' => 'roll',
                        'product_id' => $roll->product_id,
                        'roll_id' => $roll->id,
                        'qty_issued' => 1,
                        'weight_kg' => $weight,
                        'length_m' => $length,
                        'cump_at_issue' => $rollCump,
                        'value_issued' => $rollCump, // 1 * cump
                        'created_at' => $issueDate,
                        'updated_at' => $issueDate,
                    ]);
                    
                    if ($status === 'issued') {
                        $roll->update(['status' => 'consumed']);
                        $availableRolls = $availableRolls->filter(fn($r) => $r->id !== $roll->id);
                        
                        $this->createStockMovement($roll->product_id, $warehouse->id, null, 'ISSUE', 1, 
                            $weight, $length, $roll->cump, $issueDate);
                        
                        $this->createRollLifecycleEvent($roll, RollLifecycleEvent::TYPE_SORTIE, 
                            -$weight, -$length, $issueDate, "Sortie vers $dest");
                    }
                } else {
                    $product = $this->standardProducts->random();
                    $qty = rand(5, 50);
                    $price = rand(10, 40);
                    $valueIssued = round($qty * $price, 2);
                    
                    BonSortieItem::create([
                        'bon_sortie_id' => $bonSortie->id,
                        'item_type' => 'product',
                        'product_id' => $product->id,
                        'qty_issued' => $qty,
                        'cump_at_issue' => $price,
                        'value_issued' => $valueIssued,
                        'created_at' => $issueDate,
                        'updated_at' => $issueDate,
                    ]);
                    
                    if ($status === 'issued') {
                        $this->updateStockQuantity($product, $warehouse, -$qty, $price, $issueDate, 'ISSUE');
                    }
                }
            }
        }
    }
    
    protected function createBonTransferts(Carbon $monthStart, Carbon $monthEnd, int $count): void
    {
        for ($i = 0; $i < $count && $this->warehouses->count() > 1; $i++) {
            $transferDate = $this->randomDateBetween($monthStart, $monthEnd);
            
            $fromWarehouse = $this->warehouses->random();
            $toWarehouse = $this->warehouses->filter(fn($w) => $w->id !== $fromWarehouse->id)->random();
            
            $statuses = ['received', 'received', 'in_transit', 'draft'];
            $status = $statuses[array_rand($statuses)];
            
            $bonTransfert = BonTransfert::create([
                'bon_number' => 'BTRA-' . $transferDate->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'warehouse_from_id' => $fromWarehouse->id,
                'warehouse_to_id' => $toWarehouse->id,
                'transfer_date' => $transferDate,
                'status' => $status,
                'notes' => 'Transfert inter-dépôts',
                'created_at' => $transferDate,
                'updated_at' => $transferDate,
            ]);
            
            // Transfer 1-2 items
            $itemCount = rand(1, 2);
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $this->standardProducts->random();
                $qty = rand(10, 50);
                $cump = rand(10, 50) + (rand(0, 99) / 100);
                
                BonTransfertItem::create([
                    'bon_transfert_id' => $bonTransfert->id,
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'qty_transferred' => $qty,
                    'cump_at_transfer' => $cump,
                    'value_transferred' => round($qty * $cump, 2),
                    'created_at' => $transferDate,
                    'updated_at' => $transferDate,
                ]);
                
                if ($status === 'received') {
                    $this->createStockMovement($product->id, $fromWarehouse->id, $toWarehouse->id, 
                        'TRANSFER', $qty, 0, 0, 0, $transferDate);
                }
            }
        }
    }
    
    protected function createBonReintegrations(Carbon $monthStart, Carbon $monthEnd, int $count): void
    {
        $issuedRolls = Roll::where('status', 'consumed')->take($count)->get();
        
        foreach ($issuedRolls as $roll) {
            $returnDate = $this->randomDateBetween($monthStart, $monthEnd);
            $warehouse = Warehouse::find($roll->warehouse_id) ?? $this->warehouses->first();
            
            // Find a bon sortie for this roll
            $bonSortie = BonSortie::whereHas('bonSortieItems', function($q) use ($roll) {
                $q->where('roll_id', $roll->id);
            })->first();
            
            if (!$bonSortie) continue;
            
            // Calculate returned weight/length (partial return - 70-95% remaining)
            $returnPercent = rand(70, 95) / 100;
            $returnedWeight = round($roll->initial_weight_kg * $returnPercent, 2);
            $returnedLength = round(($roll->initial_length_m ?? 0) * $returnPercent, 2);
            
            $bonReintegration = BonReintegration::create([
                'bon_number' => 'BREI-' . $returnDate->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'bon_sortie_id' => $bonSortie->id,
                'warehouse_id' => $warehouse->id,
                'return_date' => $returnDate,
                'status' => 'received',
                'cump_at_return' => $roll->cump,
                'notes' => 'Retour partiel après production',
                'created_at' => $returnDate,
                'updated_at' => $returnDate,
            ]);
            
            $rollCump = $roll->cump ?? $roll->cump_value ?? 0;
            BonReintegrationItem::create([
                'bon_reintegration_id' => $bonReintegration->id,
                'item_type' => 'roll',
                'product_id' => $roll->product_id,
                'roll_id' => $roll->id,
                'qty_returned' => 1,
                'previous_weight_kg' => $roll->weight_kg,
                'returned_weight_kg' => $returnedWeight,
                'previous_length_m' => $roll->length_m,
                'returned_length_m' => $returnedLength,
                'cump_at_return' => $rollCump,
                'value_returned' => $rollCump, // 1 * cump
                'created_at' => $returnDate,
                'updated_at' => $returnDate,
            ]);
            
            // Update roll
            $roll->update([
                'weight_kg' => $returnedWeight,
                'length_m' => $returnedLength,
                'status' => 'in_stock',
            ]);
            
            $this->createStockMovement($roll->product_id, null, $warehouse->id, 'RETURN', 1, 
                $returnedWeight, $returnedLength, $roll->cump, $returnDate);
            
            $this->createRollLifecycleEvent($roll, RollLifecycleEvent::TYPE_REINTEGRATION, 
                $returnedWeight, $returnedLength, $returnDate, "Retour après production");
        }
    }
    
    protected function createStockAdjustments(Carbon $monthStart, Carbon $monthEnd, int $count): void
    {
        $types = ['INCREASE', 'DECREASE', 'CORRECTION'];
        
        for ($i = 0; $i < $count; $i++) {
            $adjDate = $this->randomDateBetween($monthStart, $monthEnd);
            $product = $this->standardProducts->random();
            $warehouse = $this->warehouses->random();
            $type = $types[array_rand($types)];
            
            // Adjustment can be positive or negative
            $qtyChange = rand(-20, 20);
            if ($qtyChange === 0) $qtyChange = rand(1, 10);
            $qtyBefore = rand(50, 200);
            $qtyAfter = max(0, $qtyBefore + $qtyChange);
            
            StockAdjustment::create([
                'adjustment_number' => 'ADJ-' . $adjDate->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'adjustment_type' => $type,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'qty_before' => $qtyBefore,
                'qty_change' => $qtyChange,
                'qty_after' => $qtyAfter,
                'reason' => $this->getAdjustmentReason($type),
                'adjusted_by' => $this->adminUser->id,
                'approved_by' => rand(0, 1) ? $this->adminUser->id : null,
                'approved_at' => rand(0, 1) ? $adjDate : null,
                'created_at' => $adjDate,
                'updated_at' => $adjDate,
            ]);
            
            $this->createStockMovement($product->id, $warehouse->id, $warehouse->id, 'ADJUSTMENT', 
                $qtyChange, 0, 0, 0, $adjDate, $type);
        }
    }
    
    protected function createRollAdjustments(Carbon $monthStart, Carbon $monthEnd, int $count): void
    {
        $inStockRolls = Roll::where('status', 'in_stock')->take($count)->get();
        $types = ['ADD', 'REMOVE', 'DAMAGE', 'RESTORE', 'WEIGHT_ADJUST'];
        
        foreach ($inStockRolls as $roll) {
            $adjDate = $this->randomDateBetween($monthStart, $monthEnd);
            $type = $types[array_rand($types)];
            
            $weightDelta = rand(-50, 20);
            $lengthDelta = rand(-200, 100);
            
            RollAdjustment::create([
                'adjustment_number' => 'RADJ-' . $adjDate->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'roll_id' => $roll->id,
                'product_id' => $roll->product_id,
                'warehouse_id' => $roll->warehouse_id,
                'adjustment_type' => $type,
                'previous_status' => $roll->status,
                'new_status' => $roll->status, // Status doesn't change for weight/length adjustments
                'previous_weight_kg' => $roll->weight_kg,
                'weight_delta_kg' => $weightDelta,
                'new_weight_kg' => max(0, $roll->weight_kg + $weightDelta),
                'previous_length_m' => $roll->length_m ?? 0,
                'length_delta_m' => $lengthDelta,
                'new_length_m' => max(0, ($roll->length_m ?? 0) + $lengthDelta),
                'reason' => "Ajustement $type lors du contrôle",
                'adjusted_by' => $this->adminUser->id,
                'created_at' => $adjDate,
                'updated_at' => $adjDate,
            ]);
            
            $roll->update([
                'weight_kg' => max(0, $roll->weight_kg + $weightDelta),
                'length_m' => max(0, ($roll->length_m ?? 0) + $lengthDelta),
            ]);
            
            $this->createRollLifecycleEvent($roll, RollLifecycleEvent::TYPE_ADJUSTMENT, 
                $weightDelta, $lengthDelta, $adjDate, "Ajustement: $type");
        }
    }
    
    protected function createLowStockAlerts(Carbon $date): void
    {
        $product = $this->standardProducts->random();
        $warehouse = $this->warehouses->random();
        
        $isResolved = rand(0, 1) ? true : false;
        $severities = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];
        
        LowStockAlert::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'current_qty' => rand(1, 10),
            'severity' => $severities[array_rand($severities)],
            'status' => $isResolved ? 'RESOLVED' : 'ACTIVE',
            'resolved_by' => $isResolved ? $this->adminUser->id : null,
            'resolved_at' => $isResolved ? $date->copy()->addDays(rand(1, 5)) : null,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
    
    protected function createStockMovement($productId, $fromWarehouseId, $toWarehouseId, $type, 
        $qty, $weight, $length, $value, $date, $refNumber = null): void
    {
        StockMovement::create([
            'movement_number' => 'SMOV-' . $date->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'product_id' => $productId,
            'warehouse_from_id' => $fromWarehouseId,
            'warehouse_to_id' => $toWarehouseId,
            'movement_type' => $type,
            'qty_moved' => $qty,
            'roll_weight_delta_kg' => $weight,
            'roll_length_delta_m' => $length,
            'cump_at_movement' => $value,
            'value_moved' => $value * abs($qty),
            'status' => 'confirmed',
            'reference_number' => $refNumber,
            'user_id' => $this->adminUser->id,
            'performed_at' => $date,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
    
    protected function createRollLifecycleEvent($roll, $eventType, $weightDelta, $lengthDelta, $date, $notes): void
    {
        RollLifecycleEvent::create([
            'roll_id' => $roll->id,
            'event_type' => $eventType,
            'weight_before_kg' => $roll->weight_kg,
            'weight_after_kg' => max(0, $roll->weight_kg + $weightDelta),
            'weight_delta_kg' => $weightDelta,
            'length_before_m' => $roll->length_m ?? 0,
            'length_after_m' => max(0, ($roll->length_m ?? 0) + $lengthDelta),
            'length_delta_m' => $lengthDelta,
            'triggered_by_id' => $this->adminUser->id,
            'notes' => $notes,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
    
    protected function updateStockQuantity($product, $warehouse, $qty, $price, $date, $movementType): void
    {
        $stockQty = StockQuantity::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
            ['total_qty' => 0, 'available_qty' => 0, 'cump_snapshot' => $price, 'total_weight_kg' => 0, 'reserved_qty' => 0]
        );
        
        $newQty = max(0, $stockQty->total_qty + $qty);
        $stockQty->update([
            'total_qty' => $newQty,
            'available_qty' => max(0, $newQty - ($stockQty->reserved_qty ?? 0)),
            'cump_snapshot' => $price,
        ]);
    }
    
    protected function randomDateBetween(Carbon $start, Carbon $end): Carbon
    {
        $diffDays = $start->diffInDays($end);
        $randomDays = rand(0, max(0, $diffDays));
        return $start->copy()->addDays($randomDays)->setTime(rand(8, 18), rand(0, 59));
    }
    
    protected function getRandomNote(string $type): ?string
    {
        $notes = [
            'entry' => [
                'Réception conforme à la commande',
                'Livraison urgente - priorité haute',
                'Contrôle qualité effectué',
                'Réception partielle - reste à livrer',
                'Commande mensuelle régulière',
            ],
            'issue' => [
                'Sortie pour production ligne A',
                'Commande client urgent',
                'Approvisionnement atelier',
                'Échantillons pour test qualité',
                'Sortie pour maintenance',
            ],
        ];
        
        $list = $notes[$type] ?? [];
        if (empty($list)) {
            return null;
        }
        // 30% chance to return null
        if (rand(1, 10) <= 3) {
            return null;
        }
        return $list[array_rand($list)];
    }
    
    protected function getAdjustmentReason(string $type): string
    {
        $reasons = [
            'inventory_count' => 'Écart inventaire physique',
            'damage' => 'Produit endommagé lors du stockage',
            'loss' => 'Perte inexpliquée - enquête en cours',
            'correction' => 'Correction erreur de saisie',
            'expiry' => 'Produit périmé - mise au rebut',
        ];
        
        return $reasons[$type] ?? 'Ajustement standard';
    }
    
    protected function printSummary(): void
    {
        $this->command?->newLine();
        $this->command?->info('📊 Summary of generated data:');
        
        $this->command?->table(
            ['Table', 'Count'],
            [
                ['BonEntree', BonEntree::count()],
                ['BonEntreeItem', BonEntreeItem::count()],
                ['BonSortie', BonSortie::count()],
                ['BonSortieItem', BonSortieItem::count()],
                ['BonTransfert', BonTransfert::count()],
                ['BonTransfertItem', BonTransfertItem::count()],
                ['BonReintegration', BonReintegration::count()],
                ['BonReintegrationItem', BonReintegrationItem::count()],
                ['Roll', Roll::count()],
                ['RollAdjustment', RollAdjustment::count()],
                ['RollLifecycleEvent', RollLifecycleEvent::count()],
                ['StockMovement', StockMovement::count()],
                ['StockAdjustment', StockAdjustment::count()],
                ['StockQuantity', StockQuantity::count()],
                ['LowStockAlert', LowStockAlert::count()],
            ]
        );
    }
}
