<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Import\SkuGenerator;

class SkuGeneratorTest extends TestCase
{
    public function test_generate_sku_stable()
    {
        $g = new SkuGenerator();
        $a = $g->generate('Double Face', 'ROULEAU', 1);
        $b = $g->generate('Double Face', 'ROULEAU', 1);
        $this->assertEquals($a, $b);
        $this->assertStringStartsWith('CONS-', $a);
    }
}
