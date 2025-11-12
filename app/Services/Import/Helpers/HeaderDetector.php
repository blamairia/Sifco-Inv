<?php

namespace App\Services\Import\Helpers;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HeaderDetector
{
    public static function normalizeHeader(string $header): string
    {
        // Normalize: uppercase, remove accents, underscores
        $header = trim($header);
        $header = mb_strtoupper($header);
        $header = preg_replace('/\s+/', '_', $header);
        $header = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $header) ?: $header;
        $header = preg_replace('/[^A-Z0-9_]/', '', $header);
        return $header;
    }

    public static function detectHeaderRow(Worksheet $sheet, array $canonicalHeaders, int $scanRows = 15): ?array
    {
        $highestRow = min($sheet->getHighestRow(), $scanRows);
        for ($row = 1; $row <= $highestRow; $row++) {
            $cells = $sheet->rangeToArray("A{$row}:ZZ{$row}", null, true, true, true)[0];
            $normalized = [];
            foreach ($cells as $col => $value) {
                if (! is_string($value) && ! is_numeric($value)) {
                    $normalized[$col] = null;
                    continue;
                }
                $normalized[$col] = self::normalizeHeader((string) $value);
            }

            $matches = 0;
            foreach ($normalized as $col => $n) {
                if (in_array($n, $canonicalHeaders, true)) {
                    $matches++;
                }
            }

            // Heuristic: if 4 or more canonical headers found on same line, it's a header row
            if ($matches >= 4) {
                return ['row' => $row, 'mapping' => $normalized];
            }
        }

        return null;
    }
}
