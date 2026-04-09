<?php

namespace Database\Seeders\Sales;

use App\Models\Agent;
use App\Models\AgentCommissionSettlement;
use App\Models\Order;
use Illuminate\Database\Seeder;

/**
 * Seed data for Sales → Commission settlements.
 */
class CommissionSettlementsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $agent = Agent::where('name', 'Dhaka North Dealer 01')->first();

        if (! $agent) {
            return;
        }

        $from = now()->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->toDateString();

        // Aggregate sales and commission for the period from existing orders.
        $orders = Order::where('agent_id', $agent->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $salesTotal = $orders->sum('total');
        $commissionTotal = $orders->sum('commission_total');

        AgentCommissionSettlement::updateOrCreate(
            [
                'agent_id'    => $agent->id,
                'period_start'=> $from,
                'period_end'  => $to,
            ],
            [
                'sales_total'      => $salesTotal,
                'commission_total' => $commissionTotal,
                'status'           => 'open',
            ]
        );
    }
}
