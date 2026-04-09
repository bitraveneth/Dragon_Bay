<?php

namespace Database\Seeders\Control\Products;

use App\Models\Product;
use App\Models\TaxClass;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Materials (raw / service / in‑house) used in BOMs.
 */
class MaterialsSeeder extends Seeder
{
    public function run(): void
    {
        $vatExempt = TaxClass::where('name', 'VAT exempt')->first();

        // Fallback in case VAT exempt not seeded yet
        if (! $vatExempt) {
            $vatExempt = TaxClass::create([
                'name' => 'VAT exempt',
                'rate' => 0,
            ]);
        }

        // --- Raw materials (buy it → tracked as stock) ----------------------

        Product::firstOrCreate(
            ['sku' => 'RM-PET-500'],
            [
                'name'          => 'PET Bottle 500ml',
                'product_type'  => 'raw',
                'uom'           => 'piece',
                // Approximate BDT cost per empty 500ml bottle
                'standard_cost' => 5.00,
                'supplier_name' => 'ABC Plastics',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        Product::firstOrCreate(
            ['sku' => 'RM-CAP-STD'],
            [
                'name'          => 'Bottle Cap – Standard',
                'product_type'  => 'raw',
                'uom'           => 'piece',
                // Standard 28mm cap per piece (BDT)
                'standard_cost' => 0.80,
                'supplier_name' => 'ABC Plastics',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        Product::firstOrCreate(
            ['sku' => 'RM-LABEL-500'],
            [
                'name'          => 'BOPP Label – 500ml Bottle',
                'product_type'  => 'raw',
                'uom'           => 'piece',
                // Printed BOPP label per piece (BDT)
                'standard_cost' => 0.60,
                'supplier_name' => 'XYZ Labels',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        Product::firstOrCreate(
            ['sku' => 'RM-CARTON-12X500'],
            [
                'name'          => 'Carton Box – 12 x 500ml',
                'product_type'  => 'raw',
                'uom'           => 'piece',
                // Corrugated carton per 12x500ml case (BDT)
                'standard_cost' => 20.00,
                'supplier_name' => 'CartonCo',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        Product::firstOrCreate(
            ['sku' => 'RM-SHRINK-CTN'],
            [
                'name'          => 'Shrink Wrap Film – Carton',
                'product_type'  => 'raw',
                'uom'           => 'piece',
                // Shrink film per carton (BDT)
                'standard_cost' => 2.50,
                'supplier_name' => 'Packaging Ltd',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        // Treated water – could be tracked as raw if needed
        Product::firstOrCreate(
            ['sku' => 'RM-RO-WATER'],
            [
                'name'          => 'Treated RO Water',
                'product_type'  => 'raw',
                'uom'           => 'liter',
                // Per liter cost including treatment chemicals (BDT)
                'standard_cost' => 0.30,
                'supplier_name' => 'Local Water Provider',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        // --- Service / external cost (pay for it → no stock) ---------------

        Product::firstOrCreate(
            ['sku' => 'SV-LAB-FACT'],
            [
                'name'          => 'Labour – Factory Line',
                'product_type'  => 'service',
                'uom'           => 'day',
                // Daily contract labour cost per head (BDT)
                'standard_cost' => 1200.00,
                'supplier_name' => 'John Contractor',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        Product::firstOrCreate(
            ['sku' => 'SV-UTIL-SHIFT'],
            [
                'name'          => 'Electricity / Utilities per Shift',
                'product_type'  => 'service',
                'uom'           => 'shift',
                // Average utilities (electricity, water, air) per shift (BDT)
                'standard_cost' => 600.00,
                'supplier_name' => 'Local Utility',
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        // --- In‑house / self‑made steps (no stock) -------------------------

        Product::firstOrCreate(
            ['sku' => 'IH-LABEL-PRINT'],
            [
                'name'          => 'Label Printing',
                'product_type'  => 'inhouse',
                'uom'           => 'piece',
                'standard_cost' => 0.00,
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );

        Product::firstOrCreate(
            ['sku' => 'IH-SHRINK-PROC'],
            [
                'name'          => 'Shrink Wrapping / Case Making',
                'product_type'  => 'inhouse',
                'uom'           => 'piece',
                'standard_cost' => 0.00,
                'tax_class_id'  => $vatExempt->id,
                'base_price'    => 0,
                'is_active'     => true,
            ]
        );
    }
}
