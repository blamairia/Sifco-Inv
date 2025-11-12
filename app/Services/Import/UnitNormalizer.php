<?php

namespace App\Services\Import;

class UnitNormalizer
{
    protected array $map = [
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
        'UN' => 'UNITE',
        'PIECE' => 'UNITE',
    ];

    public function normalize(?string $unit): string
    {
        $u = strtoupper(trim((string)($unit ?? '')));
        $u = preg_replace('/\s+/', ' ', $u);

        if ($u === '') {
            return 'UNITE';
        }
        $u = $this->stripS($u);

        return $this->map[$u] ?? $u;
    }

    protected function stripS(string $text): string
    {
        if (str_ends_with($text, 'S')) {
            return substr($text, 0, -1);
        }
        return $text;
    }
}
