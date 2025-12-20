<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Call all individual seeders in dependency order
        $this->call([
            UserSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            WarehouseSeeder::class,
            SupplierSeeder::class,
            ProductionLineSeeder::class,
            ClientSeeder::class,
            ProductSeeder::class,
            // Note: WorkflowDemoSeeder removed - uses sp_msforeachtable not available in Azure SQL
            // Use ComprehensiveDemoSeeder for demo data instead
        ]);
    }
}
