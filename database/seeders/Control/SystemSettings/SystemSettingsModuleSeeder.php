<?php

namespace Database\Seeders\Control\SystemSettings;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Control → System settings area.
 *
 * Delegates to:
 * - HelpConfigurationSeeder
 * (later you can add more system settings seeders here)
 */
class SystemSettingsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HelpConfigurationSeeder::class,
        ]);
    }
}
