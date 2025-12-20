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
        // Admin User
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@sifco.local')],
            [
                'name' => 'Administrateur Système',
                'email_verified_at' => now(),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
            ]
        );

        // Magasinier (Stock Manager)
        User::firstOrCreate(
            ['email' => 'magasinier@sifco.local'],
            [
                'name' => 'Magasinier Principal',
                'email_verified_at' => now(),
                'password' => Hash::make('magasinier123'),
            ]
        );

        // Secondary Stock Manager
        User::firstOrCreate(
            ['email' => 'assistant.magasin@sifco.local'],
            [
                'name' => 'Assistante Magasinage',
                'email_verified_at' => now(),
                'password' => Hash::make('assistant123'),
            ]
        );

        // Comptable (Finance)
        User::firstOrCreate(
            ['email' => 'comptable@sifco.local'],
            [
                'name' => 'Comptable Matières',
                'email_verified_at' => now(),
                'password' => Hash::make('comptable123'),
            ]
        );

        // Production Manager
        User::firstOrCreate(
            ['email' => 'production@sifco.local'],
            [
                'name' => 'Responsable Production',
                'email_verified_at' => now(),
                'password' => Hash::make('production123'),
            ]
        );
    }
}
