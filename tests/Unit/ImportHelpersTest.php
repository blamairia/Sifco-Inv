<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Import\HeaderDetector;
use App\Services\Import\UnitNormalizer;
use App\Services\Import\CategorySuggester;
use App\Services\Import\SkuGenerator;

class ImportHelpersTest extends TestCase
{
    public function test_unit_normalizer()
    {
        $n = new UnitNormalizer();
        $this->assertEquals('KG', $n->normalize('kgs'));
        $this->assertEquals('L', $n->normalize('Litres'));
        $this->assertEquals('UNITE', $n->normalize(''));
    }

    public function test_sku_generator()
    {
        $g = new SkuGenerator();
        $sku = $g->generate('Adhesive Tape 50mm', 'ROULEAU', 1);
        $this->assertStringContainsString('CONS-', $sku);
    }

    public function test_category_suggester()
    {
        $s = new CategorySuggester();
        $this->assertEquals('Rubans/Scellage', $s->suggest('Scotch tape 50mm'));
        $this->assertEquals('Autres', $s->suggest('Random Item'));
    }

    public function test_header_detector_detects_header()
    {
        $rows = [
            ['A'=> 'Some', 'B'=> 'Other'],
            ['A'=> 'DESCRIPTION', 'B'=> 'STOCK_INT', 'C' => 'RECEPTION', 'D' => 'UNITE'],
            ['A'=> 'Glue', 'B'=> '10', 'C'=> '5', 'D' => 'UNITE'],
        ];
        $h = new HeaderDetector();
        $det = $h->detect($rows);
        $this->assertNotNull($det);
        $this->assertEquals(1, $det['rowIndex']);
    }
}
