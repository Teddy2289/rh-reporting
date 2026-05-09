<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Permissions ────────────────────────────────────────────────────
        $permissions = [
            // Agents
            'view agents',
            'create agents',
            'edit agents',
            'delete agents',

            // Planning
            'view planning',
            'create planning',
            'edit planning',
            'delete planning',
            'generate planning',

            // Congés
            'view leaves',
            'create leaves',
            'edit leaves',
            'delete leaves',
            'approve leaves',

            // Rapports
            'view reports',
            'export reports',

            // Clients / Missions
            'manage clients',
            'manage missions',
            'manage departments',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']); // ← api
        }

        $admin   = Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'api']);
        $rh      = Role::firstOrCreate(['name' => 'rh',      'guard_name' => 'api']);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'api']);
        $agent   = Role::firstOrCreate(['name' => 'agent',   'guard_name' => 'api']);

        // Admin : tout
        $admin->syncPermissions(Permission::all());

        // RH : tout sauf manage departments
        $rh->syncPermissions([
            'view agents',
            'create agents',
            'edit agents',
            'view planning',
            'create planning',
            'edit planning',
            'delete planning',
            'generate planning',
            'view leaves',
            'create leaves',
            'edit leaves',
            'delete leaves',
            'approve leaves',
            'view reports',
            'export reports',
            'manage clients',
            'manage missions',
        ]);

        // Manager : son équipe
        $manager->syncPermissions([
            'view agents',
            'view planning',
            'create planning',
            'edit planning',
            'view leaves',
            'approve leaves',
            'view reports',
        ]);

        // Agent : lecture + demandes
        $agent->syncPermissions([
            'view planning',
            'view leaves',
            'create leaves',
        ]);

        $this->command->info('Rôles et permissions créés avec succès.');
    }
}
