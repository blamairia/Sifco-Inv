<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds - Create test suppliers
     */
    public function run(): void
    {
        Supplier::create([
            'name' => 'Groupe Papier Maroc',
            'contact_person' => 'Mohammed Zahra',
            'email' => 'contact@groupe-papier.ma',
            'phone' => '+212523456789',
        ]);

        Supplier::create([
            'name' => 'Société Cartonnerie Europe',
            'contact_person' => 'Jean-Pierre Dupont',
            'email' => 'sales@carton-europe.fr',
            'phone' => '+33123456789',
        ]);

        Supplier::create([
            'name' => 'Papier et Carton International',
            'contact_person' => 'Carlos González',
            'email' => 'info@pci-spain.es',
            'phone' => '+34912345678',
        ]);

        Supplier::create([
            'name' => 'Deutsche Papier AG',
            'contact_person' => 'Klaus Schmidt',
            'email' => 'k.schmidt@deutsche-papier.de',
            'phone' => '+493012345678',
        ]);

        Supplier::create([
            'name' => 'Fournitures Industrielles Locales',
            'contact_person' => 'Fatima Alaoui',
            'email' => 'fatima.alaoui@fil.ma',
            'phone' => '+212661234567',
        ]);
    }
}
