<?php

namespace Tests\Feature;

use App\Models\BonEntree;
use App\Models\BonEntreeItem;
use App\Models\BonReintegration;
use App\Models\BonReintegrationItem;
use App\Models\BonSortie;
use App\Models\BonSortieItem;
use App\Models\BonTransfert;
use App\Models\BonTransfertItem;
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
            'code' => 'TEST-WH',
            'warehouse_type' => 'storage',
            'address' => '123 Test St',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Test Supplier',
            'code' => 'SUP-001',
            'contact_name' => 'John Doe',
            'phone' => '123456789',
        ]);
        
        $this->unit = Unit::firstOrCreate(
            ['name' => 'kg'],
            ['symbol' => 'kg', 'type' => 'weight']
        );

        $this->product = Product::create([
            'name' => 'Test Roll Product',
            'code' => 'ROLL-001',
            'unit_id' => $this->unit->id,
            'type' => 'papier_roll',
        ]);
    }

    public function test_roll_reception_creates_lifecycle_event(): void
    {
        $service = app(BonEntreeService::class);

        $bonEntree = BonEntree::create([
            'bon_number' => 'BE-TEST-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'entry_type' => 'purchase',
            'expected_date' => now()->addDay(),
        ]);

        $item = BonEntreeItem::create([
            'bon_entree_id' => $bonEntree->id,
            'product_id' => $this->product->id,
            'item_type' => 'bobine',
            'qty_ordered' => 1,
            'qty_entered' => 1,
            'price_ht' => 1000.0,
            'price_ttc' => 1200.0,
            'weight_kg' => 250.0,
            'length_m' => 1500.0,
            'ean_13' => '1234567890123',
            'grammage' => 80,
            'laize' => 100,
            'quality' => 'A',
        ]);

        $service->receive($bonEntree);

        $roll = Roll::where('ean_13', '1234567890123')->first();
        $this->assertNotNull($roll);

        $event = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'RECEPTION')
            ->first();

        $this->assertNotNull($event, 'Reception lifecycle event should be created');
        $this->assertEquals('BE-TEST-001', $event->reference_number);
        $this->assertEquals($this->warehouse->id, $event->warehouse_to_id);
        $this->assertEquals(1500.0, (float) $event->length_after_m);
        $this->assertEquals(250.0, (float) $event->weight_after_kg);
        $this->assertNotNull($event->stock_movement_id);
    }

    public function test_roll_sortie_creates_lifecycle_event(): void
    {
        // Create roll in stock
        $roll = Roll::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'ean_13' => '1111111111111',
            'weight_kg' => 200.0,
            'length_m' => 1200.0,
            'status' => Roll::STATUS_IN_STOCK,
            'grammage' => 80,
            'laize' => 100,
            'quality' => 'A',
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

        // Create sortie
        $bonSortie = BonSortie::create([
            'bon_number' => 'BS-TEST-001',
            'warehouse_id' => $this->warehouse->id,
            'destination' => 'Production',
            'status' => 'draft',
            'sortie_type' => 'production',
            'sortie_date' => now(),
            'issued_date' => now(),
        ]);

        BonSortieItem::create([
            'bon_sortie_id' => $bonSortie->id,
            'product_id' => $this->product->id,
            'item_type' => 'bobine',
            'roll_id' => $roll->id,
            'qty_issued' => 1,
            'weight_issued_kg' => 200.0,
            'length_issued_m' => 1200.0,
            'cump_at_issue' => 1000.0,
        ]);

        $service = app(BonSortieService::class);
        $service->issue($bonSortie);

        $event = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'SORTIE')
            ->first();

        $this->assertNotNull($event, 'Sortie lifecycle event should be created');
        $this->assertEquals('BS-TEST-001', $event->reference_number);
        $this->assertEquals($this->warehouse->id, $event->warehouse_from_id);
        $this->assertEquals(1200.0, (float) $event->length_before_m);
        $this->assertEquals(200.0, (float) $event->weight_before_kg);
    }

    public function test_roll_reintegration_creates_lifecycle_event(): void
    {
        // Create consumed roll
        $roll = Roll::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'ean_13' => '2222222222222',
            'weight' => 200.0,
            'length_m' => 0,
            'status' => Roll::STATUS_CONSUMED,
            'grammage' => 80,
            'laize' => 100,
            'quality' => 'A',
            'received_date' => now()->subDays(5),
        ]);

        // Create a sortie record first (required for reintegration)
        $bonSortie = BonSortie::create([
            'bon_number' => 'BS-REF-001',
            'warehouse_id' => $this->warehouse->id,
            'destination' => 'Production',
            'status' => 'issued',
            'sortie_type' => 'production',
            'sortie_date' => now()->subDays(5),
            'issued_date' => now()->subDays(5),
        ]);

        // Create reintegration
        $bonReintegration = BonReintegration::create([
            'bon_number' => 'BR-TEST-001',
            'warehouse_id' => $this->warehouse->id,
            'bon_sortie_id' => $bonSortie->id,
            'status' => 'draft',
            'reintegration_date' => now(),
            'return_date' => now(),
            'cump_at_return' => 1000.0,
        ]);

        BonReintegrationItem::create([
            'bon_reintegration_id' => $bonReintegration->id,
            'product_id' => $this->product->id,
            'item_type' => 'roll',
            'roll_id' => $roll->id,
            'qty_returned' => 1,
            'value_returned' => 1000.0,
            'previous_weight_kg' => 200.0,
            'returned_weight_kg' => 50.0,
            'weight_delta_kg' => -150.0,
            'previous_length_m' => 0,
            'returned_length_m' => 300.0,
            'length_delta_m' => 300.0,
        ]);

        $service = app(BonReintegrationService::class);
        $service->receive($bonReintegration);

        $event = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'REINTEGRATION')
            ->first();

        $this->assertNotNull($event, 'Reintegration lifecycle event should be created');
        $this->assertEquals('BR-TEST-001', $event->reference_number);
        $this->assertEquals($this->warehouse->id, $event->warehouse_to_id);
        $this->assertEquals(300.0, (float) $event->length_after_m);
        $this->assertEquals(50.0, (float) $event->weight_after_kg);
        $this->assertEquals(150.0, (float) $event->waste_weight_kg);
        $this->assertEquals(0, (float) $event->waste_length_m);
    }

    public function test_roll_transfer_creates_transfer_events(): void
    {
        $warehouseFrom = $this->warehouse;
        $warehouseTo = Warehouse::create([
            'name' => 'Destination Warehouse',
            'code' => 'DEST-WH',
            'warehouse_type' => 'storage',
            'address' => '456 Dest St',
        ]);

        // Create roll in source warehouse
        $roll = Roll::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $warehouseFrom->id,
            'ean_13' => '3333333333333',
            'weight_kg' => 180.0,
            'length_m' => 1100.0,
            'status' => Roll::STATUS_IN_STOCK,
            'grammage' => 80,
            'laize' => 100,
            'quality' => 'A',
            'received_date' => now(),
        ]);

        StockQuantity::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $warehouseFrom->id,
            'total_qty' => 1,
            'total_weight_kg' => 180.0,
            'total_length_m' => 1100.0,
            'reserved_qty' => 0,
            'cump_snapshot' => 1000.0,
        ]);

        // Create transfer
        $bonTransfert = BonTransfert::create([
            'bon_number' => 'BT-TEST-001',
            'warehouse_from_id' => $warehouseFrom->id,
            'warehouse_to_id' => $warehouseTo->id,
            'status' => 'draft',
            'transfer_date' => now(),
        ]);

        BonTransfertItem::create([
            'bon_transfert_id' => $bonTransfert->id,
            'product_id' => $this->product->id,
            'item_type' => 'roll',
            'roll_id' => $roll->id,
            'qty_transferred' => 1,
            'weight_transferred_kg' => 180.0,
            'length_transferred_m' => 1100.0,
            'cump_at_transfer' => 1000.0,
        ]);

        $service = app(BonTransfertService::class);
        $service->transfer($bonTransfert);

        // Check transfer start event
        $transferEvent = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'TRANSFER')
            ->first();

        $this->assertNotNull($transferEvent, 'Transfer start lifecycle event should be created');
        $this->assertEquals('BT-TEST-001', $transferEvent->reference_number);
        $this->assertEquals($warehouseFrom->id, $transferEvent->warehouse_from_id);
        $this->assertEquals($warehouseTo->id, $transferEvent->warehouse_to_id);
        $this->assertEquals(1100.0, (float) $transferEvent->length_after_m);
        $this->assertEquals(180.0, (float) $transferEvent->weight_after_kg);

        // Now receive the transfer
        $service->receive($bonTransfert);

        // Check transfer completed event
        $completedEvent = RollLifecycleEvent::where('roll_id', $roll->id)
            ->where('event_type', 'TRANSFER_COMPLETED')
            ->first();

        $this->assertNotNull($completedEvent, 'Transfer completed lifecycle event should be created');
        $this->assertEquals('BT-TEST-001', $completedEvent->reference_number);
        $this->assertEquals($warehouseTo->id, $completedEvent->warehouse_to_id);
        $this->assertEquals(1100.0, (float) $completedEvent->length_after_m);
        $this->assertEquals(180.0, (float) $completedEvent->weight_after_kg);
    }

    public function test_lifecycle_events_maintain_chronological_order(): void
    {
        $service = app(BonEntreeService::class);

        // 1. Reception
        $bonEntree = BonEntree::create([
            'bon_number' => 'BE-LIFE-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'pending',
            'entry_type' => 'purchase',
            'expected_date' => now()->addDay(),
        ]);

        BonEntreeItem::create([
            'bon_entree_id' => $bonEntree->id,
            'product_id' => $this->product->id,
            'item_type' => 'bobine',
            'qty_ordered' => 1,
            'qty_entered' => 1,
            'ean_13' => '9999999999999',
            'unit_price' => 1000.0,
            'price_ht' => 1000.0,
            'price_ttc' => 1000.0,
            'weight_kg' => 250.0,
            'length_m' => 1500.0,
        ]);

        $service->receive($bonEntree);

        $roll = Roll::where('ean_13', '9999999999999')->first();
        $this->assertNotNull($roll);

        sleep(1); // Ensure different timestamps

        // 2. Sortie
        $bonSortie = BonSortie::create([
            'bon_number' => 'BS-LIFE-001',
            'warehouse_id' => $this->warehouse->id,
            'destination' => 'Production',
            'status' => 'draft',
            'sortie_type' => 'production',
            'sortie_date' => now(),
            'issued_date' => now(),
        ]);

        BonSortieItem::create([
            'bon_sortie_id' => $bonSortie->id,
            'product_id' => $this->product->id,
            'item_type' => 'bobine',
            'roll_id' => $roll->id,
            'qty_issued' => 1,
            'weight_issued_kg' => 250.0,
            'length_issued_m' => 1500.0,
            'cump_at_issue' => 1000.0,
        ]);

        $sortieService = app(BonSortieService::class);
        $sortieService->issue($bonSortie);

        // Check events are in chronological order
        $events = RollLifecycleEvent::where('roll_id', $roll->id)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(2, $events);
        $this->assertEquals('RECEPTION', $events[0]->event_type);
        $this->assertEquals('SORTIE', $events[1]->event_type);
        $this->assertTrue($events[0]->created_at < $events[1]->created_at);
    }
}
