<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds - Create test users for manual testing
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Administrateur Système',
            'email' => 'admin@sifco.local',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
        ]);

        // Magasinier (Stock Manager)
        User::create([
            'name' => 'Magasinier Principal',
            'email' => 'magasinier@sifco.local',
            'email_verified_at' => now(),
            'password' => Hash::make('magasinier123'),
        ]);

        // Secondary Stock Manager
        User::create([
            'name' => 'Assistante Magasinage',
            'email' => 'assistant.magasin@sifco.local',
            'email_verified_at' => now(),
            'password' => Hash::make('assistant123'),
        ]);

        // Comptable (Finance)
        User::create([
            'name' => 'Comptable Matières',
            'email' => 'comptable@sifco.local',
            'email_verified_at' => now(),
            'password' => Hash::make('comptable123'),
        ]);

        // Production Manager
        User::create([
            'name' => 'Responsable Production',
            'email' => 'production@sifco.local',
            'email_verified_at' => now(),
            'password' => Hash::make('production123'),
        ]);
    }
}
