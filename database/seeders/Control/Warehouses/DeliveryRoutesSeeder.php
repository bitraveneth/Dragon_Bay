<?php

namespace Database\Seeders\Control\Warehouses;

use App\Models\DeliveryRoute;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Delivery routes.
 */
class DeliveryRoutesSeeder extends Seeder
{
    public function run(): void
    {
        $truck1 = Vehicle::where('name', 'Truck 1')->first();
        $truck2 = Vehicle::where('name', 'Truck 2')->first();

        if ($truck1) {
            DeliveryRoute::firstOrCreate(
                ['name' => 'Dhaka North Route 1'],
                [
                    'zone'      => 'Dhaka North',
                    'day'       => 'Sunday',
                    'vehicle_id'=> $truck1->id,
                    'driver'    => $truck1->driver,
                ]
            );
        }

        if ($truck2) {
            DeliveryRoute::firstOrCreate(
                ['name' => 'Dhaka North Route 2'],
                [
                    'zone'      => 'Dhaka North',
                    'day'       => 'Wednesday',
                    'vehicle_id'=> $truck2->id,
                    'driver'    => $truck2->driver,
                ]
            );
        }
    }
}

