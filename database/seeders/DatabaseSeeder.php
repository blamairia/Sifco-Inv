<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\PaperRollType;
use App\Models\StockLevel;
use App\Models\Roll;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // === UNITS ===
        $kg = Unit::firstOrCreate(['name' => 'Kilogramme'], ['symbol' => 'kg']);
        $pcs = Unit::firstOrCreate(['name' => 'Pièce'], ['symbol' => 'pcs']);
        $roll = Unit::firstOrCreate(['name' => 'Rouleau'], ['symbol' => 'roll']);
        $litre = Unit::firstOrCreate(['name' => 'Litre'], ['symbol' => 'L']);

        // === CATEGORIES ===
        $papier = Category::firstOrCreate(['name' => 'Papiers']);
        $consommables = Category::firstOrCreate(['name' => 'Consommables']);
        $fini = Category::firstOrCreate(['name' => 'Produits Finis']);

        // === SUBCATEGORIES ===
        $papierKraft = Subcategory::firstOrCreate(
            ['category_id' => $papier->id, 'name' => 'Papier KRAFT']
        );
        $papierBlanc = Subcategory::firstOrCreate(
            ['category_id' => $papier->id, 'name' => 'Papier BLANC']
        );
        $adhes = Subcategory::firstOrCreate(
            ['category_id' => $consommables->id, 'name' => 'Adhésifs']
        );
        $encres = Subcategory::firstOrCreate(
            ['category_id' => $consommables->id, 'name' => 'Encres']
        );
        $carton = Subcategory::firstOrCreate(
            ['category_id' => $fini->id, 'name' => 'Cartons']
        );

        // === PAPER ROLL TYPES ===
        $kl120 = PaperRollType::firstOrCreate(
            ['type_code' => 'KL'],
            ['name' => 'KRAFT KL', 'grammage' => 120, 'laise' => 1200, 'weight' => 500.00]
        );
        $tlb80 = PaperRollType::firstOrCreate(
            ['type_code' => 'TLB'],
            ['name' => 'TELE BLANC TLB', 'grammage' => 80, 'laise' => 1000, 'weight' => 400.00]
        );
        $tlm100 = PaperRollType::firstOrCreate(
            ['type_code' => 'TLM'],
            ['name' => 'TELE MARGE TLM', 'grammage' => 100, 'laise' => 800, 'weight' => 380.00]
        );
        $fl60 = PaperRollType::firstOrCreate(
            ['type_code' => 'FL'],
            ['name' => 'FEUILLE FL', 'grammage' => 60, 'laise' => 600, 'weight' => 250.00]
        );

        // === WAREHOUSES ===
        Warehouse::firstOrCreate(
            ['name' => 'PRODUCTION_CONSUMED'],
            ['is_system' => true]
        );
        $wh_papier = Warehouse::firstOrCreate(
            ['name' => 'ENTREPOT_PAPIER'],
            ['is_system' => false]
        );
        $wh_consomm = Warehouse::firstOrCreate(
            ['name' => 'ENTREPOT_CONSOMMABLES'],
            ['is_system' => false]
        );
        $wh_fini = Warehouse::firstOrCreate(
            ['name' => 'ENTREPOT_PRODUITS_FINIS'],
            ['is_system' => false]
        );

        // === SUPPLIERS ===
        Supplier::firstOrCreate(
            ['name' => 'Fournisseur ABC', 'email' => 'contact@abc.dz'],
            ['contact_person' => 'Ahmed Ben Ali', 'phone' => '021-123-4567']
        );
        Supplier::firstOrCreate(
            ['name' => 'Fournisseur XYZ', 'email' => 'info@xyz.dz'],
            ['contact_person' => 'Fatima Djamila', 'phone' => '021-234-5678']
        );
        Supplier::firstOrCreate(
            ['name' => 'Papiers Import SARL', 'email' => 'sales@papiers-import.dz'],
            ['contact_person' => 'Mohammed Karim', 'phone' => '021-345-6789']
        );

        // === PRODUCTS ===
        $prod_kraft120 = Product::firstOrCreate(
            ['name' => 'Papier KRAFT 120 GSM'],
            [
                'type' => 'papier_roll',
                'category_id' => $papier->id,
                'subcategory_id' => $papierKraft->id,
                'unit_id' => $kg->id,
                'paper_roll_type_id' => $kl120->id,
                'gsm' => 120,
                'flute' => 'A',
                'width' => 1200,
                'min_stock' => 100,
                'safety_stock' => 150,
                'avg_cost' => 450.00,
            ]
        );

        $prod_kraft80 = Product::firstOrCreate(
            ['name' => 'Papier KRAFT 80 GSM'],
            [
                'type' => 'papier_roll',
                'category_id' => $papier->id,
                'subcategory_id' => $papierKraft->id,
                'unit_id' => $kg->id,
                'paper_roll_type_id' => $tlb80->id,
                'gsm' => 80,
                'flute' => 'B',
                'width' => 1000,
                'min_stock' => 200,
                'safety_stock' => 300,
                'avg_cost' => 350.00,
            ]
        );

        $prod_blanc100 = Product::firstOrCreate(
            ['name' => 'Papier BLANC 100 GSM'],
            [
                'type' => 'papier_roll',
                'category_id' => $papier->id,
                'subcategory_id' => $papierBlanc->id,
                'unit_id' => $kg->id,
                'paper_roll_type_id' => $tlm100->id,
                'gsm' => 100,
                'flute' => 'C',
                'width' => 800,
                'min_stock' => 150,
                'safety_stock' => 200,
                'avg_cost' => 520.00,
            ]
        );

        $prod_colle = Product::firstOrCreate(
            ['name' => 'Colle Adhésive'],
            [
                'type' => 'consommable',
                'category_id' => $consommables->id,
                'subcategory_id' => $adhes->id,
                'unit_id' => $litre->id,
                'min_stock' => 50,
                'safety_stock' => 75,
                'avg_cost' => 1200.00,
            ]
        );

        $prod_encre = Product::firstOrCreate(
            ['name' => 'Encre d\'Impression'],
            [
                'type' => 'consommable',
                'category_id' => $consommables->id,
                'subcategory_id' => $encres->id,
                'unit_id' => $litre->id,
                'min_stock' => 20,
                'safety_stock' => 30,
                'avg_cost' => 2500.00,
            ]
        );

        $prod_carton = Product::firstOrCreate(
            ['name' => 'Carton Ondulé'],
            [
                'type' => 'fini',
                'category_id' => $fini->id,
                'subcategory_id' => $carton->id,
                'unit_id' => $pcs->id,
                'gsm' => 350,
                'flute' => 'A',
                'width' => 1400,
                'min_stock' => 200,
                'safety_stock' => 300,
                'avg_cost' => 850.00,
            ]
        );

        $prod_boite = Product::firstOrCreate(
            ['name' => 'Boîte Pliante Standard'],
            [
                'type' => 'fini',
                'category_id' => $fini->id,
                'subcategory_id' => $carton->id,
                'unit_id' => $pcs->id,
                'min_stock' => 500,
                'safety_stock' => 750,
                'avg_cost' => 125.00,
            ]
        );

        // === STOCK LEVELS ===
        StockLevel::firstOrCreate(
            ['product_id' => $prod_kraft120->id, 'warehouse_id' => $wh_papier->id],
            ['qty' => 2500.00]
        );
        StockLevel::firstOrCreate(
            ['product_id' => $prod_kraft80->id, 'warehouse_id' => $wh_papier->id],
            ['qty' => 1800.00]
        );
        StockLevel::firstOrCreate(
            ['product_id' => $prod_blanc100->id, 'warehouse_id' => $wh_papier->id],
            ['qty' => 1200.00]
        );
        StockLevel::firstOrCreate(
            ['product_id' => $prod_colle->id, 'warehouse_id' => $wh_consomm->id],
            ['qty' => 150.00]
        );
        StockLevel::firstOrCreate(
            ['product_id' => $prod_encre->id, 'warehouse_id' => $wh_consomm->id],
            ['qty' => 85.00]
        );
        StockLevel::firstOrCreate(
            ['product_id' => $prod_carton->id, 'warehouse_id' => $wh_fini->id],
            ['qty' => 5000.00]
        );
        StockLevel::firstOrCreate(
            ['product_id' => $prod_boite->id, 'warehouse_id' => $wh_fini->id],
            ['qty' => 12000.00]
        );

        // === ROLLS (Sample rolls with EAN-13) ===
        Roll::firstOrCreate(
            ['ean_13' => '9791234567890'],
            [
                'product_id' => $prod_kraft120->id,
                'warehouse_id' => $wh_papier->id,
                'qty' => 500.00,
                'status' => 'in_stock',
            ]
        );
        Roll::firstOrCreate(
            ['ean_13' => '9791234567891'],
            [
                'product_id' => $prod_kraft120->id,
                'warehouse_id' => $wh_papier->id,
                'qty' => 500.00,
                'status' => 'in_stock',
            ]
        );
        Roll::firstOrCreate(
            ['ean_13' => '9791234567892'],
            [
                'product_id' => $prod_kraft80->id,
                'warehouse_id' => $wh_papier->id,
                'qty' => 400.00,
                'status' => 'in_stock',
            ]
        );
        Roll::firstOrCreate(
            ['ean_13' => '9791234567893'],
            [
                'product_id' => $prod_blanc100->id,
                'warehouse_id' => $wh_papier->id,
                'qty' => 300.00,
                'status' => 'in_stock',
            ]
        );

        // === USERS ===
        User::firstOrCreate(
            ['email' => 'admin@cartonstock.dz'],
            [
                'name' => 'Administrateur CartonStock',
                'password' => Hash::make('admin123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'test@cartonstock.dz'],
            [
                'name' => 'Utilisateur Test',
                'password' => Hash::make('test123'),
            ]
        );
    }
}
