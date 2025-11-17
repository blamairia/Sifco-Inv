<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BonEntree;

$rows = BonEntree::with(['warehouse', 'sourceable'])->orderBy('id','desc')->limit(10)->get();
foreach ($rows as $r) {
    echo "#{$r->id}: {$r->bon_number} | {".($r->sourceable?->name ?? '—')."} | wh: {".($r->warehouse?->name ?? '—')."} | status: {$r->status}\n";
}
