<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\User;
use App\Models\Department;
use App\Enums\ContractType;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération des données nécessaires
$deptRh = Department::query()->where('code', 'RH')->first();
        $deptExp = Department::query()->where('code', 'EXP')->first();

        // Récupération des users créés dans le UserSeeder
        $userAdmin = User::query()->where('email', 'admin@rhplaning.fr')->first();
        $userManager = User::query()->where('email', 'manager@rhplaning.fr')->first();
        $userAgent = User::query()->where('email', 'agent@rhplaning.fr')->first();

        // 1. Création de l'agent correspondant au Manager (pour avoir un manager_id valide plus tard)
        $managerAgent = Agent::updateOrCreate(
            ['user_id' => $userManager->id],
            [
                'department_id'     => $deptExp->id,
                'employee_code'     => 'MGR-001',
                'first_name'        => 'Marc',
                'last_name'         => 'Manager',
                'phone'             => '0601020304',
                'contract_type'     => ContractType::CDI,
                'hire_date'         => '2023-01-01',
                'weekly_hours'      => 39,
                'annual_leave_days' => 25,
                'is_active'         => true,
            ]
        );

        // 2. Création de l'agent standard (lié au manager ci-dessus)
        Agent::updateOrCreate(
            ['user_id' => $userAgent->id],
            [
                'department_id'     => $deptExp->id,
                'manager_id'        => $managerAgent->id, // L'agent report au manager
                'employee_code'     => 'AGT-001',
                'first_name'        => 'Jean',
                'last_name'         => 'Agent',
                'phone'             => '0600000001',
                'contract_type'     => ContractType::CDD,
                'hire_date'         => '2024-02-15',
                'contract_end_date' => '2024-08-15',
                'weekly_hours'      => 35,
                'annual_leave_days' => 25,
                'is_active'         => true,
            ]
        );

        // 3. Création de l'agent pour l'admin (Optionnel, si l'admin est aussi un employé)
        Agent::updateOrCreate(
            ['user_id' => $userAdmin->id],
            [
                'department_id'     => $deptRh->id,
                'employee_code'     => 'ADM-001',
                'first_name'        => 'Admin',
                'last_name'         => 'Système',
                'phone'             => '0100000000',
                'contract_type'     => ContractType::CDI,
                'hire_date'         => '2022-01-01',
                'weekly_hours'      => 35,
                'annual_leave_days' => 30,
                'is_active'         => true,
            ]
        );
    }
}
