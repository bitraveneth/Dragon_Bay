<?php

namespace Database\Seeders\Inventory;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed core stock entries used by the Inventory dashboard.
 *
 * We assume:
 * - Finished product SAF-500ML-CTN exists (from ProductsSeeder)
 * - A demo batch exists (from BatchesLotsModuleSeeder)
 * - Warehouses Factory & Central Depot exist (from WarehousesSeeder)
 */
class InventoryDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $product      = Product::where('sku', 'SAF-500ML-CTN')->first();
        $factory      = Warehouse::where('name', 'Factory')->first();
        $centralDepot = Warehouse::where('name', 'Central Depot')->first();

        if (! $product || ! $factory || ! $centralDepot) {
            return;
        }

        // Use existing batch if available, otherwise create a simple one.
        $batch = Batch::where('product_id', $product->id)->first();

        if (! $batch) {
            $today = Carbon::today();
            $batch = Batch::create([
                'product_id'      => $product->id,
                'batch_code'      => '500ML-CTN-' . $today->format('ymd') . '-B',
                'production_date' => $today,
                'expiry_date'     => $today->copy()->addMonths(6),
                'qc_status'       => 'approved',
                'notes'           => 'Inventory seeder demo batch',
            ]);
        }

        // Factory stock – available cartons
        StockEntry::firstOrCreate(
            [
                'warehouse_id' => $factory->id,
                'product_id'   => $product->id,
                'batch_id'     => $batch->id,
                'status'       => 'available',
            ],
            [
                'warehouse_location_id' => null,
                'quantity'              => 1500, // 1,500 cartons at factory
            ]
        );

        // Central Depot stock – available cartons
        StockEntry::firstOrCreate(
            [
                'warehouse_id' => $centralDepot->id,
                'product_id'   => $product->id,
                'batch_id'     => $batch->id,
                'status'       => 'available',
            ],
            [
                'warehouse_location_id' => null,
                'quantity'              => 200, // 200 cartons at depot
            ]
        );
    }
}

