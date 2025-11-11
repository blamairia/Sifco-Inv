<?php

namespace Tests\Unit;

use App\Models\BonSortie;
use App\Models\BonSortieItem;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\StockQuantity;
use App\Models\Warehouse;
use App\Services\BonSortieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonSortieServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_to_production_line_decrements_stock_and_marks_issued(): void
    {
        // Arrange
        $warehouse = Warehouse::create(['name' => 'Main Out', 'is_system' => false]);
        $line = ProductionLine::create(['name' => 'MACARBOX', 'code' => 'MACARBOX', 'status' => 'active']);

        $product = Product::create([
            'code' => 'PROD-PS-001',
            'name' => 'Product Stock',
            'type' => 'consommable',
            'is_roll' => false,
            'unit_id' => null,
            'product_type' => Product::TYPE_RAW_MATERIAL,
        ]);

        // Seed initial stock
        StockQuantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'total_qty' => 50,
            'cump_snapshot' => 12.50,
        ]);

        $bon = BonSortie::create([
            'bon_number' => 'BSRT-UT-001',
            'warehouse_id' => $warehouse->id,
            'issued_date' => now()->toDateString(),
            'destination' => 'MACARBOX',
            'destinationable_type' => ProductionLine::class,
            'destinationable_id' => $line->id,
            'status' => 'draft',
        ]);

        $item = BonSortieItem::create([
            'bon_sortie_id' => $bon->id,
            'item_type' => 'product',
            'product_id' => $product->id,
            'qty_issued' => 10,
            'cump_at_issue' => 12.50,
        ]);

    // Arrange - create a user and act as it so StockMovement FK to user exists
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // Act
        /** @var BonSortieService $service */
        $service = app(BonSortieService::class);
        $service->issue($bon->fresh());

        // Assert
        $this->assertDatabaseHas('bon_sorties', ['bon_number' => 'BSRT-UT-001', 'status' => 'issued']);

        $stock = StockQuantity::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(40, (int) $stock->total_qty);
    }
}
