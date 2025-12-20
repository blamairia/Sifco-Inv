<?php

namespace Database\Seeders;

use App\Models\Product;
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
        
        // Paper Rolls (flagged as rolls)
        $p1 = Product::firstOrCreate(
            ['code' => 'PROD-KR80-001'],
            [
                'name' => 'Kraft Blanc 80g/m²', 'form_type' => Product::FORM_ROLL,
                'description' => 'Papier kraft blanchi', 'grammage' => 80, 'laize' => 1600,
                'type_papier' => 'Kraftliner', 'unit_id' => $unitRoll?->id,
                'min_stock' => 50, 'safety_stock' => 20,
                'product_type' => Product::TYPE_RAW_MATERIAL,
            ]
        );
        if ($p1->wasRecentlyCreated && $catKraft) {
            $p1->categories()->syncWithoutDetaching([$catKraft->id => ['is_primary' => true]]);
        }
        
        $p2 = Product::firstOrCreate(
            ['code' => 'PROD-TS120-002'],
            [
                'name' => 'Test 120g/m² Cannelure', 'form_type' => Product::FORM_ROLL,
                'grammage' => 120, 'laize' => 1400, 'flute' => 'B', 'type_papier' => 'Test/Fluting',
                'unit_id' => $unitRoll?->id, 'min_stock' => 40, 'safety_stock' => 15,
                'product_type' => Product::TYPE_RAW_MATERIAL,
            ]
        );
        if ($p2->wasRecentlyCreated && $catTest) {
            $p2->categories()->syncWithoutDetaching([$catTest->id => ['is_primary' => true]]);
        }
        
        $p3 = Product::firstOrCreate(
            ['code' => 'PROD-REC60-003'],
            [
                'name' => 'Recyclé 60g/m²', 'form_type' => Product::FORM_ROLL,
                'grammage' => 60, 'laize' => 1500, 'type_papier' => 'Recyclé',
                'unit_id' => $unitRoll?->id, 'min_stock' => 30, 'safety_stock' => 10,
                'product_type' => Product::TYPE_RAW_MATERIAL,
            ]
        );
        if ($p3->wasRecentlyCreated && $catRecycle) {
            $p3->categories()->syncWithoutDetaching([$catRecycle->id => ['is_primary' => true]]);
        }
        
        // Finished Products
        $p4 = Product::firstOrCreate(
            ['code' => 'PROD-C3E-004'],
            [
                'name' => 'Carton 3 Plis Microflûte E', 'form_type' => Product::FORM_SHEET,
                'flute' => 'E', 'extra_attributes' => json_encode(['thickness_mm' => 1.2]),
                'unit_id' => $unitPcs?->id, 'min_stock' => 1000, 'safety_stock' => 500,
                'product_type' => Product::TYPE_FINISHED_GOOD,
            ]
        );
        if ($p4->wasRecentlyCreated && $catFini) {
            $p4->categories()->syncWithoutDetaching([$catFini->id => ['is_primary' => true]]);
        }
        
        $p5 = Product::firstOrCreate(
            ['code' => 'PROD-C5BC-005'],
            [
                'name' => 'Carton 5 Plis BC', 'form_type' => Product::FORM_SHEET,
                'flute' => 'BC', 'extra_attributes' => json_encode(['thickness_mm' => 7.0]),
                'unit_id' => $unitPcs?->id, 'min_stock' => 500, 'safety_stock' => 200,
                'product_type' => Product::TYPE_FINISHED_GOOD,
            ]
        );
        if ($p5->wasRecentlyCreated && $catFini) {
            $p5->categories()->syncWithoutDetaching([$catFini->id => ['is_primary' => true]]);
        }
        
        // Consommables
        $p6 = Product::firstOrCreate(
            ['code' => 'CONS-FILM-006'],
            [
                'name' => 'Film Étirable 500mm', 'form_type' => Product::FORM_OTHER,
                'extra_attributes' => json_encode(['width_mm' => 500]), 'unit_id' => $unitPcs?->id,
                'min_stock' => 100, 'safety_stock' => 50,
                'product_type' => Product::TYPE_CONSUMABLE,
            ]
        );
        if ($p6->wasRecentlyCreated && $catConsommable) {
            $p6->categories()->syncWithoutDetaching([$catConsommable->id => ['is_primary' => true]]);
        }
        
        $p7 = Product::firstOrCreate(
            ['code' => 'CONS-ADHE-007'],
            [
                'name' => 'Adhésif Kraft 50mm', 'form_type' => Product::FORM_OTHER,
                'unit_id' => $unitPcs?->id, 'min_stock' => 200, 'safety_stock' => 100,
                'product_type' => Product::TYPE_CONSUMABLE,
            ]
        );
        if ($p7->wasRecentlyCreated && $catConsommable) {
            $p7->categories()->syncWithoutDetaching([$catConsommable->id => ['is_primary' => true]]);
        }
        
        $p8 = Product::firstOrCreate(
            ['code' => 'CONS-CALA-008'],
            [
                'name' => 'Calage Papier Ondulé', 'form_type' => Product::FORM_OTHER,
                'unit_id' => $unitKg?->id, 'min_stock' => 500, 'safety_stock' => 250,
                'product_type' => Product::TYPE_CONSUMABLE,
            ]
        );
        if ($p8->wasRecentlyCreated && $catConsommable) {
            $p8->categories()->syncWithoutDetaching([$catConsommable->id => ['is_primary' => true]]);
        }
        
        $p9 = Product::firstOrCreate(
            ['code' => 'EQP-MCH-009'],
            [
                'name' => 'Machine de Test 200', 'form_type' => Product::FORM_OTHER,
                'unit_id' => $unitPcs?->id, 'min_stock' => 1, 'safety_stock' => 0,
                'product_type' => Product::TYPE_EQUIPMENT,
            ]
        );
        if ($p9->wasRecentlyCreated && $catFini) {
            $p9->categories()->syncWithoutDetaching([$catFini->id => ['is_primary' => true]]);
        }
    }
}
