<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds - Create test warehouses
     */
    public function run(): void
    {
        // Main Warehouse (Magasin Principal)
        Warehouse::firstOrCreate(
            ['name' => 'Magasin Principal - Siège'],
            ['is_system' => false]
        );

        // Secondary Warehouse (Magasin Secondaire)
        Warehouse::firstOrCreate(
            ['name' => 'Magasin Secondaire - Production'],
            ['is_system' => false]
        );

        // Transit/Quarantine Warehouse (Magasin Tampon)
        Warehouse::firstOrCreate(
            ['name' => 'Magasin Tampon - Conformité'],
            ['is_system' => false]
        );
    }
}
