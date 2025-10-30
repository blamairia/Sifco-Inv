<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Pièce', 'symbol' => 'pcs', 'description' => 'Unité individuelle'],
            ['name' => 'Bobine', 'symbol' => 'roll', 'description' => 'Bobine de papier'],
            ['name' => 'Kilogramme', 'symbol' => 'kg', 'description' => 'Poids en kilogrammes'],
            ['name' => 'Mètre', 'symbol' => 'm', 'description' => 'Longueur en mètres'],
            ['name' => 'Tonne', 'symbol' => 't', 'description' => 'Poids en tonnes'],
        ];

        foreach ($units as $unit) {
            \App\Models\Unit::create($unit);
        }
    }
}
