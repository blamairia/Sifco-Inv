<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\BonSortie;
use App\Models\Warehouse;
use App\Models\Product;

class BonSortiePdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_export_returns_pdf_response()
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create(['is_active' => true]);

        $bonSortie = BonSortie::factory()->create([
            'warehouse_id' => $warehouse->id,
            'bon_number' => 'TEST-BS-001',
            'destination' => 'Test Destination',
        ]);

        // create an item
        $bonSortie->bonSortieItems()->create([
            'product_id' => $product->id,
            'item_type' => 'product',
            'qty_issued' => 2,
            'cump_at_issue' => 10.0,
        ]);

        $response = $this->get(route('bonSortie.pdf', [$bonSortie]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
