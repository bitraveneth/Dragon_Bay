<?php

namespace Database\Seeders\Control\Products;

use App\Models\Agent;
use App\Models\AgentPriceList;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Price lists / catalog pricing.
 *
 * For now we create:
 * - Base product price already lives on products.base_price
 * - One agent specific price list entry if a demo agent exists.
 */
class PriceListsSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('sku', 'SAF-500ML-CTN')->first();

        if (! $product) {
            return;
        }

        // Optional: if an agent called "Dhaka North Dealer 01" exists,
        // give them a slightly discounted price.
        $agent = Agent::where('name', 'Dhaka North Dealer 01')->first();

        if ($agent) {
            AgentPriceList::firstOrCreate(
                [
                    'agent_id'   => $agent->id,
                    'product_id' => $product->id,
                ],
                [
                    'price' => 530, // discounted price for demo agent
                    'notes' => 'Demo price for Dhaka North Dealer 01',
                ]
            );
        }
    }
}

