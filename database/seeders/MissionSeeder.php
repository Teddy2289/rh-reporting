<?php

namespace Database\Seeders;

use App\Models\Mission;
use App\Models\Clients;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        $hopital = Clients::query()->where('code', 'HOSP-01')->first();
        $banque = Clients::query()->where('code', 'BNK-SEC')->first();

        if ($hopital) {
            Mission::create([
                'client_id' => $hopital->id,
                'name' => 'Surveillance Nuit',
                'code' => 'NIGHT-WATCH',
                'description' => 'Patrouille nocturne dans les couloirs',
                'is_active' => true,
            ]);
        }

        if ($banque) {
            Mission::create([
                'client_id' => $banque->id,
                'name' => 'Contrôle Accès',
                'code' => 'ACCESS-CTRL',
                'description' => 'Vérification des badges à l\'entrée',
                'is_active' => true,
            ]);
        }
    }
}
