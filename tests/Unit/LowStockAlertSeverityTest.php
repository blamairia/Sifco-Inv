<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\LowStockAlert;
use App\Models\Product;

class LowStockAlertSeverityTest extends TestCase
{
    use RefreshDatabase;

    public function test_compute_severity_uses_product_thresholds()
    {
        $product = Product::factory()->create([
            'min_stock' => 50,
            'safety_stock' => 20,
        ]);

        $alert = LowStockAlert::create([
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'current_qty' => 10,
            'min_stock' => 50,
            'safety_stock' => 20,
            'severity' => 'HIGH',
            'status' => 'ACTIVE',
        ]);

        $this->assertSame('HIGH', $alert->computeSeverity());

        $alert->current_qty = 0;
        $this->assertSame('CRITICAL', $alert->computeSeverity());

        $alert->current_qty = 40; // <= min_stock (50) => MEDIUM
        $this->assertSame('MEDIUM', $alert->computeSeverity());

        $alert->current_qty = 52; // <= min_stock * 1.1 = 55 => LOW
        $this->assertSame('LOW', $alert->computeSeverity());

        $alert->current_qty = 100; // above thresholds => null
        $this->assertNull($alert->computeSeverity());
    }
}
