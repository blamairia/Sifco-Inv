<?php

namespace Tests\Unit;

use App\Models\BonEntree;
use App\Models\BonSortie;
use App\Models\ProductionLine;
use App\Models\Supplier;
use App\Models\Warehouse;
use Database\Seeders\ProductionLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionLineFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_registers_default_production_lines(): void
    {
        $this->seed(ProductionLineSeeder::class);

        $codes = ProductionLine::orderBy('code')->pluck('code')->toArray();

        $this->assertSame([
            'CURIONI',
            'ETERNA',
            'FOSBER',
            'MACARBOX',
        ], $codes);
    }

    public function test_bon_entree_can_reference_production_line(): void
    {
        $line = ProductionLine::create([
            'name' => 'FOSBER',
            'code' => 'FOSBER',
            'status' => 'active',
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Principal',
            'is_system' => false,
        ]);

        $bon = BonEntree::create([
            'bon_number' => 'BEN-TEST-001',
            'sourceable_type' => ProductionLine::class,
            'sourceable_id' => $line->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'draft',
            'total_amount_ht' => 0,
            'frais_approche' => 0,
            'total_amount_ttc' => 0,
        ]);

        $this->assertTrue($bon->relationLoaded('sourceable') === false);
        $this->assertInstanceOf(ProductionLine::class, $bon->sourceable);
        $this->assertSame('FOSBER', $bon->sourceable->name);
        $this->assertSame($line->id, $bon->productionLine()?->id);
        $this->assertNull($bon->supplier());
    }

    public function test_bon_entree_can_still_reference_supplier(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-01',
            'name' => 'Papier Supplier',
            'contact_person' => 'Contact',
            'phone' => '0000000000',
            'email' => 'supplier@example.com',
            'address' => 'Zone industrielle',
            'payment_terms' => '30d',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Secondaire',
            'is_system' => false,
        ]);

        $bon = BonEntree::create([
            'bon_number' => 'BEN-TEST-002',
            'sourceable_type' => Supplier::class,
            'sourceable_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'draft',
            'total_amount_ht' => 0,
            'frais_approche' => 0,
            'total_amount_ttc' => 0,
        ]);

        $this->assertInstanceOf(Supplier::class, $bon->sourceable);
        $this->assertSame('Papier Supplier', $bon->supplier()?->name);
        $this->assertNull($bon->productionLine());
    }

    public function test_bon_sortie_tracks_production_line_destination(): void
    {
        $line = ProductionLine::create([
            'name' => 'MACARBOX',
            'code' => 'MACARBOX',
            'status' => 'active',
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Expédition',
            'is_system' => false,
        ]);

        $bon = BonSortie::create([
            'bon_number' => 'BSRT-TEST-001',
            'warehouse_id' => $warehouse->id,
            'issued_date' => now()->toDateString(),
            'destination' => 'MACARBOX',
            'destinationable_type' => ProductionLine::class,
            'destinationable_id' => $line->id,
            'status' => 'draft',
        ]);

        $bon->refresh();

        $this->assertInstanceOf(ProductionLine::class, $bon->destinationable);
        $this->assertSame('MACARBOX', $bon->productionLine()?->name);
    }
}
