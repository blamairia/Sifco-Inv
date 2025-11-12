<?php

namespace App\Services\Import;

class SkuGenerator
{
    public function generate(string $description, string $unit, int $sequence = 1): string
    {
        // Normalize description tokens; take up to 3 meaningful tokens
        $tokens = preg_split('/[^A-Z0-9]+/i', strtoupper($description));
        $tokens = array_filter($tokens, fn($t) => strlen($t) >= 2);
        $tokens = array_slice($tokens, 0, 3);
        $namePart = implode('-', $tokens ?: ['ITEM']);
        $unitPart = strtoupper(trim($unit ?: 'UNITE'));
        $sku = sprintf('CONS-%s-%s-%03d', $namePart, $unitPart, $sequence);
        return substr($sku, 0, 64); // DB length guard
    }
}
