<?php

namespace Database\Seeders\Manufacturing;

use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seed data for Manufacturing → BOMs (Bill of Materials).
 *
 * For now we create one standard BOM for:
 * - SAF-500ML-CTN (500ml carton, 12 bottles)
 */
class BomsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Shared components used across multiple BOMs
        $petBottle  = Product::where('sku', 'RM-PET-500')->first();
        $cap        = Product::where('sku', 'RM-CAP-STD')->first();
        $label      = Product::where('sku', 'RM-LABEL-500')->first();
        $carton500  = Product::where('sku', 'RM-CARTON-12X500')->first();
        $shrinkWrap = Product::where('sku', 'RM-SHRINK-CTN')->first();
        $water      = Product::where('sku', 'RM-RO-WATER')->first();
        $labour     = Product::where('sku', 'SV-LAB-FACT')->first();
        $utilities  = Product::where('sku', 'SV-UTIL-SHIFT')->first();

        /**
         * We seed three simple BOMs:
         * - SAF-500ML-CTN (carton of 12 bottles)
         * - SAF-500ML (single bottle)
         * - SAF-20L-JAR (office / home jar)
         */
        $definitions = [
            [
                'sku'   => 'SAF-500ML-CTN',
                'name'  => 'SAF-500ML standard',
                'notes' => '1 carton = 12 bottles, 12 caps, 12 labels, carton box, shrink wrap, 6L RO water, labour & utilities.',
                'lines' => [
                    ['product' => $petBottle,  'qty' => 12,  'unit' => 'bottle'],
                    ['product' => $cap,        'qty' => 12,  'unit' => 'cap'],
                    ['product' => $label,      'qty' => 12,  'unit' => 'label'],
                    ['product' => $carton500,  'qty' => 1,   'unit' => 'carton'],
                    ['product' => $shrinkWrap, 'qty' => 1,   'unit' => 'wrap'],
                    ['product' => $water,      'qty' => 6.0, 'unit' => 'liter'],
                    ['product' => $labour,     'qty' => 0.5, 'unit' => 'day'],
                    ['product' => $utilities,  'qty' => 0.2, 'unit' => 'shift'],
                ],
            ],
            [
                'sku'   => 'SAF-500ML',
                'name'  => 'SAF-500ML bottle standard',
                'notes' => 'Single bottle with PET, cap, label, 0.5L RO water, labour & utilities.',
                'lines' => [
                    ['product' => $petBottle, 'qty' => 1,   'unit' => 'bottle'],
                    ['product' => $cap,       'qty' => 1,   'unit' => 'cap'],
                    ['product' => $label,     'qty' => 1,   'unit' => 'label'],
                    ['product' => $water,     'qty' => 0.5, 'unit' => 'liter'],
                    ['product' => $labour,    'qty' => 0.05, 'unit' => 'day'],
                    ['product' => $utilities, 'qty' => 0.02, 'unit' => 'shift'],
                ],
            ],
            [
                'sku'   => 'SAF-20L-JAR',
                'name'  => 'SAF-20L jar standard',
                'notes' => '20L office / home jar – mostly water, plus labour & utilities.',
                'lines' => [
                    ['product' => $water,     'qty' => 20.0, 'unit' => 'liter'],
                    ['product' => $labour,    'qty' => 0.25, 'unit' => 'day'],
                    ['product' => $utilities, 'qty' => 0.1,  'unit' => 'shift'],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $finished = Product::where('sku', $definition['sku'])->first();

            if (! $finished) {
                continue;
            }

            $bom = BillOfMaterial::firstOrCreate(
                [
                    'product_id' => $finished->id,
                    'name'       => $definition['name'],
                ],
                [
                    'is_active' => true,
                    'notes'     => $definition['notes'],
                ]
            );

            // If items already exist, do not duplicate
            if ($bom->items()->count() > 0) {
                continue;
            }

            $items = [];

            foreach ($definition['lines'] as $line) {
                $product = $line['product'] ?? null;

                if (! $product) {
                    continue;
                }

                $items[] = [
                    'component_product_id' => $product->id,
                    'quantity'             => $line['qty'],
                    'unit_cost'            => $product->standard_cost,
                    'unit'                 => $line['unit'],
                ];
            }

            if (empty($items)) {
                continue;
            }

            $bom->items()->createMany($items);

            // Also seed a BOM-level material_unit_cost (sum of qty * unit_cost)
            $materialUnitCost = 0.0;
            foreach ($items as $item) {
                if (! empty($item['unit_cost'])) {
                    $materialUnitCost += (float) $item['unit_cost'] * (float) $item['quantity'];
                }
            }

            if ($materialUnitCost > 0) {
                $bom->material_unit_cost = $materialUnitCost;
                $bom->save();
            }
        }
    }
}
