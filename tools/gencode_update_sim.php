<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

// Use a distinct name to avoid collisions (no truncation required)

$p = Product::create([
    'name' => 'Auto Regenerate 60',
    'form_type' => Product::FORM_ROLL,
    'grammage' => 60,
    'product_type' => Product::TYPE_RAW_MATERIAL,
]);

echo "Initial product code: " . $p->code . PHP_EOL;

$expectedNew = Product::generateCode($p->name, $p->form_type, Product::FORM_SHEET, $p->grammage, $p->type_papier, $p->flute, $p->laize);

echo "Expected new code (pre-update): " . $expectedNew . PHP_EOL;

$p->form_type = Product::FORM_SHEET;
$p->save();

echo "Code after update: " . $p->fresh()->code . PHP_EOL;

echo "Generate code directly after update call: " . Product::generateCode($p->name, $p->form_type, Product::FORM_SHEET, $p->grammage, $p->type_papier, $p->flute, $p->laize) . PHP_EOL;
echo "Generate code same call as update hook (passing both type & form): " . Product::generateCode($p->name, $p->type ?? null, $p->form_type, $p->grammage, $p->type_papier, $p->flute, $p->laize) . PHP_EOL;
