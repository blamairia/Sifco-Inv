<?php

namespace Database\Seeders;

use App\Models\ProductionLine;
use Illuminate\Database\Seeder;

class ProductionLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lines = [
            ['name' => 'FOSBER', 'code' => 'FOSBER', 'status' => 'active'],
            ['name' => 'MACARBOX', 'code' => 'MACARBOX', 'status' => 'active'],
            ['name' => 'ETERNA', 'code' => 'ETERNA', 'status' => 'active'],
            ['name' => 'CURIONI', 'code' => 'CURIONI', 'status' => 'active'],
        ];

        foreach ($lines as $line) {
            ProductionLine::updateOrCreate(
                ['code' => $line['code']],
                $line
            );
        }
    }
}
