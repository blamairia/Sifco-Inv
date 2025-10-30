<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $unitRoll = \App\Models\Unit::where('symbol', 'roll')->first();
        $unitPcs = \App\Models\Unit::where('symbol', 'pcs')->first();
        $unitKg = \App\Models\Unit::where('symbol', 'kg')->first();
        
        $catKraft = \App\Models\Category::where('name', 'Papiers Kraftliner')->first();
        $catTest = \App\Models\Category::where('name', 'Papiers Test/Fluting')->first();
        $catRecycle = \App\Models\Category::where('name', 'Papiers Recyclés')->first();
        $catConsommable = \App\Models\Category::where('name', 'Consommables Production')->first();
        $catFini = \App\Models\Category::where('name', 'Produits Finis')->first();
        
        // Paper Rolls
        $p1 = \App\Models\Product::create([
            'code' => 'PROD-KR80-001', 'name' => 'Kraft Blanc 80g/m²', 'type' => 'papier_roll',
            'description' => 'Papier kraft blanchi', 'grammage' => 80, 'laize' => 1600,
            'type_papier' => 'Kraftliner', 'unit_id' => $unitRoll?->id, 'min_stock' => 50, 'safety_stock' => 20,
        ]);
        $p1->categories()->attach($catKraft->id, ['is_primary' => true]);
        
        $p2 = \App\Models\Product::create([
            'code' => 'PROD-TS120-002', 'name' => 'Test 120g/m² Cannelure', 'type' => 'papier_roll',
            'grammage' => 120, 'laize' => 1400, 'flute' => 'B', 'type_papier' => 'Test/Fluting',
            'unit_id' => $unitRoll?->id, 'min_stock' => 40, 'safety_stock' => 15,
        ]);
        $p2->categories()->attach($catTest->id, ['is_primary' => true]);
        
        $p3 = \App\Models\Product::create([
            'code' => 'PROD-REC60-003', 'name' => 'Recyclé 60g/m²', 'type' => 'papier_roll',
            'grammage' => 60, 'laize' => 1500, 'type_papier' => 'Recyclé',
            'unit_id' => $unitRoll?->id, 'min_stock' => 30, 'safety_stock' => 10,
        ]);
        $p3->categories()->attach($catRecycle->id, ['is_primary' => true]);
        
        // Finished Products
        $p4 = \App\Models\Product::create([
            'code' => 'PROD-C3E-004', 'name' => 'Carton 3 Plis Microflûte E', 'type' => 'fini',
            'flute' => 'E', 'extra_attributes' => json_encode(['thickness_mm' => 1.2]),
            'unit_id' => $unitPcs?->id, 'min_stock' => 1000, 'safety_stock' => 500,
        ]);
        $p4->categories()->attach($catFini->id, ['is_primary' => true]);
        
        $p5 = \App\Models\Product::create([
            'code' => 'PROD-C5BC-005', 'name' => 'Carton 5 Plis BC', 'type' => 'fini',
            'flute' => 'BC', 'extra_attributes' => json_encode(['thickness_mm' => 7.0]),
            'unit_id' => $unitPcs?->id, 'min_stock' => 500, 'safety_stock' => 200,
        ]);
        $p5->categories()->attach($catFini->id, ['is_primary' => true]);
        
        // Consommables
        $p6 = \App\Models\Product::create([
            'code' => 'CONS-FILM-006', 'name' => 'Film Étirable 500mm', 'type' => 'consommable',
            'extra_attributes' => json_encode(['width_mm' => 500]), 'unit_id' => $unitPcs?->id,
            'min_stock' => 100, 'safety_stock' => 50,
        ]);
        $p6->categories()->attach($catConsommable->id, ['is_primary' => true]);
        
        $p7 = \App\Models\Product::create([
            'code' => 'CONS-ADHE-007', 'name' => 'Adhésif Kraft 50mm', 'type' => 'consommable',
            'unit_id' => $unitPcs?->id, 'min_stock' => 200, 'safety_stock' => 100,
        ]);
        $p7->categories()->attach($catConsommable->id, ['is_primary' => true]);
        
        $p8 = \App\Models\Product::create([
            'code' => 'CONS-CALA-008', 'name' => 'Calage Papier Ondulé', 'type' => 'consommable',
            'unit_id' => $unitKg?->id, 'min_stock' => 500, 'safety_stock' => 250,
        ]);
        $p8->categories()->attach($catConsommable->id, ['is_primary' => true]);
    }
}
