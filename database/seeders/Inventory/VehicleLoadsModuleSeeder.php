<?php

namespace Database\Seeders\Inventory;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Inventory → Vehicle loads.
 */
class VehicleLoadsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Reuse any existing deliveries from the Sales module to
        // ensure the Vehicle Loads screen has something to show.

        // If at least one scheduled delivery already exists we don't
        // create additional rows to avoid duplication.
        if (Delivery::whereNotNull('vehicle_id')->exists()) {
            return;
        }

        $vehicle = Vehicle::orderBy('id')->first();

        if (! $vehicle) {
            return;
        }

        // Use a few confirmed orders (if any) as demo deliveries.
        $orders = Order::whereIn('status', ['confirmed', 'delivered'])
            ->orderBy('id')
            ->take(3)
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $baseDate = Carbon::today();

        foreach ($orders as $index => $order) {
            Delivery::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'route_id'   => null,
                    'vehicle_id' => $vehicle->id,
                    'status'     => 'scheduled',
                    'pod_photo'  => null,
                    'exception_notes' => null,
                    'created_at' => $baseDate->copy()->setTime(8 + $index * 2, 0),
                    'updated_at' => $baseDate->copy()->setTime(8 + $index * 2, 0),
                ]
            );
        }
    }
}
