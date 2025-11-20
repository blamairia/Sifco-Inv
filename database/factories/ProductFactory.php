<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('PROD-[A-Z0-9]{6}'),
            'name' => $this->faker->word(),
            'product_type' => Product::TYPE_FINISHED_GOOD,
            'form_type' => Product::FORM_OTHER,
            'is_active' => true,
        ];
    }
}
