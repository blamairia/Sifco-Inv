<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Unit;
use App\Models\Product;
use App\Models\Category;
use App\Models\ImportRecord;
use App\Models\BonEntree;
use App\Models\BonEntreeItem;
use App\Models\BonSortie;
use App\Models\BonSortieItem;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ConsumableImporter
{
    protected HeaderDetector $headers;
    protected UnitNormalizer $unitNormalizer;
    protected CategorySuggester $categorySuggester;
    protected SkuGenerator $skuGenerator;

    public function __construct()
    {
        $this->headers = new HeaderDetector();
        $this->unitNormalizer = new UnitNormalizer();
        $this->categorySuggester = new CategorySuggester();
        $this->skuGenerator = new SkuGenerator();
    }

    public function import(string $file, bool $dryRun = true, ?int $limit = null): array
    {
        // If phpspreadsheet is not installed, we only run a lightweight profile
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            return $this->profileWorkbook($file);
        }

        $spreadsheet = IOFactory::load($file);
        $sheetCount = $spreadsheet->getSheetCount();
        $logs = [];
        $summary = ['sheets' => $sheetCount, 'products' => 0, 'bon_entrees' => 0, 'bon_sorties' => 0, 'skipped_rows' => 0, 'issues' => []];

        for ($i = 0; $i < $sheetCount; $i++) {
            $sheet = $spreadsheet->getSheet($i);
            $sheetTitle = $sheet->getTitle();
            $rows = $sheet->toArray(null, true, true, true);

            $headerInfo = $this->headers->detect($rows);
            $logs[] = ['sheet' => $sheetTitle, 'header' => $headerInfo];

            if ($headerInfo === null) {
                $summary['issues'][] = "Header not found for sheet {$sheetTitle}";
                continue;
            }

            $rowIndex = $headerInfo['rowIndex'];
            $mapping = $headerInfo['mapping'];
            $dataStart = $rowIndex + 1;
            $maxRows = $limit ? min($dataStart + $limit, count($rows)) : count($rows);

            for ($r = $dataStart; $r <= $maxRows; $r++) {
                $row = $rows[$r] ?? null;
                if (!$row || empty(array_filter($row))) {
                    continue;
                }

                $descr = trim($row[$mapping['DESCRIPTION']] ?? '');
                if (stripos($descr, 'TOTAL') !== false) {
                    continue;
                }

                $unitRaw = $row[$mapping['UNITE']] ?? null;
                $unitName = $this->unitNormalizer->normalize($unitRaw);
                $opening = $this->toNumber($row[$mapping['STOCK_INT']] ?? 0);
                $reception = $this->toNumber($row[$mapping['RECEPTION']] ?? 0);
                $sortie = $this->toNumber($row[$mapping['SORTIE']] ?? 0);
                $price = $this->toNumber($row[$mapping['PRIX']] ?? 0);
                $value = $this->toNumber($row[$mapping['VALEUR']] ?? 0);
                $closing = $this->toNumber($row[$mapping['STOCK']] ?? 0);

                if ($opening + $reception - $sortie !== $closing) {
                    $summary['issues'][] = [
                        'sheet' => $sheetTitle,
                        'row' => $r + 1,
                        'description' => $descr,
                        'opening' => $opening,
                        'reception' => $reception,
                        'sortie' => $sortie,
                        'closing' => $closing,
                        'delta' => $closing - ($opening + $reception - $sortie),
                    ];
                }

                // product identification and creation
                $legacyKey = strtoupper(trim($descr . '|' . $unitName));
                $product = Product::where('name', trim($descr))->whereHas('unit', function ($q) use ($unitName) {
                    $q->where('name', $unitName);
                })->first();

                if (!$product) {
                    // create unit if needed
                    $unit = Unit::firstOrCreate(['name' => $unitName], ['symbol' => $unitName]);
                    // find category
                    $primaryCategory = Category::firstOrCreate(['name' => 'Consommables']);
                    $subcategory = $this->categorySuggester->suggest($descr);
                    $subCategory = Category::firstOrCreate(['name' => $subcategory, 'parent_id' => $primaryCategory->id]);

                    $sku = $this->skuGenerator->generate($descr, $unitName, 1);
                    $product = Product::create([
                        'code' => $sku,
                        'name' => trim($descr),
                        'product_type' => Product::TYPE_RAW_MATERIAL,
                        'form_type' => Product::FORM_CONSUMABLE,
                        'unit_id' => $unit->id,
                        'is_active' => true,
                        'min_stock' => 0,
                        'safety_stock' => 0,
                    ]);

                    $product->categories()->attach($subCategory->id, ['is_primary' => true]);
                    $summary['products']++;
                }

                // now create movements
                $date = $this->parseSheetDate($sheetTitle);
                // external id for idempotency:
                $externalBase = implode('|', [$sheetTitle, $r, $date, $descr, $unitName]);

                if ($reception > 0) {
                    $externalId = sha1($externalBase . '|RECEPTION|' . $reception);
                    if (ImportRecord::where('external_id', $externalId)->exists()) {
                        continue; // skip duplicate
                    }
                    if (!$dryRun) {
                        $bon = BonEntree::create([
                            'bon_number' => BonEntree::generateBonNumber(),
                            'warehouse_id' => 1,
                            'received_date' => $date,
                            'status' => 'received',
                            'document_number' => $sheetTitle,
                        ]);

                        BonEntreeItem::create([
                            'bon_entree_id' => $bon->id,
                            'item_type' => 'product',
                            'product_id' => $product->id,
                            'qty_entered' => $reception,
                            'price_ht' => $price,
                            'price_ttc' => $price,
                        ]);
                        $summary['bon_entrees']++;
                        ImportRecord::create([
                            'external_id' => $externalId,
                            'model_type' => BonEntree::class,
                            'model_id' => $bon->id,
                            'payload' => ['qty' => $reception, 'row' => $r],
                        ]);
                    }
                }

                if ($sortie > 0) {
                    $externalId = sha1($externalBase . '|SORTIE|' . $sortie);
                    if (ImportRecord::where('external_id', $externalId)->exists()) {
                        continue; // skip duplicate
                    }
                    if (!$dryRun) {
                        $bon = BonSortie::create([
                            'bon_number' => sprintf('BSRT-%s', now()->format('Ymd')), 
                            'warehouse_id' => 1,
                            'document_number' => $sheetTitle,
                            'status' => 'issued',
                            'created_at' => $date,
                        ]);

                        BonSortieItem::create([
                            'bon_sortie_id' => $bon->id,
                            'product_id' => $product->id,
                            'qty_issued' => $sortie,
                            'price_ttc' => $price,
                        ]);
                        $summary['bon_sorties']++;
                        ImportRecord::create([
                            'external_id' => $externalId,
                            'model_type' => BonSortie::class,
                            'model_id' => $bon->id,
                            'payload' => ['qty' => $sortie, 'row' => $r],
                        ]);
                    }
                }
            }
        }

        // write logs
        $logdir = storage_path('app/import_logs');
        if (!is_dir($logdir)) {
            mkdir($logdir, 0755, true);
        }

        file_put_contents($logdir . '/profile.json', json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($logdir . '/summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ['summary' => $summary, 'logs' => $logs];
    }

    protected function toNumber($value): float
    {
        if (is_numeric($value)) return (float) $value;
        $str = str_replace([',', ' '], ['', ''], (string)$value);
        return is_numeric($str) ? (float)$str : 0.0;
    }

    protected function parseSheetDate(string $title): string
    {
        // Expect DDMM
        if (preg_match('/^(\d{2})(\d{2})$/', $title, $m)) {
            $day = $m[1]; $month = $m[2];
            return Carbon::create(2025, (int)$month, (int)$day)->toDateString();
        }

        return Carbon::now()->toDateString();
    }

    public function profileWorkbook(string $file): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            return ['error' => 'Cannot open workbook'];
        }

        // load shared strings
        $shared = [];
        $si = $zip->getFromName('xl/sharedStrings.xml');
        if ($si) {
            $xml = simplexml_load_string($si);
            foreach ($xml->si as $s) {
                $shared[] = (string)$s->t;
            }
        }

        $wb = $zip->getFromName('xl/workbook.xml');
        $xmlWB = simplexml_load_string($wb);
        $sheets = [];
        foreach ($xmlWB->sheets->sheet as $sheet) {
            $sheetName = (string)$sheet['name'];
            $rId = (string)$sheet['id'];
            // map rid to actual file by workbook rels
            $sheets[] = $sheetName;
        }

    $profile = [];
    $seenCombos = [];
    $units = [];
        // list sheet xml files under /xl/worksheets
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^xl/worksheets/sheet(\d+)\.xml$#', $name, $m)) {
                $sheetIndex = (int)$m[1];
                $sheetName = $sheets[$sheetIndex - 1] ?? 'Sheet-' . $sheetIndex;
                $content = $zip->getFromName($name);
                $xml = simplexml_load_string($content);
                $rows = [];
                $normRows = [];
                foreach ($xml->sheetData->row as $row) {
                    $cells = [];
                    foreach ($row->c as $cell) {
                        $v = (string)$cell->v;
                        $t = (string)$cell['t'];
                        if ($t === 's') {
                            $val = $shared[(int)$v] ?? $v;
                        } else {
                            $val = $v;
                        }
                        $cells[] = $val;
                    }
                    $rows[] = $cells;
                    $normRows[] = array_map(function ($c) {
                        return HeaderDetector::removeAccents(strtoupper(trim((string)$c))); }, $cells);
                    if (count($rows) >= 20) break;
                }

                $header = $this->headers->detect($rows);
                // collect unique combos and units
                foreach ($rows as $r) {
                    $d = trim($r[0] ?? '');
                    $u = trim($r[3] ?? '');
                    if ($d !== '') {
                        $key = strtoupper($d . '|' . ($u ?: 'UNITE'));
                        $seenCombos[$key] = true;
                        $units[strtoupper($u ?: 'UNITE')] = true;
                    }
                }

                $profile[] = ['sheet' => $sheetName, 'header' => $header, 'sample' => array_slice($rows, 0, 3), 'normalized_sample' => array_slice($normRows, 0, 3)];
            }
        }

        $zip->close();
        // write profile logs
        $logdir = storage_path('app/import_logs');
        if (!is_dir($logdir)) mkdir($logdir, 0755, true);
    file_put_contents($logdir . '/profile.json', json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($logdir . '/unit_summary.json', json_encode(array_keys($units), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($logdir . '/unique_combos.json', json_encode(['count' => count($seenCombos), 'samples' => array_slice(array_keys($seenCombos), 0, 20)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return ['profile' => $profile, 'units' => array_keys($units), 'unique_count' => count($seenCombos)];
    }
}
