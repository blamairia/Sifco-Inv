<?php

namespace App\Services\Import\Helpers;

class SkuGenerator
{
    public static function generate(string $description, string $unit, int $seq): string
    {
        $tokens = preg_split('/\s+/', strtoupper(trim($description)));
        $tokens = array_filter($tokens, fn($t) => preg_match('/[A-Z0-9]/', $t));
        $tokens = array_slice($tokens, 0, 3);
        $base = implode('-', $tokens);
        $unitPart = preg_replace('/[^A-Z0-9]/', '', strtoupper($unit));
        $sku = sprintf('CONS-%s-%s-%04d', substr($base, 0, 30), $unitPart, $seq);
        // Ensure max length 64
        return substr($sku, 0, 64);
    }
}
