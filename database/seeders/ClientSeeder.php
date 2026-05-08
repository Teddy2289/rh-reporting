<?php

namespace Database\Seeders;

use App\Models\Clients;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Hôpital Central',
                'code' => 'HOSP-01',
                'color' => '#E74C3C',
                'contact_email' => 'contact@hopital.fr',
                'contact_phone' => '0102030405',
                'is_active' => true,
            ],
            [
                'name' => 'Banque Nationale',
                'code' => 'BNK-SEC',
                'color' => '#2E86C1',
                'contact_email' => 'security@bnk.fr',
                'contact_phone' => '0504030201',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $client) {
            Clients::updateOrCreate(['code' => $client['code']], $client);
        }
    }
}
