<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Resources\BonEntrees\Pages\ListBonEntrees;

$page = new ListBonEntrees();
$ref = new \ReflectionMethod($page, 'getTableQuery');
$ref->setAccessible(true);
$qb = $ref->invoke($page);

echo 'Query: ' . $qb->toSql() . PHP_EOL;
echo 'Bindings: ' . json_encode($qb->getBindings()) . PHP_EOL;
echo 'Count: ' . $qb->count() . PHP_EOL;
