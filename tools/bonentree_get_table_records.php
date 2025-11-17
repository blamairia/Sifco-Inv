<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Resources\BonEntrees\Pages\ListBonEntrees;

$page = new ListBonEntrees();
$ref = new ReflectionMethod($page, 'getTableRecords');
$ref->setAccessible(true);
$records = $ref->invoke($page);

echo 'Count: ' . count($records) . PHP_EOL;
foreach ($records as $rec) {
    echo $rec['id']." | ".$rec['bon_number'].PHP_EOL;
}
