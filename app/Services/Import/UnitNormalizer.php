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

    public function getMap(): array
    {
        return $this->map;
    }

    public function normalize(?string $unit): string
    {
        $u = strtoupper(trim((string)($unit ?? '')));
        $u = preg_replace('/\s+/', ' ', $u);

        if ($u === '') {
            return 'UNITE';
        }
        // If the unit cell contains only numbers, it's likely misaligned; default to 'UNITE'
        if (preg_match('/^\d+$/', $u)) {
            return 'UNITE';
        }
        // If the unit value is a month name, treat it as misaligned header and fallback
        $months = ['JAN', 'FEV', 'FÉV', 'MARS', 'AVR', 'MAI', 'JUIN', 'JUIL', 'AOUT', 'AOÛT', 'SEP', 'OCT', 'NOV', 'DEC'];
        foreach ($months as $m) {
            if (stripos($u, $m) !== false) {
                return 'UNITE';
            }
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
