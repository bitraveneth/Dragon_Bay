<?php

namespace Database\Seeders\Control\Suppliers;

use Illuminate\Database\Seeder;

/**
 * Orchestrator for the Suppliers subsection under
 * Control (Masters & Settings).
 */
class SuppliersModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuppliersSeeder::class,
            PurchaseOrdersSeeder::class,
            PurchaseBillsSeeder::class,
        ]);
    }
}
