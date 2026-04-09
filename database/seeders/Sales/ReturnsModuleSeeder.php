<?php

namespace Database\Seeders\Sales;

use App\Models\Agent;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

/**
 * Seed data for Sales → Returns.
 */
class ReturnsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Optional tiny demo: mark one order as a return-type order.

        $agent = Agent::where('name', 'Dhaka North Dealer 01')->first();

        if (! $agent) {
            return;
        }

        $existingReturn = Order::where('order_type', 'return')->first();
        if ($existingReturn) {
            return; // already have a demo return
        }

        $baseOrder = Order::where('agent_id', $agent->id)->orderBy('id')->first();
        if (! $baseOrder) {
            return;
        }

        $item = $baseOrder->items()->first();
        if (! $item) {
            return;
        }

        // Create a small return order for a subset of the quantity.
        $returnQty = min(50, $item->quantity);
        $unitPrice = $item->unit_price;
        $total     = $returnQty * $unitPrice * -1; // negative total for return

        $returnOrder = Order::create([
            'agent_id'      => $agent->id,
            'order_type'    => 'return',
            'delivery_date' => now()->addDays(3),
            'status'        => 'confirmed',
            'total'         => $total,
            'notes'         => 'Demo customer return seeded from ReturnsModuleSeeder',
            'is_credit_used'=> false,
            // For simplicity we treat the return as a credit adjustment
            // on the customer account.
            'payment_mode'  => 'credit',
        ]);

        OrderItem::create([
            'order_id'          => $returnOrder->id,
            'product_id'        => $item->product_id,
            'quantity'          => $returnQty * -1,
            'unit_price'        => $unitPrice,
            'order_type'        => 'return',
            'commission_rate'   => 0,
            'commission_amount' => 0,
        ]);
    }
}
