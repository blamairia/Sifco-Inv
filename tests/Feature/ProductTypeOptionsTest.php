<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class ProductTypeOptionsTest extends TestCase
{
    public function test_product_type_options_include_new_types()
    {
        $options = Product::productTypeOptions();

        $this->assertArrayHasKey(Product::TYPE_CONSUMABLE, $options);
        $this->assertEquals('Consommable', $options[Product::TYPE_CONSUMABLE]);

        $this->assertArrayHasKey(Product::TYPE_EQUIPMENT, $options);
        $this->assertEquals('Équipement', $options[Product::TYPE_EQUIPMENT]);

        $this->assertArrayHasKey(Product::TYPE_OTHER, $options);
        $this->assertEquals('Autre', $options[Product::TYPE_OTHER]);
    }

    public function test_product_types_list_includes_new_values()
    {
        $types = Product::productTypes();

        $this->assertContains(Product::TYPE_CONSUMABLE, $types);
        $this->assertContains(Product::TYPE_EQUIPMENT, $types);
        $this->assertContains(Product::TYPE_OTHER, $types);
    }
}
