<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'code' => 'CLT-FOS',
                'name' => 'FOSBER INDUSTRIES',
                'contact_person' => 'Service Achats',
                'email' => 'achats@fosber.example',
                'phone' => '+213 21 000 111',
                'city' => 'Alger',
                'country' => 'Algérie',
            ],
            [
                'code' => 'CLT-MAC',
                'name' => 'MACARBOX AFRICA',
                'contact_person' => 'Direction Production',
                'email' => 'production@macarbox.example',
                'phone' => '+213 31 222 333',
                'city' => 'Oran',
                'country' => 'Algérie',
            ],
        ];

        foreach ($clients as $client) {
            Client::updateOrCreate(
                ['code' => $client['code']],
                $client
            );
        }
    }
}
