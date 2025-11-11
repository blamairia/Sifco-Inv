<?php

namespace Tests\Unit;

use App\Models\BonEntree;
use App\Models\BonEntreeItem;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\Roll;
use App\Models\StockQuantity;
use App\Models\Warehouse;
use App\Services\BonEntreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonEntreeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_receive_from_production_line_creates_roll_and_updates_stock(): void
    {
        // Arrange
        $warehouse = Warehouse::create(['name' => 'Main', 'is_system' => false]);
        $line = ProductionLine::create(['name' => 'FOSBER', 'code' => 'FOSBER', 'status' => 'active']);

        $product = Product::create([
            'code' => 'TEST-ROLL-001',
            'name' => 'Test Roll',
            'is_roll' => true,
            'unit_id' => null,
            'product_type' => Product::TYPE_SEMI_FINISHED,
        ]);

        $bon = BonEntree::create([
            'bon_number' => 'BENT-UT-001',
            'sourceable_type' => ProductionLine::class,
            'sourceable_id' => $line->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'pending',
            'frais_approche' => 0,
            'total_amount_ht' => 0,
            'total_amount_ttc' => 0,
        ]);

        $item = BonEntreeItem::create([
            'bon_entree_id' => $bon->id,
            'item_type' => 'bobine',
            'product_id' => $product->id,
            'ean_13' => '2990000000001',
            'qty_entered' => 1,
            'weight_kg' => 100.0,
            'length_m' => 1200.0,
            'price_ht' => 1000.0,
            'price_ttc' => 1000.0,
        ]);

        // Act
        /** @var BonEntreeService $service */
        $service = app(BonEntreeService::class);
        $service->receive($bon->fresh());

        // Assert
        $this->assertDatabaseHas('rolls', ['ean_13' => '2990000000001']);

        $roll = Roll::where('ean_13', '2990000000001')->first();
        $this->assertNotNull($roll);
        $this->assertEquals($warehouse->id, $roll->warehouse_id);

        $stock = StockQuantity::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(1, (int) $stock->total_qty);
    }
}
