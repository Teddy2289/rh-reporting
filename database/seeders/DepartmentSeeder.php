<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name'        => 'Ressources Humaines',
                'code'        => 'RH',
                'color'       => '#E74C3C', // Rouge
                'description' => 'Gestion administrative, paie et contrats.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Exploitation / Sécurité',
                'code'        => 'EXP',
                'color'       => '#2E86C1', // Bleu
                'description' => 'Gestion des agents sur le terrain et des plannings.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Direction Générale',
                'code'        => 'DIR',
                'color'       => '#212F3D', // Noir/Gris foncé
                'description' => 'Pilotage stratégique et décisions majeures.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Logistique',
                'code'        => 'LOG',
                'color'       => '#27AE60', // Vert
                'description' => 'Gestion du matériel, des véhicules et des stocks.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Commercial',
                'code'        => 'COM',
                'color'       => '#F1C40F', // Jaune
                'description' => 'Relation client et développement des missions.',
                'is_active'   => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']], // Empêche les doublons sur le code
                $dept
            );
        }
    }
}
