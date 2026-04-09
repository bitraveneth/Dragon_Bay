<?php

namespace Database\Seeders\Control\Warehouses;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Warehouse locations (racks / bins / shelves).
 */
class WarehouseLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $factory      = Warehouse::where('name', 'Factory')->first();
        $centralDepot = Warehouse::where('name', 'Central Depot')->first();

        if ($factory) {
            WarehouseLocation::firstOrCreate(
                ['warehouse_id' => $factory->id, 'code' => 'F1-R1-S1'],
                ['description' => 'Factory rack 1, shelf 1']
            );

            WarehouseLocation::firstOrCreate(
                ['warehouse_id' => $factory->id, 'code' => 'F1-R2-S1'],
                ['description' => 'Factory rack 2, shelf 1']
            );
        }

        if ($centralDepot) {
            WarehouseLocation::firstOrCreate(
                ['warehouse_id' => $centralDepot->id, 'code' => 'CD-R1-S1'],
                ['description' => 'Central depot rack 1, shelf 1']
            );

            WarehouseLocation::firstOrCreate(
                ['warehouse_id' => $centralDepot->id, 'code' => 'CD-R1-S2'],
                ['description' => 'Central depot rack 1, shelf 2']
            );
        }
    }
}

