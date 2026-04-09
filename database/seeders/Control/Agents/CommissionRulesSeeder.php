<?php

namespace Database\Seeders\Control\Agents;

use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Commission rules linked to agents or products.
 */
class CommissionRulesSeeder extends Seeder
{
    public function run(): void
    {
        $carton500 = Product::where('sku', 'SAF-500ML-CTN')->first();
        $bottle500 = Product::where('sku', 'SAF-500ML')->first();
        $carton1L  = Product::where('sku', 'SAF-1L-CTN')->first();
        $jar20L    = Product::where('sku', 'SAF-20L-JAR')->first();

        $dnDealer  = Agent::where('name', 'Dhaka North Dealer 01')->first();
        $dsDealer  = Agent::where('name', 'Dhaka South Dealer 01')->first();
        $ctgDealer = Agent::where('name', 'Chattogram Dealer 01')->first();
        $sylDealer = Agent::where('name', 'Sylhet Dealer 01')->first();
        $corpClient= Agent::where('name', 'Corporate Client 01')->first();

        if ($dnDealer && $carton500) {
            // Dhaka North dealer – 2% commission on all regular orders
            AgentCommissionRule::firstOrCreate(
                [
                    'agent_id' => $dnDealer->id,
                    'sku'      => $carton500->sku,
                    'type'     => 'percentage',
                    'frequency'=> 'per_order',
                ],
                [
                    'value'      => 2,
                    'order_type' => 'regular',
                ]
            );
        }

        if ($dsDealer && $carton500) {
            // Dhaka South dealer – flat 10 taka per carton for bulk orders (500ml carton)
            AgentCommissionRule::firstOrCreate(
                [
                    'agent_id' => $dsDealer->id,
                    'sku'      => $carton500->sku,
                    'type'     => 'fixed',
                    'frequency'=> 'per_order',
                ],
                [
                    'value'      => 10,
                    'order_type' => 'bulk',
                ]
            );
        }

        if ($ctgDealer && $carton1L) {
            // Chattogram dealer – 1.5% commission on 1L cartons (regular orders)
            AgentCommissionRule::firstOrCreate(
                [
                    'agent_id' => $ctgDealer->id,
                    'sku'      => $carton1L->sku,
                    'type'     => 'percentage',
                    'frequency'=> 'per_order',
                ],
                [
                    'value'      => 1.5,
                    'order_type' => 'regular',
                ]
            );
        }

        if ($sylDealer && $bottle500) {
            // Sylhet dealer – fixed 5 taka per 500ml bottle on bulk orders
            AgentCommissionRule::firstOrCreate(
                [
                    'agent_id' => $sylDealer->id,
                    'sku'      => $bottle500->sku,
                    'type'     => 'fixed',
                    'frequency'=> 'per_order',
                ],
                [
                    'value'      => 5,
                    'order_type' => 'bulk',
                ]
            );
        }

        if ($corpClient && $jar20L) {
            // Corporate client – monthly settlement, 1% commission on 20L jars (per_order for simplicity)
            AgentCommissionRule::firstOrCreate(
                [
                    'agent_id' => $corpClient->id,
                    'sku'      => $jar20L->sku,
                    'type'     => 'percentage',
                    'frequency'=> 'per_order',
                ],
                [
                    'value'      => 1,
                    'order_type' => 'regular',
                ]
            );
        }
    }
}
