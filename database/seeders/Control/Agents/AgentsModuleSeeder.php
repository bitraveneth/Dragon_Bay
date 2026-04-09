<?php

namespace Database\Seeders\Control\Agents;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Control → Agents area.
 *
 * Delegates to:
 * - AgentsSeeder
 * - CommissionRulesSeeder
 */
class AgentsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AgentsSeeder::class,
            CommissionRulesSeeder::class,
        ]);
    }
}
