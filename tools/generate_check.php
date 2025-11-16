<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . "/../bootstrap/app.php";
// Bootstrap kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$name = 'Auto Regenerate 60';
$type = 'papier_roll';
$grammage = 60;

echo "generateCode for SHEET: ";
echo Product::generateCode($name, $type, Product::FORM_SHEET, $grammage) . PHP_EOL;

echo "generateCode for ROLL: ";
echo Product::generateCode($name, $type, Product::FORM_ROLL, $grammage) . PHP_EOL;

// Also generate with type null to see difference

echo "generateCode for SHEET with type null: ";
echo Product::generateCode($name, null, Product::FORM_SHEET, $grammage) . PHP_EOL;

echo "generateCode for ROLL with type null: ";
echo Product::generateCode($name, null, Product::FORM_ROLL, $grammage) . PHP_EOL;
