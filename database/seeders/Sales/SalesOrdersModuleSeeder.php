<?php

namespace Database\Seeders\Sales;

use App\Models\Agent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Sales → Sales orders.
 *
 * Creates a couple of demo orders so that the Sales screens,
 * picking lists, deliveries and accounting flows have
 * something to work with after `migrate:fresh --seed`.
 */
class SalesOrdersModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Finished product SKU seeded in Control\Products\ProductsSeeder.
        $product = Product::where('sku', 'SAF-500ML-CTN')->first();

        if (! $product) {
            // Core demo SKU missing – skip seeding orders to avoid FK errors.
            return;
        }

        // Primary demo agents.
        $agentNorth = Agent::where('name', 'Dhaka North Dealer 01')->first();
        $agentSouth = Agent::where('name', 'Dhaka South Dealer 01')->first();

        if (! $agentNorth && ! $agentSouth) {
            // Agents not present yet – nothing to seed.
            return;
        }

        // Helper closure to create an order with a single line item on a
        // specific delivery date. We include "total" in the unique keys so
        // that re-seeding does not create duplicates.
        $createOrder = function (
            Agent $agent,
            string $orderType,
            int $quantity,
            string $paymentMode,
            Carbon $deliveryDate,
            string $noteSuffix = ''
        ) use ($product) {
            $unitPrice = $product->base_price ?? 550;
            $total     = $quantity * $unitPrice;

            // Simple tiered commission: 2% for regular, 4% for bulk
            $commissionRate = $orderType === 'bulk' ? 4 : 2;
            $commissionTotal = round($total * ($commissionRate / 100), 2);

            $order = Order::firstOrCreate(
                [
                    'agent_id'      => $agent->id,
                    'order_type'    => $orderType,
                    'delivery_date' => $deliveryDate->toDateString(),
                    'total'         => $total,
                ],
                [
                    'agent_reference'        => $agent->location_code ?? null,
                    'delivery_contact_name'  => $agent->name,
                    'delivery_contact_phone' => $agent->phone,
                    'delivery_address'       => trim(($agent->area ?? '') . ', ' . ($agent->zone ?? '')),
                    'status'                 => 'confirmed',
                    'commission_total'       => $commissionTotal,
                    'notes'                  => 'Seeded demo order' . ($noteSuffix ? " ({$noteSuffix})" : ''),
                    'is_credit_used'         => false,
                    'payment_mode'           => $paymentMode,
                ]
            );

            OrderItem::firstOrCreate(
                [
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity'          => $quantity,
                    'unit_price'        => $unitPrice,
                    'order_type'        => $orderType,
                    'commission_rate'   => $commissionRate,
                    'commission_amount' => $commissionTotal,
                ]
            );

            return $order;
        };

        $today = Carbon::today();

        if ($agentNorth) {
            // A mix of historical and recent orders so that analytics charts
            // have something useful to work with even without DemoAnalyticsSeeder.
            $createOrder($agentNorth, 'regular', 1200, 'cash', $today->copy()->addDays(2), 'North – upcoming');
            $createOrder($agentNorth, 'regular', 900, 'cash', $today->copy()->subDays(3), 'North – last week');
            $createOrder($agentNorth, 'bulk', 1500, 'bank_transfer', $today->copy()->startOfMonth()->addDays(4), 'North – this month');
            $createOrder($agentNorth, 'regular', 700, 'bank_transfer', $today->copy()->subMonth()->startOfMonth()->addDays(9), 'North – last month');
            $createOrder($agentNorth, 'regular', 650, 'cash', $today->copy()->subMonths(2)->startOfMonth()->addDays(9), 'North – two months ago');
        }

        if ($agentSouth) {
            $createOrder($agentSouth, 'bulk', 800, 'bank_transfer', $today->copy()->addDays(3), 'South – upcoming');
            $createOrder($agentSouth, 'regular', 600, 'cash', $today->copy()->subDays(5), 'South – last week');
            $createOrder($agentSouth, 'regular', 1100, 'cash', $today->copy()->startOfMonth()->addDays(6), 'South – this month');
            $createOrder($agentSouth, 'bulk', 900, 'bank_transfer', $today->copy()->subMonth()->startOfMonth()->addDays(12), 'South – last month');
            $createOrder($agentSouth, 'regular', 700, 'cash', $today->copy()->subMonths(2)->startOfMonth()->addDays(12), 'South – two months ago');
        }
    }
}
