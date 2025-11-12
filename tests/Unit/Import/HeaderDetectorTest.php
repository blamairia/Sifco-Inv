<?php

namespace Tests\Unit\Import;

use PHPUnit\Framework\TestCase;
use App\Services\Import\HeaderDetector;

class HeaderDetectorTest extends TestCase
{
    public function test_detects_header_row()
    {
        $detector = new HeaderDetector();
        $rows = [
            ['Random', 'Row'],
            ['DESCRIPTION', 'STOCK_INT', 'RECEPTION', 'UNITE', 'SORTIE', 'PRIX', 'VALEUR', 'STOCK'],
            ['Glue', 10, 0, 'KG', 2, 100, 200, 8],
        ];

        $info = $detector->detect($rows);
        $this->assertIsArray($info);
        $this->assertEquals(1, $info['rowIndex']);
        $this->assertArrayHasKey('DESCRIPTION', $info['mapping']);
    }
}
