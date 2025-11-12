<?php

namespace Tests\Unit\Import;

use PHPUnit\Framework\TestCase;
use App\Services\Import\UnitNormalizer;

class UnitNormalizerTest extends TestCase
{
    public function test_normalizes_units()
    {
        $n = new UnitNormalizer();
        $this->assertEquals('KG', $n->normalize('kgs'));
        $this->assertEquals('L', $n->normalize('litres'));
        $this->assertEquals('UNITE', $n->normalize(''));
    }
}
