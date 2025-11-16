<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPrimaryCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_category_is_included_when_creating()
    {
    $cat = Category::create(['name' => 'Test Category']);

        $page = new CreateProduct();
        $data = [
            'name' => 'PC Test',
            'categories' => [],
            'primary_category_id' => $cat->id,
        ];

        // Call mutateFormDataBeforeCreate using reflection since it's protected on the page
        $ref = new \ReflectionMethod($page, 'mutateFormDataBeforeCreate');
        $ref->setAccessible(true);

        $out = $ref->invoke($page, $data);

        $this->assertArrayHasKey('categories', $out);
        $this->assertContains($cat->id, $out['categories']);
    }

    public function test_sync_primary_category_marks_pivot()
    {
    $cat = Category::create(['name' => 'Test Category 2']);
        $prod = Product::create(['name' => 'PC Test', 'form_type' => Product::FORM_ROLL, 'product_type' => Product::TYPE_RAW_MATERIAL]);

        // Attach the category
        $prod->categories()->attach($cat->id);

        // Prepare the page and set properties
        $page = new CreateProduct();
        $refPrimary = new \ReflectionProperty($page, 'primaryCategoryId');
        $refPrimary->setAccessible(true);
        $refPrimary->setValue($page, $cat->id);

        $refRecord = new \ReflectionProperty($page, 'record');
        $refRecord->setAccessible(true);
        $refRecord->setValue($page, $prod);

        $refSync = new \ReflectionMethod($page, 'syncPrimaryCategory');
        $refSync->setAccessible(true);
        $refSync->invoke($page);

        $this->assertDatabaseHas('product_category', [
            'product_id' => $prod->id,
            'category_id' => $cat->id,
            'is_primary' => 1,
        ]);
    }
}
