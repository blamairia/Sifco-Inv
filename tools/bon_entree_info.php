<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\BonEntree;

echo "DB Connection: " . config('database.default') . "\n";
$driver = Schema::getConnection()->getDriverName();
echo "Driver: $driver\n\n";

// Print table columns
$cols = Schema::getColumnListing('bon_entrees');
echo "Columns (" . count($cols) . "):\n";
foreach ($cols as $c) echo " - $c\n";
echo "\n";

// Count rows
$count = BonEntree::count();
 echo "Total bon_entree rows: $count\n\n";

// Print last 10 rows
$rows = BonEntree::orderBy('id', 'desc')->limit(10)->get(['id','bon_number','status','warehouse_id','sourceable_type','sourceable_id','total_amount_ht','total_amount_ttc','created_at']);

if (count($rows) === 0) {
    echo "No entries found.\n";
} else {
    echo "Last entries:\n";
    foreach ($rows as $r) {
        printf("%s | %s | status: %s | wh: %s | src: %s#%s | HT: %s | TTC: %s | created: %s\n",
            $r->id, $r->bon_number, $r->status, $r->warehouse_id, $r->sourceable_type, $r->sourceable_id, $r->total_amount_ht, $r->total_amount_ttc, $r->created_at);
    }
}

echo "\n";

// Index information depending on driver
if ($driver === 'mysql' || $driver === 'mariadb') {
    $indexes = DB::select('SHOW INDEX FROM bon_entrees');
    echo "Indexes (MySQL):\n";
    foreach ($indexes as $idx) {
        echo " - Key name: {$idx->Key_name}, Column: {$idx->Column_name}, Seq: {$idx->Seq_in_index}, Unique: " . ($idx->Non_unique ? 'no' : 'yes') . "\n";
    }
} elseif ($driver === 'sqlite') {
    $indexList = DB::select("PRAGMA index_list('bon_entrees')");
    echo "Indexes (SQLite):\n";
    foreach ($indexList as $idx) {
        echo " - Name: {$idx->name}, unique: " . ($idx->unique ? 'yes' : 'no') . "\n";
        $columns = DB::select("PRAGMA index_info('{$idx->name}')");
        foreach ($columns as $col) {
            echo "    - Column: {$col->name}\n";
        }
    }
} else {
    echo "Index detection not implemented for driver: $driver\n";
}
