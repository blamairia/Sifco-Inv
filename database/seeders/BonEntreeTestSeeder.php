<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BonEntree;
use App\Models\BonEntreeItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;

class BonEntreeTestSeeder extends Seeder
{
    /**
     * Run the database seeders.
     * Creates test scenarios for Bon d'Entrée workflow
     */
    public function run(): void
    {
        // Clean up old test data
        BonEntree::where('bon_number', 'LIKE', 'BENT-TEST-%')->delete();
        $this->command->info('🧹 Cleaned up old test data');
        
        // Ensure we have test data
        $warehouse = Warehouse::first();
        $supplier = Supplier::first();
        
        if (!$warehouse || !$supplier) {
            $this->command->error('❌ Please run main seeders first (php artisan db:seed)');
            return;
        }

        // Get products (2 bobines + 2 normal products)
        $bobineProducts = Product::where('is_roll', true)->take(2)->get();
        $normalProducts = Product::where('is_roll', false)->take(2)->get();

        if ($bobineProducts->count() < 2 || $normalProducts->count() < 2) {
            $this->command->error('❌ Not enough products. Need at least 2 bobines and 2 normal products.');
            return;
        }

        $this->command->info('🌱 Creating Bon d\'Entrée test scenarios...');

        // ========================================
        // SCENARIO 1: DRAFT - Ready to validate
        // ========================================
        $this->command->info('📝 Creating Scenario 1: DRAFT bon with mixed items...');
        
        $bon1 = BonEntree::create([
            'bon_number' => 'BENT-TEST-001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'document_number' => 'FACT-2025-001',
            'expected_date' => now()->addDays(2),
            'status' => 'draft',
            'frais_approche' => 500.00,
            'total_amount_ht' => 0,
            'total_amount_ttc' => 0,
            'notes' => 'TEST SCENARIO 1: Draft bon ready for validation',
        ]);

        // Add 2 bobines (each with unique EAN-13)
        BonEntreeItem::create([
            'bon_entree_id' => $bon1->id,
            'item_type' => 'bobine',
            'product_id' => $bobineProducts[0]->id,
            'ean_13' => '2990000010001',
            'batch_number' => 'LOT-2025-A1',
            'qty_entered' => 1,
            'price_ht' => 1500.00,
            'price_ttc' => 1500.00,
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bon1->id,
            'item_type' => 'bobine',
            'product_id' => $bobineProducts[1]->id,
            'ean_13' => '2990000020002',
            'batch_number' => 'LOT-2025-A2',
            'qty_entered' => 1,
            'price_ht' => 1800.00,
            'price_ttc' => 1800.00,
        ]);

        // Add 2 normal products
        BonEntreeItem::create([
            'bon_entree_id' => $bon1->id,
            'item_type' => 'product',
            'product_id' => $normalProducts[0]->id,
            'qty_entered' => 100,
            'price_ht' => 15.00,
            'price_ttc' => 15.00,
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bon1->id,
            'item_type' => 'product',
            'product_id' => $normalProducts[1]->id,
            'qty_entered' => 50,
            'price_ht' => 25.00,
            'price_ttc' => 25.00,
        ]);

        $this->command->info("   ✅ Created {$bon1->bon_number} (DRAFT) with 2 bobines + 2 products");

        // ========================================
        // SCENARIO 2: BOBINES ONLY - Test bobine workflow
        // ========================================
        $this->command->info('📝 Creating Scenario 2: Bobines only...');
        
        $bon2 = BonEntree::create([
            'bon_number' => 'BENT-TEST-002',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'document_number' => 'FACT-2025-002',
            'expected_date' => now()->addDays(1),
            'status' => 'draft',
            'frais_approche' => 300.00,
            'total_amount_ht' => 0,
            'total_amount_ttc' => 0,
            'notes' => 'TEST SCENARIO 2: Bobines only - test roll creation',
        ]);

        // Add 3 bobines with unique EAN codes
        BonEntreeItem::create([
            'bon_entree_id' => $bon2->id,
            'item_type' => 'bobine',
            'product_id' => $bobineProducts[0]->id,
            'ean_13' => '2990000100011',
            'batch_number' => 'LOT-2025-B1',
            'qty_entered' => 1,
            'price_ht' => 1600.00,
            'price_ttc' => 1600.00,
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bon2->id,
            'item_type' => 'bobine',
            'product_id' => $bobineProducts[0]->id,
            'ean_13' => '2990000100022',
            'batch_number' => 'LOT-2025-B2',
            'qty_entered' => 1,
            'price_ht' => 1600.00,
            'price_ttc' => 1600.00,
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bon2->id,
            'item_type' => 'bobine',
            'product_id' => $bobineProducts[0]->id,
            'ean_13' => '2990000100033',
            'batch_number' => 'LOT-2025-B3',
            'qty_entered' => 1,
            'price_ht' => 1600.00,
            'price_ttc' => 1600.00,
        ]);

        $this->command->info("   ✅ Created {$bon2->bon_number} (DRAFT) with 3 bobines");

        // ========================================
        // SCENARIO 3: PRODUCTS ONLY - Test normal products
        // ========================================
        $this->command->info('📝 Creating Scenario 3: Products only...');
        
        $bon3 = BonEntree::create([
            'bon_number' => 'BENT-TEST-003',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'document_number' => 'FACT-2025-003',
            'expected_date' => now()->addDays(3),
            'status' => 'draft',
            'frais_approche' => 200.00,
            'total_amount_ht' => 0,
            'total_amount_ttc' => 0,
            'notes' => 'TEST SCENARIO 3: Normal products only - test stock updates',
        ]);

        // Add 2 products with different quantities
        BonEntreeItem::create([
            'bon_entree_id' => $bon3->id,
            'item_type' => 'product',
            'product_id' => $normalProducts[0]->id,
            'qty_entered' => 200,
            'price_ht' => 12.00,
            'price_ttc' => 12.00,
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bon3->id,
            'item_type' => 'product',
            'product_id' => $normalProducts[1]->id,
            'qty_entered' => 150,
            'price_ht' => 20.00,
            'price_ttc' => 20.00,
        ]);

        $this->command->info("   ✅ Created {$bon3->bon_number} (DRAFT) with 2 products");

        // ========================================
        // SCENARIO 4: PENDING - Ready to receive
        // ========================================
        $this->command->info('📝 Creating Scenario 4: PENDING bon ready to receive...');
        
        $bon4 = BonEntree::create([
            'bon_number' => 'BENT-TEST-004',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'document_number' => 'FACT-2025-004',
            'expected_date' => now(),
            'status' => 'pending',
            'frais_approche' => 400.00,
            'total_amount_ht' => 4300.00,
            'total_amount_ttc' => 4700.00,
            'notes' => 'TEST SCENARIO 4: Pending bon - test reception workflow',
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bon4->id,
            'item_type' => 'bobine',
            'product_id' => $bobineProducts[0]->id,
            'ean_13' => '2990000030004',
            'batch_number' => 'LOT-2025-C1',
            'qty_entered' => 1,
            'price_ht' => 1500.00,
            'price_ttc' => 1600.00, // Frais already distributed
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bon4->id,
            'item_type' => 'product',
            'product_id' => $normalProducts[0]->id,
            'qty_entered' => 100,
            'price_ht' => 15.00,
            'price_ttc' => 16.00, // Frais already distributed
        ]);

        $this->command->info("   ✅ Created {$bon4->bon_number} (PENDING) ready for reception");

        // ========================================
        // Summary
        // ========================================
        $this->command->newLine();
        $this->command->info('✨ Test scenarios created successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Bon Number', 'Status', 'Items', 'Purpose'],
            [
                ['BENT-TEST-001', 'DRAFT', '2 bobines + 2 products', 'Test validation + frais distribution'],
                ['BENT-TEST-002', 'DRAFT', '3 bobines', 'Test roll creation'],
                ['BENT-TEST-003', 'DRAFT', '2 products', 'Test normal stock updates'],
                ['BENT-TEST-004', 'PENDING', '1 bobine + 1 product', 'Test reception workflow'],
            ]
        );
        $this->command->newLine();
        $this->command->info('🚀 Ready to test! Go to Filament admin panel.');
    }
}
