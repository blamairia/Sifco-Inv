<?php

namespace App\Services\Import\Helpers;

class ExternalIdGenerator
{
    public static function generate(string $sheet, int $row, string $date, string $desc, string $unit, string $movementType, $qty): string
    {
        $payload = sprintf('%s|%d|%s|%s|%s|%s|%s', $sheet, $row, $date, strtoupper(trim($desc)), strtoupper(trim($unit)), $movementType, (string) $qty);
        return sha1($payload);
    }
}
