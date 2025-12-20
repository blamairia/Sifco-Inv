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
        Supplier::firstOrCreate(
            ['code' => 'SUPP-GPM-001'],
            [
                'name' => 'Groupe Papier Maroc',
                'contact_person' => 'Mohammed Zahra',
                'email' => 'contact@groupe-papier.ma',
                'phone' => '+212523456789',
                'address' => 'Zone Industrielle, Casablanca',
                'payment_terms' => 'Net 30',
            ]
        );

        Supplier::firstOrCreate(
            ['code' => 'SUPP-SCE-002'],
            [
                'name' => 'Société Cartonnerie Europe',
                'contact_person' => 'Jean-Pierre Dupont',
                'email' => 'sales@carton-europe.fr',
                'phone' => '+33123456789',
                'address' => 'Lyon, France',
                'payment_terms' => 'Net 45',
            ]
        );

        Supplier::firstOrCreate(
            ['code' => 'SUPP-PCI-003'],
            [
                'name' => 'Papier et Carton International',
                'contact_person' => 'Carlos González',
                'email' => 'info@pci-spain.es',
                'phone' => '+34912345678',
                'address' => 'Madrid, Spain',
                'payment_terms' => 'Net 30',
            ]
        );

        Supplier::firstOrCreate(
            ['code' => 'SUPP-DPA-004'],
            [
                'name' => 'Deutsche Papier AG',
                'contact_person' => 'Klaus Schmidt',
                'email' => 'k.schmidt@deutsche-papier.de',
                'phone' => '+493012345678',
                'address' => 'Berlin, Germany',
                'payment_terms' => 'Net 60',
            ]
        );

        Supplier::firstOrCreate(
            ['code' => 'SUPP-FIL-005'],
            [
                'name' => 'Fournitures Industrielles Locales',
                'contact_person' => 'Fatima Alaoui',
                'email' => 'fatima.alaoui@fil.ma',
                'phone' => '+212661234567',
                'address' => 'Rabat, Morocco',
                'payment_terms' => 'Net 15',
            ]
        );
    }
}
