<?php

namespace Tests\Feature;

use App\Models\BonEntree;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonEntreeCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Product $product;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->warehouse = Warehouse::create(['name' => 'Calc Warehouse', 'is_system' => false]);
        $this->supplier = Supplier::create([ 'code' => 'SUP-CALC', 'name' => 'Supplier', 'is_active' => true ]);
        $this->unit = Unit::firstOrCreate(['name' => 'Unit'], ['symbol' => 'u', 'description' => 'unit']);

        $this->product = Product::create([
            'code' => 'PROD-01',
            'name' => 'Test Product',
            'form_type' => Product::FORM_SHEET,
            'unit_id' => $this->unit->id,
            'product_type' => Product::TYPE_FINISHED_GOOD,
            'is_active' => true,
        ]);
    }

    public function test_bon_entree_total_is_qty_times_price_for_each_line(): void
    {
        $bon = BonEntree::create([
            'bon_number' => 'BENT-CALC-001',
            'sourceable_type' => Supplier::class,
            'sourceable_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'draft',
            'frais_approche' => 0,
            'total_amount_ht' => 0,
            'total_amount_ttc' => 0,
        ]);

        // Add a bobine (qty 1) with unit price 1000
        $bon->bonEntreeItems()->create([
            'item_type' => 'bobine',
            'product_id' => $this->product->id,
            'qty_entered' => 1,
            'weight_kg' => 100,
            'length_m' => 500,
            'price_ht' => 1000.0,
            'price_ttc' => 1000.0,
            'ean_13' => '0000000000001',
        ]);

        // Add a product (qty 2) price 50
        $bon->bonEntreeItems()->create([
            'item_type' => 'product',
            'product_id' => $this->product->id,
            'qty_entered' => 2,
            'price_ht' => 50.0,
            'price_ttc' => 50.0,
        ]);

        // Recalculate totals
        $bon->recalculateTotals();

        $this->assertEquals(1100.00, (float) $bon->total_amount_ht);
        $this->assertEquals(1100.00, (float) $bon->total_amount_ttc);
    }
}
