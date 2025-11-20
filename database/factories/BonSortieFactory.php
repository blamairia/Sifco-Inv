<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BonSortie;
use App\Models\Warehouse;

class BonSortieFactory extends Factory
{
    protected $model = BonSortie::class;

    public function definition()
    {
        return [
            'bon_number' => 'BSRT-' . now()->format('Ymd') . '-' . $this->faker->unique()->randomNumber(4),
            'warehouse_id' => Warehouse::factory(),
            'issued_date' => now()->toDateString(),
            'destination' => $this->faker->sentence(3),
            'status' => 'draft',
        ];
    }
}
