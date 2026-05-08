<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création des rôles
        $roles = ['admin', 'rh', 'manager', 'agent'];
        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName);
        }

        // 2. Création de l'Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@rhplaning.fr'],
            [
                'name' => 'Admin Système',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // 3. Création d'un Manager
        $manager = User::updateOrCreate(
            ['email' => 'manager@rhplaning.fr'],
            [
                'name' => 'Marc Manager',
                'password' => Hash::make('password'),
            ]
        );
        $manager->assignRole('manager');

        // 4. Création d'un compte Agent (test)
        $agentUser = User::updateOrCreate(
            ['email' => 'agent@rhplaning.fr'],
            [
                'name' => 'Jean Agent',
                'password' => Hash::make('password'),
            ]
        );
        $agentUser->assignRole('agent');
    }
}
