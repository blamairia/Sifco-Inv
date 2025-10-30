<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Papiers Kraftliner', 'description' => 'Papiers kraft pour couvertures'],
            ['name' => 'Papiers Test/Fluting', 'description' => 'Papiers pour cannelures'],
            ['name' => 'Papiers Recyclés', 'description' => 'Papiers à base recyclée'],
            ['name' => 'Consommables Production', 'description' => 'Fournitures production'],
            ['name' => 'Produits Finis', 'description' => 'Cartons ondulés finis'],
            ['name' => 'Accessoires Emballage', 'description' => 'Films, adhésifs, etc.'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
