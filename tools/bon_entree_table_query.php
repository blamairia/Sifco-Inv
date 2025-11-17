<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BonEntree;

echo "Total Count (BonEntree::count()): " . BonEntree::count() . "\n";
$rows = BonEntree::orderBy('created_at','desc')->limit(100)->get();
foreach ($rows as $r) {
    echo "#{$r->id}: {$r->bon_number} | status: {$r->status} | src: {$r->sourceable_type}#{$r->sourceable_id} | wh: {$r->warehouse_id} | created: {$r->created_at}\n";
}
