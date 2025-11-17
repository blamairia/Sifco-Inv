<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BonSortie;
use App\Models\ProductionLine;
use App\Models\Warehouse;

$pl = ProductionLine::first();
$wh = Warehouse::first();

$data = [
    'bon_number' => BonSortie::generateBonNumber(),
    'warehouse_id' => $wh->id,
    'destinationable_type' => ProductionLine::class,
    'destinationable_id' => $pl->id,
    'issued_date' => now(),
    'notes' => 'Test create via script',
    'status' => 'draft',
];

try {
    $bon = BonSortie::create($data);
    echo "Created BonSortie ID: {$bon->id}. destination: {$bon->destination}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
