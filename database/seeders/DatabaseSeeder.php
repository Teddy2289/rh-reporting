<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            ClientSeeder::class,
            MissionSeeder::class,
            UserSeeder::class,
            AgentSeeder::class,
        ]);
    }
}
