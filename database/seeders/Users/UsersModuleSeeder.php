<?php

namespace Database\Seeders\Users;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for creating application users (login accounts).
 *
 * Delegates to:
 * - AdminUsersSeeder
 * - EmployeeUsersSeeder
 * - (later) AgentUsersSeeder
 */
class UsersModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUsersSeeder::class,
            EmployeeUsersSeeder::class,
            // AgentUsersSeeder::class, // enable when agents are seeded
        ]);
    }
}
