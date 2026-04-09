<?php

namespace Database\Seeders\Inventory;

use Illuminate\Database\Seeder;

/**
 * Seed data for Inventory → Deliveries & POD.
 *
 * For now this seeder does not create additional rows because
 * deliveries are better seeded from the Sales module. This
 * class exists so that the module structure is complete.
 */
class DeliveriesPodModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally left empty; deliveries will be seeded
        // from the Sales module when needed.
    }
}

