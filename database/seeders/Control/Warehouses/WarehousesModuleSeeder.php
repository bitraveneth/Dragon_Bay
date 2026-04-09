<?php

namespace Database\Seeders\Control\Warehouses;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Control → Warehouses area.
 *
 * Delegates to:
 * - WarehousesSeeder
 * - WarehouseLocationsSeeder
 * - DeliveryRoutesSeeder
 * - FleetSeeder
 */
class WarehousesModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WarehousesSeeder::class,
            WarehouseLocationsSeeder::class,
            FleetSeeder::class,
            // Routes depend on vehicles, so seed fleet first.
            DeliveryRoutesSeeder::class,
        ]);
    }
}
