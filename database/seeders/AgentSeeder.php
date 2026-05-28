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
        // Récupération des nouveaux départements mis à jour
        $deptIt = Department::query()->where('code', 'IT')->first();
        $deptCrm = Department::query()->where('code', 'CRM')->first();

        // Récupération des users créés dans le UserSeeder
        $userAdmin = User::query()->where('email', 'admin@rhplaning.fr')->first();
        $userManager = User::query()->where('email', 'manager@rhplaning.fr')->first();
        $userAgent = User::query()->where('email', 'agent@rhplaning.fr')->first();

        // Sécurité : On vérifie que le département CRM existe bien pour éviter un autre crash
        $crmId = $deptCrm ? $deptCrm->id : null;
        $itId = $deptIt ? $deptIt->id : null;

        // 1. Création de l'agent correspondant au Manager (lié au département Gestion CRM)
        $managerAgent = Agent::updateOrCreate(
            ['user_id' => $userManager->id],
            [
                'department_id'     => $crmId,
                'employee_code'     => 'AGT-001',
                'first_name'        => 'Ranto',
                'last_name'         => 'Agent',
                'phone'             => '0601020304',
                'contract_type'     => ContractType::CDI,
                'hire_date'         => '2023-01-01',
                'weekly_hours'      => 39,
                'annual_leave_days' => 25,
                'is_active'         => true,
            ]
        );

        // 2. Création de l'agent standard (lié au manager ci-dessus et au département Gestion CRM)
        Agent::updateOrCreate(
            ['user_id' => $userAgent->id],
            [
                'department_id'     => $crmId,
                'manager_id'        => $managerAgent->id, // L'agent report au manager
                'employee_code'     => 'AGT-002',
                'first_name'        => 'Teddy',
                'last_name'         => 'Agent',
                'phone'             => '0600000001',
                'contract_type'     => ContractType::CDI,
                'hire_date'         => '2024-02-15',
                'contract_end_date' => '2024-08-15',
                'weekly_hours'      => 35,
                'annual_leave_days' => 25,
                'is_active'         => true,
            ]
        );

        // 3. Création de l'agent pour l'admin (Lié au département IT)
        Agent::updateOrCreate(
            ['user_id' => $userAdmin->id],
            [
                'department_id'     => $itId,
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
