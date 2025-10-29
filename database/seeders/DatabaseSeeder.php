<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the system warehouse for production consumption
        Warehouse::firstOrCreate(
            ['name' => 'PRODUCTION_CONSUMED'],
            ['is_system' => true]
        );

        // Create sample warehouses
        Warehouse::firstOrCreate(
            ['name' => 'ENTREPOT_PAPIER'],
            ['is_system' => false]
        );
        Warehouse::firstOrCreate(
            ['name' => 'ENTREPOT_CONSOMMABLES'],
            ['is_system' => false]
        );
        Warehouse::firstOrCreate(
            ['name' => 'ENTREPOT_PRODUITS_FINIS'],
            ['is_system' => false]
        );

        // Create sample suppliers
        Supplier::firstOrCreate(
            ['name' => 'Fournisseur ABC', 'email' => 'contact@abc.dz'],
            [
                'contact_person' => 'Ahmed Ben Ali',
                'phone' => '021-123-4567',
            ]
        );
        Supplier::firstOrCreate(
            ['name' => 'Fournisseur XYZ', 'email' => 'info@xyz.dz'],
            [
                'contact_person' => 'Fatima Djamila',
                'phone' => '021-234-5678',
            ]
        );
        Supplier::firstOrCreate(
            ['name' => 'Papiers Import SARL', 'email' => 'sales@papiers-import.dz'],
            [
                'contact_person' => 'Mohammed Karim',
                'phone' => '021-345-6789',
            ]
        );

        // Create sample products
        Product::firstOrCreate(
            ['name' => 'Papier KRAFT 120 GSM'],
            [
                'type' => 'papier_roll',
                'gsm' => 120,
                'flute' => 'A',
                'width' => 1200,
                'min_stock' => 100,
                'safety_stock' => 150,
                'avg_cost' => 450.00,
            ]
        );
        Product::firstOrCreate(
            ['name' => 'Papier KRAFT 80 GSM'],
            [
                'type' => 'papier_roll',
                'gsm' => 80,
                'flute' => 'B',
                'width' => 1000,
                'min_stock' => 200,
                'safety_stock' => 300,
                'avg_cost' => 350.00,
            ]
        );
        Product::firstOrCreate(
            ['name' => 'Papier BLANC 100 GSM'],
            [
                'type' => 'papier_roll',
                'gsm' => 100,
                'flute' => 'C',
                'width' => 800,
                'min_stock' => 150,
                'safety_stock' => 200,
                'avg_cost' => 520.00,
            ]
        );
        Product::firstOrCreate(
            ['name' => 'Colle Adhésive'],
            [
                'type' => 'consommable',
                'gsm' => null,
                'flute' => null,
                'width' => null,
                'min_stock' => 50,
                'safety_stock' => 75,
                'avg_cost' => 1200.00,
            ]
        );
        Product::firstOrCreate(
            ['name' => 'Encre d\'Impression'],
            [
                'type' => 'consommable',
                'gsm' => null,
                'flute' => null,
                'width' => null,
                'min_stock' => 20,
                'safety_stock' => 30,
                'avg_cost' => 2500.00,
            ]
        );
        Product::firstOrCreate(
            ['name' => 'Carton Ondulé'],
            [
                'type' => 'fini',
                'gsm' => 350,
                'flute' => 'A',
                'width' => 1400,
                'min_stock' => 200,
                'safety_stock' => 300,
                'avg_cost' => 850.00,
            ]
        );
        Product::firstOrCreate(
            ['name' => 'Boîte Pliante Standard'],
            [
                'type' => 'fini',
                'gsm' => null,
                'flute' => null,
                'width' => null,
                'min_stock' => 500,
                'safety_stock' => 750,
                'avg_cost' => 125.00,
            ]
        );

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@cartonstock.dz'],
            [
                'name' => 'Administrateur CartonStock',
                'password' => Hash::make('admin123'),
            ]
        );

        // Create test user
        User::firstOrCreate(
            ['email' => 'test@cartonstock.dz'],
            [
                'name' => 'Utilisateur Test',
                'password' => Hash::make('test123'),
            ]
        );
    }
}
