<?php

namespace Database\Seeders\Control\Warehouses;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Fleet (vehicles / trucks).
 */
class FleetSeeder extends Seeder
{
    public function run(): void
    {
        Vehicle::firstOrCreate(
            ['name' => 'Truck 1'],
            [
                'type'            => 'truck',
                'license_plate'   => 'DHAKA-METRO-TA-1234',
                'driver'          => 'Abdul Karim',
                'capacity_crates' => 200,
            ]
        );

        Vehicle::firstOrCreate(
            ['name' => 'Truck 2'],
            [
                'type'            => 'truck',
                'license_plate'   => 'DHAKA-METRO-TA-5678',
                'driver'          => 'Rahim Uddin',
                'capacity_crates' => 180,
            ]
        );
    }
}

