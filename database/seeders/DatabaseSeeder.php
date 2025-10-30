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
            ProductSeeder::class,
        ]);
    }
}
