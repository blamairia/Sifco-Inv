<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\BonEntree;
use App\Models\BonEntreeItem;
use App\Filament\Resources\BonEntrees\Pages\CreateBonEntree;

// Create supporting data
$supplier = Supplier::first() ?? Supplier::create(['code' => 'SUP-01','name' => 'Supplier Test']);
$warehouse = Warehouse::first() ?? Warehouse::create(['name' => 'Main', 'is_system' => false]);
$product = Product::first() ?? Product::create(['name' => 'Test Roll', 'form_type' => Product::FORM_ROLL, 'product_type' => Product::TYPE_RAW_MATERIAL]);

// Prepare form-like data
$data = [
    'bon_number' => BonEntree::generateBonNumber(),
    'sourceable_type' => Supplier::class,
    'sourceable_id' => $supplier->id,
    'warehouse_id' => $warehouse->id,
    'expected_date' => now()->toDateString(),
    'status' => 'draft',
    'frais_approche' => 0,
    'bobineItems' => [
        [
            'product_id' => $product->id,
            'ean_13' => '2990000000001',
            'weight_kg' => 10,
            'length_m' => 1000,
            'price_ht' => 1000,
            'price_ttc' => 1000,
            'qty_entered' => 1,
        ],
    ],
    'productItems' => [],
];

$page = new CreateBonEntree();

$ref = new ReflectionMethod(CreateBonEntree::class, 'mutateFormDataBeforeCreate');
$ref->setAccessible(true);
$mutated = $ref->invoke($page, $data);

echo "Mutated data:\n";
print_r($mutated);

// Try to create BonEntree with mutated data
try {
    $bon = BonEntree::create($mutated);
    echo "Created BonEntree ID: {$bon->id}\n";

    // Create items
    foreach ($data['bobineItems'] as $item) {
        $item['bon_entree_id'] = $bon->id;
        BonEntreeItem::create(array_merge($item, ['item_type' => 'bobine']));
    }

    echo "BonEntree and items created OK.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}



