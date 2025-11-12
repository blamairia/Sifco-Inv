<?php

namespace Tests\Unit\Import;

use PHPUnit\Framework\TestCase;
use App\Services\Import\SkuGenerator;

class SkuGeneratorTest extends TestCase
{
    public function test_generates_sku()
    {
        $g = new SkuGenerator();
        $sku = $g->generate('Colle Super PU', 'KG', 1);
        $this->assertStringStartsWith('CONS-', $sku);
    }
}
