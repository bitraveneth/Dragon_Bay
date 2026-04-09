<?php

namespace Database\Seeders\Sales;

use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seed data for Sales → Commission report.
 */
class CommissionReportModuleSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('sku', 'SAF-500ML-CTN')->first();
        $agent   = Agent::where('name', 'Dhaka North Dealer 01')->first();

        if (! $product || ! $agent) {
            return;
        }

        // Commission rule: 2% on all regular orders for this SKU (already seeded earlier).
        $rule = AgentCommissionRule::where('agent_id', $agent->id)
            ->where('type', 'percentage')
            ->where('value', 2)
            ->first();

        if (! $rule) {
            return;
        }

        // Take first regular order for this agent / product and calculate commission.
        $order = Order::where('agent_id', $agent->id)
            ->where('order_type', 'regular')
            ->with('items')
            ->first();

        if (! $order) {
            return;
        }

        /** @var OrderItem|null $item */
        $item = $order->items()->where('product_id', $product->id)->first();
        if (! $item) {
            return;
        }

        $lineTotal = $item->quantity * $item->unit_price;
        $commissionAmount = ($lineTotal * ($rule->value / 100));

        $item->update([
            'commission_rate'   => $rule->value,
            'commission_amount' => $commissionAmount,
        ]);

        $order->update([
            'commission_total' => $commissionAmount,
        ]);
    }
}
