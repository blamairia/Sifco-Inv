<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Console\Command;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;

class ImportProductsFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Accepts an optional path and optional defaults for product_type and form_type.
     */
    protected $signature = 'import:products {path? : Path to the Excel file} {--product_type=raw_material} {--form_type=consumable} {--skip-headers=0 : Number of header rows to skip (default: 0)}';

    /**
     * The console command description.
     */
    protected $description = 'Import products from an Excel workbook and upsert into products table (maps to Product model)';

    public function handle(): int
    {
        $path = $this->argument('path') ?? base_path('EXCEL/ETAT CONSOMATION MC.xlsx');
        $productTypeDefault = $this->option('product_type');
        $formTypeDefault = $this->option('form_type');
        $skipHeaders = (int) $this->option('skip-headers');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info("Reading workbook: {$path}");

    // Use the XLSX reader directly (OpenSpout v4 uses type-specific readers)
    $reader = new XLSXReader();
    $reader->open($path);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $seenCodes = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $this->line("Processing sheet: " . $sheet->getName());

            $rowIterator = $sheet->getRowIterator();
            $headerMap = null;
            $rowIndex = 0;

            foreach ($rowIterator as $rowEntity) {
                // Convert OpenSpout Row entity to plain array of values
                $row = is_object($rowEntity) && method_exists($rowEntity, 'toArray') ? $rowEntity->toArray() : (array) $rowEntity;
                $rowIndex++;
                if ($rowIndex <= $skipHeaders) {
                    continue;
                }

                // Determine header mapping on first non-empty row if not set
                if ($headerMap === null) {
                    $normalized = array_map(function ($v) {
                        return is_string($v) ? strtolower(trim($v)) : '';
                    }, $row);

                    // Heuristics: if row contains common header names, treat it as header
                    $headerCandidates = ['code', 'ref', 'reference', 'name', 'produit', 'unite', 'unit', 'grammage', 'laize', 'width', 'flute', 'type', 'type_papier', 'description'];
                    $foundHeader = false;
                    foreach ($normalized as $cell) {
                        if (in_array($cell, $headerCandidates, true)) {
                            $foundHeader = true;
                            break;
                        }
                    }

                    if ($foundHeader) {
                        $headerMap = $this->makeHeaderMap($normalized);
                        $this->line('Detected header row, mapping: ' . json_encode($headerMap));
                        continue; // header row, skip to next
                    }

                    // If not header, assume default headers positions
                    $headerMap = $this->makeHeaderMap([]); // empty map => use heuristics per position later
                }

                // Normalize row to associative using header map
                $assoc = $this->rowToAssociative($row, $headerMap);

                // Try to get a code; skip if missing
                $code = $this->normalizeValue($assoc['code'] ?? $assoc['ref'] ?? $assoc['reference'] ?? null);
                $name = $this->normalizeValue($assoc['name'] ?? $assoc['produit'] ?? $assoc['product'] ?? null);

                if (!$code && !$name) {
                    $skipped++;
                    continue;
                }

                // Use code derived from name if missing
                if (!$code) {
                    $code = substr(preg_replace('/[^A-Za-z0-9\-]+/', '_', $name), 0, 20);
                }

                // Avoid processing duplicate rows in file
                if (isset($seenCodes[$code])) {
                    $skipped++;
                    continue;
                }
                $seenCodes[$code] = true;

                // Map fields strictly to product model attributes
                $data = [];
                $data['code'] = substr($code, 0, 20);
                $data['name'] = $name ?? $data['code'];
                $data['description'] = $this->normalizeValue($assoc['description'] ?? null);
                $data['grammage'] = $this->toInt($assoc['grammage'] ?? $assoc['gsm'] ?? null);
                $data['laize'] = $this->toInt($assoc['laize'] ?? $assoc['width'] ?? null);
                $data['flute'] = $this->normalizeValue($assoc['flute'] ?? null);
                $data['type_papier'] = $this->normalizeValue($assoc['type_papier'] ?? $assoc['paper_type'] ?? null);
                $data['extra_attributes'] = array_filter([
                    'raw_row' => $row,
                ]);

                $data['product_type'] = $this->normalizeValue($assoc['product_type'] ?? $assoc['type'] ?? $productTypeDefault);
                $data['form_type'] = $this->normalizeValue($assoc['form_type'] ?? $formTypeDefault);

                // Sheet dimensions
                $data['sheet_width_mm'] = $this->toFloat($assoc['sheet_width_mm'] ?? $assoc['sheet_width'] ?? null);
                $data['sheet_length_mm'] = $this->toFloat($assoc['sheet_length_mm'] ?? $assoc['sheet_length'] ?? null);

                // Unit handling - try to find by name or symbol, create if missing
                $unitName = $this->normalizeValue($assoc['unit'] ?? $assoc['unite'] ?? $assoc['unit_name'] ?? null);
                if ($unitName) {
                    $unit = Unit::firstOrCreate(['name' => $unitName], ['symbol' => $unitName]);
                    $data['unit_id'] = $unit->id;
                }

                $data['is_active'] = true;
                $data['min_stock'] = 0;
                $data['safety_stock'] = 0;

                // Upsert product by code
                $existing = Product::where('code', $data['code'])->first();
                if ($existing) {
                    $existing->fill($data);
                    $existing->save();
                    $updated++;
                } else {
                    Product::create($data);
                    $created++;
                }
            }
        }

        $reader->close();

        $this->info("Import finished. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");
        $this->info("Assumptions: default product_type={$productTypeDefault}, form_type={$formTypeDefault}. If results need tweaking, re-run with explicit options.");

        return 0;
    }

    protected function normalizeValue($v)
    {
        if ($v === null) return null;
        if (is_array($v)) return null;
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    protected function toInt($v)
    {
        $v = $this->normalizeValue($v);
        if ($v === null) return null;
        return (int) preg_replace('/[^0-9\-]/', '', $v);
    }

    protected function toFloat($v)
    {
        $v = $this->normalizeValue($v);
        if ($v === null) return null;
        $v = str_replace([',', ' '], ['.', ''], $v);
        return is_numeric($v) ? (float) $v : null;
    }

    protected function makeHeaderMap(array $headers): array
    {
        // normalize and map common column headers to expected keys
        $map = [];
        foreach ($headers as $i => $h) {
            if (!is_string($h) || $h === '') continue;
            $key = strtolower(trim($h));
            switch ($key) {
                case 'code':
                case 'ref':
                case 'reference':
                    $map[$i] = 'code';
                    break;
                case 'produit':
                case 'product':
                case 'name':
                    $map[$i] = 'name';
                    break;
                case 'description':
                    $map[$i] = 'description';
                    break;
                case 'grammage':
                case 'gsm':
                    $map[$i] = 'grammage';
                    break;
                case 'laize':
                case 'width':
                    $map[$i] = 'laize';
                    break;
                case 'flute':
                    $map[$i] = 'flute';
                    break;
                case 'type_papier':
                case 'paper_type':
                case 'type papier':
                    $map[$i] = 'type_papier';
                    break;
                case 'unit':
                case 'unite':
                case 'uom':
                    $map[$i] = 'unit';
                    break;
                case 'sheet_width_mm':
                case 'sheet width (mm)':
                case 'sheet_width':
                case 'sheet width':
                    $map[$i] = 'sheet_width_mm';
                    break;
                case 'sheet_length_mm':
                case 'sheet length (mm)':
                case 'sheet_length':
                case 'sheet length':
                    $map[$i] = 'sheet_length_mm';
                    break;
                default:
                    // ignore unknown header
                    break;
            }
        }

        return $map;
    }

    protected function rowToAssociative(array $row, array $map): array
    {
        $assoc = [];
        // if map empty, try positional heuristics: [code, name, unit, qty...]
        if (empty($map)) {
            // safe assignments by index
            if (isset($row[0])) $assoc['code'] = $row[0];
            if (isset($row[1])) $assoc['name'] = $row[1];
            if (isset($row[2])) $assoc['unit'] = $row[2];
            if (isset($row[3])) $assoc['grammage'] = $row[3];
        } else {
            foreach ($map as $index => $key) {
                $assoc[$key] = $row[$index] ?? null;
            }
        }
        return $assoc;
    }
}
