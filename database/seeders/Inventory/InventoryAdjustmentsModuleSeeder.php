<?php

namespace Database\Seeders\Inventory;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockAudit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Seed data for Inventory → Inventory adjustments.
 *
 * Creates a simple stock audit record to show how counted
 * quantities can differ from system quantities.
 */
class InventoryAdjustmentsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $product  = Product::where('sku', 'SAF-500ML-CTN')->first();
        $factory  = Warehouse::where('name', 'Factory')->first();
        $batch    = Batch::where('product_id', optional($product)->id)->first();

        if (! $product || ! $factory || ! $batch) {
            return;
        }

        StockAudit::firstOrCreate(
            [
                'warehouse_id' => $factory->id,
                'product_id'   => $product->id,
                'batch_id'     => $batch->id,
            ],
            [
                'system_quantity'  => 1500,
                'counted_quantity' => 1490,
                'variance'         => -10,
                'notes'            => 'Demo stock audit – 10 cartons short.',
            ]
        );
    }
}

