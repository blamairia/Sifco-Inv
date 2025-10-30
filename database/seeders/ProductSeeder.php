<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds - Create test carton products
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Carton Ondulé 3 Plis - 2.0 mm',
            'type' => 'fini',
            'flute' => 'E',
            'gsm' => 450,
            'min_stock' => 1000,
            'safety_stock' => 500,
            'avg_cost' => 2.50,
        ]);

        Product::create([
            'name' => 'Carton Ondulé 5 Plis - 3.5 mm',
            'type' => 'fini',
            'flute' => 'BC',
            'gsm' => 750,
            'min_stock' => 800,
            'safety_stock' => 400,
            'avg_cost' => 4.75,
        ]);

        Product::create([
            'name' => 'Papier Kraft Blanc - 80g/m²',
            'type' => 'papier_roll',
            'gsm' => 80,
            'width' => 1600,
            'min_stock' => 2000,
            'safety_stock' => 1000,
            'avg_cost' => 1.20,
        ]);

        Product::create([
            'name' => 'Papier Journal Recyclé - 50g/m²',
            'type' => 'papier_roll',
            'gsm' => 50,
            'width' => 1600,
            'min_stock' => 3000,
            'safety_stock' => 1500,
            'avg_cost' => 0.85,
        ]);

        Product::create([
            'name' => 'Carton Microflûte - 1.2 mm',
            'type' => 'fini',
            'flute' => 'F',
            'gsm' => 300,
            'min_stock' => 500,
            'safety_stock' => 250,
            'avg_cost' => 3.40,
        ]);

        Product::create([
            'name' => 'Boîte à Pâtes Pliante - Personnalisée',
            'type' => 'fini',
            'flute' => 'E',
            'min_stock' => 5000,
            'safety_stock' => 2500,
            'avg_cost' => 0.45,
        ]);

        Product::create([
            'name' => 'Calage Papier Ondulé - Vrac',
            'type' => 'consommable',
            'min_stock' => 100,
            'safety_stock' => 50,
            'avg_cost' => 0.12,
        ]);

        Product::create([
            'name' => 'Film Étirable Plastique - 500mm',
            'type' => 'consommable',
            'width' => 500,
            'min_stock' => 200,
            'safety_stock' => 100,
            'avg_cost' => 1.85,
        ]);

        Product::create([
            'name' => 'Adhésif Kraft Papier - Rouleau',
            'type' => 'consommable',
            'min_stock' => 500,
            'safety_stock' => 250,
            'avg_cost' => 2.30,
        ]);

        Product::create([
            'name' => 'Déchet Carton Mixte - Recyclage',
            'type' => 'consommable',
            'min_stock' => 1000,
            'safety_stock' => 500,
            'avg_cost' => 0.05,
        ]);
    }
}
