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
                'name'        => 'IT',
                'code'        => 'IT',
                'color'       => '#2E86C1', // Bleu
                'description' => 'Développement, administration système et maintenance infrastructure.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Gestion CRM',
                'code'        => 'CRM',
                'color'       => '#8E44AD', // Violet
                'description' => 'Configuration, maintenance et suivi des instances CRM.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Gestion données client',
                'code'        => 'DATA',
                'color'       => '#27AE60', // Vert
                'description' => 'Analyse, traitement et sécurisation des bases de données clients.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Graphiste',
                'code'        => 'GRAPH',
                'color'       => '#F1C40F', // Jaune
                'description' => 'Création visuelle, identités de marque et design d\'interfaces.',
                'is_active'   => true,
            ],
            [
                'name'        => 'Ressources Humaines',
                'code'        => 'RH',
                'color'       => '#E74C3C', // Rouge
                'description' => 'Gestion administrative, paie, contrats et suivi des stagiaires/collaborateurs.',
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
