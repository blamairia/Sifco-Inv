<?php

namespace App\Services\Import\Helpers;

class UnitNormalizer
{
    public static array $map = [
        'KGS' => 'KG',
        'KG' => 'KG',
        'KILOGRAMME' => 'KG',
        'KILOGRAMMES' => 'KG',
        'L' => 'L',
        'LITRE' => 'L',
        'LITRES' => 'L',
        'ROULEAU' => 'ROULEAU',
        'ROULEAUX' => 'ROULEAU',
        'UNITE' => 'UNITE',
        'UNIT' => 'UNITE',
        'PIECE' => 'UNITE',
        'PCS' => 'UNITE',
    ];

    public static function normalize(?string $unit): string
    {
        if (empty($unit)) {
            return 'UNITE';
        }

        $u = strtoupper(trim($unit));
        $u = preg_replace('/[^A-Z0-9]/', '', $u);

        return self::$map[$u] ?? $u;
    }
}
