<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Filament\Tables\Table;
use App\Filament\Resources\BonEntrees\Tables\BonEntreesTable;
use App\Models\BonEntree;

$table = Table::make();
$table = BonEntreesTable::configure($table);

// Using reflection to access protected property 'columns'
$ref = new ReflectionClass($table);
if ($ref->hasProperty('columns')) {
    $prop = $ref->getProperty('columns');
    $prop->setAccessible(true);
    $cols = $prop->getValue($table);
    echo 'Columns count: '.count($cols).PHP_EOL;
    foreach ($cols as $col) {
        echo get_class($col).' | '.($col->getName() ?? 'N/A').PHP_EOL;
    }
}

$records = BonEntree::orderBy('id','desc')->limit(3)->get();
foreach ($records as $r) {
    echo 'Record: '.$r->id.' - '.$r->bon_number.PHP_EOL;
    foreach ($cols as $col) {
        try {
            // Attempt to get state via public method 'getState' if exists
            if (method_exists($col, 'getState')) {
                $state = $col->getStateUsing() ? $col->getStateUsing()($r) : null;
                echo '  '.$col->getName().': '.(is_null($state) ? 'NULL' : (is_string($state) ? $state : json_encode($state))).PHP_EOL;
            }
        } catch (Throwable $e) {
            echo '  ERROR evaluating column '.$col->getName().': '.$e->getMessage().PHP_EOL;
        }
    }
}
