<?php

namespace Tests\Feature;

use App\Models\BonTransfert;
use App\Models\BonTransfertItem;
use App\Models\Product;
use App\Models\Roll;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\BonTransfertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BonTransfertServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_transfer_moves_roll_to_transit_without_updating_destination_stock(): void
    {
        $service = app(BonTransfertService::class);

        [$bonTransfert, $roll] = $this->createRollTransferFixture();

        $service->transfer($bonTransfert);

        $bonTransfert->refresh();
        $roll->refresh();

        $this->assertSame('in_transit', $bonTransfert->status);
        $this->assertNotNull($bonTransfert->transferred_at);
        $this->assertSame(Roll::STATUS_RESERVED, $roll->status);
        $this->assertSame($bonTransfert->warehouse_to_id, $roll->warehouse_id);

        $sourceStock = StockQuantity::where('product_id', $roll->product_id)
            ->where('warehouse_id', $bonTransfert->warehouse_from_id)
            ->first();
        $this->assertSame(0.0, (float) $sourceStock->total_qty);

        $destStock = StockQuantity::where('product_id', $roll->product_id)
            ->where('warehouse_id', $bonTransfert->warehouse_to_id)
            ->first();
        $this->assertNull($destStock);

        $item = $bonTransfert->bonTransfertItems()->first();
        $this->assertNotNull($item->movement_out_id);
        $this->assertNotNull($item->movement_in_id);
        $this->assertSame(250.0, (float) $item->weight_transferred_kg);

        $outMovement = StockMovement::find($item->movement_out_id);
        $inMovement = StockMovement::find($item->movement_in_id);

        $this->assertSame('confirmed', $outMovement->status);
        $this->assertSame(-1.0, (float) $outMovement->qty_moved);

        $this->assertSame('pending', $inMovement->status);
        $this->assertSame(1.0, (float) $inMovement->qty_moved);
    }

    public function test_receive_confirms_roll_transfer_and_updates_destination_stock(): void
    {
        $service = app(BonTransfertService::class);

        [$bonTransfert, $roll] = $this->createRollTransferFixture();

        $service->transfer($bonTransfert);
        $service->receive($bonTransfert->fresh());

        $bonTransfert->refresh();
        $roll->refresh();

        $this->assertSame('received', $bonTransfert->status);
        $this->assertNotNull($bonTransfert->received_at);
        $this->assertSame(Roll::STATUS_IN_STOCK, $roll->status);

        $item = $bonTransfert->bonTransfertItems()->first();
        $inMovement = StockMovement::find($item->movement_in_id);

        $this->assertSame('confirmed', $inMovement->status);
        $this->assertEqualsWithDelta(now()->timestamp, $inMovement->performed_at?->timestamp ?? 0, 5);

        $destStock = StockQuantity::where('product_id', $roll->product_id)
            ->where('warehouse_id', $bonTransfert->warehouse_to_id)
            ->firstOrFail();

        $this->assertSame(1.0, (float) $destStock->total_qty);
        $this->assertSame(250.0, (float) $destStock->total_weight_kg);
        $this->assertSame($inMovement->id, $destStock->last_movement_id);
        $this->assertSame(125.0, (float) $destStock->cump_snapshot);
    }

    public function test_receive_updates_destination_cump_for_product(): void
    {
        $service = app(BonTransfertService::class);

        [$bonTransfert, $product] = $this->createProductTransferFixture();

        $service->transfer($bonTransfert);

        // Seed destination with existing stock to test CUMP averaging
        StockQuantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $bonTransfert->warehouse_to_id,
            'total_qty' => 10,
            'total_weight_kg' => 0,
            'reserved_qty' => 0,
            'cump_snapshot' => 100,
        ]);

        $service->receive($bonTransfert->fresh());

        $destStock = StockQuantity::where('product_id', $product->id)
            ->where('warehouse_id', $bonTransfert->warehouse_to_id)
            ->firstOrFail();

        $this->assertSame(15.0, (float) $destStock->total_qty);
    $this->assertSame(116.67, round((float) $destStock->cump_snapshot, 2));
    }

    private function createRollTransferFixture(): array
    {
        $unit = Unit::create([
            'name' => 'Kilogram',
            'symbol' => 'kg',
        ]);

        $product = Product::create([
            'code' => 'ROLL-001',
            'name' => 'Roll 001',
            'type' => 'papier_roll',
            'unit_id' => $unit->id,
            'is_active' => true,
            'is_roll' => true,
        ]);

        $warehouseFrom = Warehouse::create(['name' => 'Source']);
        $warehouseTo = Warehouse::create(['name' => 'Destination']);

        StockQuantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseFrom->id,
            'total_qty' => 1,
            'total_weight_kg' => 250,
            'reserved_qty' => 0,
            'cump_snapshot' => 125,
        ]);

        $roll = Roll::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseFrom->id,
            'ean_13' => '1234567890123',
            'received_date' => Carbon::now(),
            'status' => Roll::STATUS_IN_STOCK,
            'weight_kg' => 250,
            'cump_value' => 125,
        ]);

        $bonTransfert = BonTransfert::create([
            'bon_number' => 'BTRN-TEST-001',
            'warehouse_from_id' => $warehouseFrom->id,
            'warehouse_to_id' => $warehouseTo->id,
            'transfer_date' => Carbon::now(),
            'status' => 'draft',
        ]);

        $item = BonTransfertItem::create([
            'bon_transfert_id' => $bonTransfert->id,
            'item_type' => 'roll',
            'product_id' => $product->id,
            'roll_id' => $roll->id,
            'qty_transferred' => 1,
            'cump_at_transfer' => 125,
        ]);

        return [$bonTransfert->fresh(), $roll->fresh()];
    }

    private function createProductTransferFixture(): array
    {
        $unit = Unit::create([
            'name' => 'Unit',
            'symbol' => 'u',
        ]);

        $product = Product::create([
            'code' => 'PRD-001',
            'name' => 'Product 001',
            'type' => 'consommable',
            'unit_id' => $unit->id,
            'is_active' => true,
            'is_roll' => false,
        ]);

        $warehouseFrom = Warehouse::create(['name' => 'Source']);
        $warehouseTo = Warehouse::create(['name' => 'Destination']);

        StockQuantity::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseFrom->id,
            'total_qty' => 20,
            'total_weight_kg' => 0,
            'reserved_qty' => 0,
            'cump_snapshot' => 120,
        ]);

        $bonTransfert = BonTransfert::create([
            'bon_number' => 'BTRN-PRD-001',
            'warehouse_from_id' => $warehouseFrom->id,
            'warehouse_to_id' => $warehouseTo->id,
            'transfer_date' => Carbon::now(),
            'status' => 'draft',
        ]);

        BonTransfertItem::create([
            'bon_transfert_id' => $bonTransfert->id,
            'item_type' => 'product',
            'product_id' => $product->id,
            'qty_transferred' => 5,
            'cump_at_transfer' => 150,
        ]);

        return [$bonTransfert->fresh(), $product];
    }
}
