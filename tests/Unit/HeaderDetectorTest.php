<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Import\HeaderDetector;

class HeaderDetectorTest extends TestCase
{
    public function test_detect_header_simple()
    {
        $rows = [
            ['', 'STOCK INT', 'RECEPTION', 'UNITE', 'SORTIER', 'PRIX', 'VALEUR', 'STOCK'],
            ['DOUBLE FACE','178','158','ROULEAU','6','230.62','1383.72','172']
        ];
        $detector = new HeaderDetector();
        $info = $detector->detect($rows, 5);
        $this->assertNotNull($info);
        $this->assertEquals(0, $info['rowIndex']);
        $this->assertArrayHasKey('STOCK_INT', $info['mapping']);
        $this->assertArrayHasKey('RECEPTION', $info['mapping']);
        $this->assertArrayHasKey('UNITE', $info['mapping']);
    }
}
