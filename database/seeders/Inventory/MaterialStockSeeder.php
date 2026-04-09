<?php

namespace Database\Seeders\Inventory;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Seed opening stock for core raw materials in the Factory warehouse so that
 * BOM consumption during production has real material quantities to work with.
 */
class MaterialStockSeeder extends Seeder
{
    public function run(): void
    {
        $factory = Warehouse::where('name', 'Factory')->first();

        if (! $factory) {
            return;
        }

        // Map of material SKU => opening quantity in base UOM
        $materials = [
            'RM-PET-500'        => 60000, // PET Bottle 500ml
            'RM-CAP-STD'        => 60000, // Standard caps
            'RM-LABEL-500'      => 60000, // 500ml labels
            'RM-CARTON-12X500'  => 6000,  // cartons for 12x500ml
            'RM-SHRINK-CTN'     => 6000,  // shrink film wraps
            'RM-RO-WATER'       => 200000, // litres of treated water
        ];

        foreach ($materials as $sku => $qty) {
            $product = Product::where('sku', $sku)->where('product_type', 'raw')->first();

            if (! $product) {
                continue;
            }

            StockEntry::firstOrCreate(
                [
                    'warehouse_id'          => $factory->id,
                    'warehouse_location_id' => null,
                    'product_id'            => $product->id,
                    'batch_id'              => null,
                    'status'                => 'available',
                ],
                [
                    'quantity' => $qty,
                ]
            );
        }
    }
}
