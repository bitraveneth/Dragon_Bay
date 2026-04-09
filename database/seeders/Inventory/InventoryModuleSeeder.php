<?php

namespace Database\Seeders\Inventory;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Inventory (Core Operations) module.
 *
 * Delegates to:
 * - InventoryDashboardSeeder
 * - GoodsReceiptsSeeder
 * - TransfersModuleSeeder
 * - DeliveriesPodModuleSeeder
 * - VehicleLoadsModuleSeeder
 * - PackingSlipsModuleSeeder
 * - InventoryAdjustmentsModuleSeeder
 */
class InventoryModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InventoryDashboardSeeder::class,
            MaterialStockSeeder::class,
            GoodsReceiptsSeeder::class,
            TransfersModuleSeeder::class,
            DeliveriesPodModuleSeeder::class,
            VehicleLoadsModuleSeeder::class,
            PackingSlipsModuleSeeder::class,
            InventoryAdjustmentsModuleSeeder::class,
        ]);
    }
}
