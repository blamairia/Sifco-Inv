<?php

namespace Tests\Feature;

use App\Models\BonEntree;
use App\Models\BonReintegration;
use App\Models\BonSortie;
use App\Models\BonTransfert;
use App\Models\Product;
use App\Models\Roll;
use App\Models\RollLifecycleEvent;
use App\Models\StockQuantity;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\BonEntreeService;
use App\Services\BonReintegrationService;
use App\Services\BonSortieService;
use App\Services\BonTransfertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RollLifecycleEventTest extends TestCase
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

        $this->warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'is_system' => false,
        ]);

        $this->supplier = Supplier::create([
            'code' => 'SUP-001',
            'name' => 'Test Supplier',
            'contact_person' => 'John Doe',
            'phone' => '123456789',
            'email' => 'supplier@example.com',
            'address' => 'Zone industrielle',
            'payment_terms' => '30d',
            'is_active' => true,
        ]);

        $this->unit = Unit::firstOrCreate(
            ['name' => 'Kilogramme'],
            ['symbol' => 'kg', 'description' => 'Unité de masse standard']
        );

        $this->product = Product::create([
            'code' => 'ROLL-001',
            'name' => 'Test Roll Product',
            'type' => 'papier_roll',
            'unit_id' => $this->unit->id,
            'product_type' => Product::TYPE_RAW_MATERIAL,
            'is_active' => true,
            'is_roll' => true,
            'min_stock' => 0,
            'safety_stock' => 0,
            'grammage' => 80,
            'laize' => 100,
        ]);
    }

    protected function createPendingBonEntree(string $bonNumber = 'BE-TEST-001'): BonEntree
    {
        return BonEntree::create([
            'bon_number' => $bonNumber,
            'sourceable_type' => Supplier::class,
            'sourceable_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'document_number' => 'DOC-' . $bonNumber,
            'expected_date' => now()->addDay(),
            'status' => 'pending',
            'total_amount_ht' => 0,
            'frais_approche' => 0,
            'total_amount_ttc' => 0,
        ]);
    }

    public function test_roll_reception_creates_lifecycle_event(): void
    {
        $service = app(BonEntreeService::class);
        $bonEntree = $this->createPendingBonEntree();

        $bonEntree->bonEntreeItems()->create([
            'item_type' => 'bobine',
            'product_id' => $this->product->id,
            'qty_entered' => 1,
            'weight_kg' => 250.0,
            'length_m' => 1500.0,
            'price_ht' => 1000.0,
            'price_ttc' => 1200.0,
            'ean_13' => '1234567890123',
            'batch_number' => 'LOT-001',
        ]);

        $service->receive($bonEntree->fresh());

        $roll = Roll::where('ean_13', '1234567890123')->firstOrFail();

        $event = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'RECEPTION')
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals('BE-TEST-001', $event->reference_number);
        $this->assertEquals($this->warehouse->id, $event->warehouse_to_id);
        $this->assertEquals(1500.0, (float) $event->length_after_m);
        $this->assertEquals(250.0, (float) $event->weight_after_kg);
        $this->assertNotNull($event->stock_movement_id);
    }

    public function test_roll_sortie_creates_lifecycle_event(): void
    {
        $roll = Roll::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'ean_13' => '1111111111111',
            'status' => Roll::STATUS_IN_STOCK,
            'weight_kg' => 200.0,
            'length_m' => 1200.0,
            'cump_value' => 1000.0,
            'received_date' => now(),
        ]);

        StockQuantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'total_qty' => 1,
            'total_weight_kg' => 200.0,
            'total_length_m' => 1200.0,
            'reserved_qty' => 0,
            'cump_snapshot' => 1000.0,
        ]);

        $bonSortie = BonSortie::create([
            'bon_number' => 'BS-TEST-001',
            'warehouse_id' => $this->warehouse->id,
            'destination' => 'Production',
            'status' => 'draft',
            'issued_date' => now()->toDateString(),
            'notes' => 'Sortie de test',
        ]);

        $bonSortie->bonSortieItems()->create([
            'product_id' => $this->product->id,
            'item_type' => 'roll',
            'roll_id' => $roll->id,
            'qty_issued' => 1,
            'weight_kg' => 200.0,
            'length_m' => 1200.0,
            'cump_at_issue' => 1000.0,
        ]);

        $service = app(BonSortieService::class);
        $service->issue($bonSortie->fresh());

        $event = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'SORTIE')
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals('BS-TEST-001', $event->reference_number);
        $this->assertEquals($this->warehouse->id, $event->warehouse_from_id);
        $this->assertEquals(1200.0, (float) $event->length_before_m);
        $this->assertEquals(200.0, (float) $event->weight_before_kg);
    }

    public function test_roll_reintegration_creates_lifecycle_event(): void
    {
        $roll = Roll::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'ean_13' => '2222222222222',
            'status' => Roll::STATUS_CONSUMED,
            'weight_kg' => 200.0,
            'length_m' => 0,
            'cump_value' => 1000.0,
            'received_date' => now()->subDays(5),
        ]);

        $bonSortie = BonSortie::create([
            'bon_number' => 'BS-REF-001',
            'warehouse_id' => $this->warehouse->id,
            'destination' => 'Production',
            'status' => 'issued',
            'issued_date' => now()->subDays(5)->toDateString(),
            'notes' => 'Sortie précédente',
        ]);

        $bonReintegration = BonReintegration::create([
            'bon_number' => 'BR-TEST-001',
            'bon_sortie_id' => $bonSortie->id,
            'warehouse_id' => $this->warehouse->id,
            'return_date' => now()->toDateString(),
            'status' => 'draft',
            'cump_at_return' => 1000.0,
            'notes' => 'Retour partiel',
        ]);

        $bonReintegration->bonReintegrationItems()->create([
            'item_type' => 'roll',
            'product_id' => $this->product->id,
            'roll_id' => $roll->id,
            'qty_returned' => 1,
            'previous_weight_kg' => 200.0,
            'returned_weight_kg' => 50.0,
            'weight_delta_kg' => -150.0,
            'previous_length_m' => 0,
            'returned_length_m' => 300.0,
            'length_delta_m' => 300.0,
            'cump_at_return' => 1000.0,
            'value_returned' => 1000.0,
        ]);

        $service = app(BonReintegrationService::class);
        $service->receive($bonReintegration->fresh());

        $event = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'REINTEGRATION')
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals('BR-TEST-001', $event->reference_number);
        $this->assertEquals($this->warehouse->id, $event->warehouse_to_id);
        $this->assertEquals(300.0, (float) $event->length_after_m);
        $this->assertEquals(50.0, (float) $event->weight_after_kg);
        $this->assertEquals(150.0, (float) $event->waste_weight_kg);
        $this->assertEquals(0.0, (float) $event->waste_length_m);
    }

    public function test_roll_transfer_creates_transfer_events(): void
    {
        $warehouseTo = Warehouse::create([
            'name' => 'Destination Warehouse',
            'is_system' => false,
        ]);

        $roll = Roll::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'ean_13' => '3333333333333',
            'status' => Roll::STATUS_IN_STOCK,
            'weight_kg' => 180.0,
            'length_m' => 1100.0,
            'cump_value' => 1000.0,
            'received_date' => now(),
        ]);

        StockQuantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'total_qty' => 1,
            'total_weight_kg' => 180.0,
            'total_length_m' => 1100.0,
            'reserved_qty' => 0,
            'cump_snapshot' => 1000.0,
        ]);

        $bonTransfert = BonTransfert::create([
            'bon_number' => 'BT-TEST-001',
            'warehouse_from_id' => $this->warehouse->id,
            'warehouse_to_id' => $warehouseTo->id,
            'transfer_date' => now()->toDateString(),
            'status' => 'draft',
        ]);

        $bonTransfert->bonTransfertItems()->create([
            'item_type' => 'roll',
            'product_id' => $this->product->id,
            'roll_id' => $roll->id,
            'qty_transferred' => 1,
            'cump_at_transfer' => 1000.0,
        ]);

        $service = app(BonTransfertService::class);
        $service->transfer($bonTransfert->fresh());

        $transferEvent = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'TRANSFER')
            ->first();

        $this->assertNotNull($transferEvent);
        $this->assertEquals('BT-TEST-001', $transferEvent->reference_number);
        $this->assertEquals($this->warehouse->id, $transferEvent->warehouse_from_id);
        $this->assertEquals($warehouseTo->id, $transferEvent->warehouse_to_id);
        $this->assertEquals(1100.0, (float) $transferEvent->length_after_m);
        $this->assertEquals(180.0, (float) $transferEvent->weight_after_kg);

        $service->receive($bonTransfert->fresh());

        $completedEvent = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'TRANSFER_COMPLETED')
            ->first();

        $this->assertNotNull($completedEvent);
        $this->assertEquals('BT-TEST-001', $completedEvent->reference_number);
        $this->assertEquals($warehouseTo->id, $completedEvent->warehouse_to_id);
        $this->assertEquals(1100.0, (float) $completedEvent->length_after_m);
        $this->assertEquals(180.0, (float) $completedEvent->weight_after_kg);
    }

    public function test_lifecycle_events_maintain_chronological_order(): void
    {
        $service = app(BonEntreeService::class);
        $bonEntree = $this->createPendingBonEntree('BE-LIFE-001');

        $bonEntree->bonEntreeItems()->create([
            'item_type' => 'bobine',
            'product_id' => $this->product->id,
            'qty_entered' => 1,
            'weight_kg' => 250.0,
            'length_m' => 1500.0,
            'price_ht' => 1000.0,
            'price_ttc' => 1000.0,
            'ean_13' => '9999999999999',
            'batch_number' => 'LOT-LIFE',
        ]);

        $service->receive($bonEntree->fresh());

        $roll = Roll::where('ean_13', '9999999999999')->firstOrFail();

        sleep(1);

        $bonSortie = BonSortie::create([
            'bon_number' => 'BS-LIFE-001',
            'warehouse_id' => $this->warehouse->id,
            'destination' => 'Production',
            'status' => 'draft',
            'issued_date' => now()->toDateString(),
            'notes' => 'Sortie chronologique',
        ]);

        $bonSortie->bonSortieItems()->create([
            'product_id' => $this->product->id,
            'item_type' => 'roll',
            'roll_id' => $roll->id,
            'qty_issued' => 1,
            'weight_kg' => 250.0,
            'length_m' => 1500.0,
            'cump_at_issue' => 1000.0,
        ]);

        $sortieService = app(BonSortieService::class);
        $sortieService->issue($bonSortie->fresh());

        $events = RollLifecycleEvent::where('roll_id', $roll->id)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(2, $events);
        $this->assertEquals('RECEPTION', $events[0]->event_type);
        $this->assertEquals('SORTIE', $events[1]->event_type);
        $this->assertTrue($events[0]->created_at->lt($events[1]->created_at));
    }
}
