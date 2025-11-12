<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Import\UnitNormalizer;

class UnitNormalizerTest extends TestCase
{
    public function test_normalize_known_units()
    {
        $u = new UnitNormalizer();
        $this->assertEquals('KG', $u->normalize('kgs'));
        $this->assertEquals('KG', $u->normalize('kilogrammes'));
        $this->assertEquals('L', $u->normalize('litres'));
        $this->assertEquals('UNITE', $u->normalize('')); // empty default
        $this->assertEquals('UNITE', $u->normalize('1470')); // numeric fallbacks
    }
}
