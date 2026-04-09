<?php

namespace Database\Seeders\Control\Employees;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Control → Employees area.
 *
 * Delegates to:
 * - EmployeesSeeder
 * - ContractsSeeder
 * - AllowancesSeeder
 * - EquipmentSeeder
 * - LeavesSeeder
 * - BadgesSeeder
 */
class EmployeesModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EmployeesSeeder::class,
            ContractsSeeder::class,
            AllowancesSeeder::class,
            EquipmentSeeder::class,
            LeavesSeeder::class,
            BadgesSeeder::class,
        ]);
    }
}
