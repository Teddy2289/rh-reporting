<?php

namespace Database\Seeders;

use App\Models\Clients; // Ajuste le namespace si ton modèle est au singulier "Client"
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'AOPIA',
                'code' => 'AOPIA',
                'color' => '#2E86C1', // Bleu pro
                'contact_email' => 'contact@aopia.fr',
                'contact_phone' => '0102030405',
                'is_active' => true,
            ],
            [
                'name' => 'LIKES',
                'code' => 'LIKES',
                'color' => '#E74C3C', // Rouge dynamique
                'contact_email' => 'contact@likes.fr',
                'contact_phone' => '0102030406',
                'is_active' => true,
            ],
            [
                'name' => 'MBL',
                'code' => 'MBL',
                'color' => '#27AE60', // Vert
                'contact_email' => 'contact@mbl.fr',
                'contact_phone' => '0102030407',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $client) {
            Clients::updateOrCreate(['code' => $client['code']], $client);
        }
    }
}
