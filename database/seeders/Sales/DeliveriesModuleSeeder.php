<?php

namespace Database\Seeders\Sales;

use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

/**
 * Seed data for Sales → Deliveries.
 */
class DeliveriesModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Use the first confirmed order as the basis for a demo delivery.
        $order = Order::where('status', 'confirmed')->orderBy('id')->first();

        if (! $order) {
            return;
        }

        $route = DeliveryRoute::orderBy('id')->first();
        $vehicle = Vehicle::orderBy('id')->first();

        Delivery::firstOrCreate(
            ['order_id' => $order->id],
            [
                'route_id'   => $route?->id,
                'vehicle_id' => $vehicle?->id,
                'status'     => 'scheduled',
                'pod_photo'  => null,
                'exception_notes' => null,
            ]
        );
    }
}
