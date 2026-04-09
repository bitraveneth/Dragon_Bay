<?php

namespace Database\Seeders\Control\Products;

use App\Models\TaxClass;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Tax & VAT classes (including HSN/SAC codes).
 */
class TaxVatClassesSeeder extends Seeder
{
    public function run(): void
    {
        // Standard VAT 15% – drinking water (HSN 2201)
        TaxClass::firstOrCreate(
            ['name' => 'Standard VAT 15%'],
            [
                'hsn_code'       => '2201',
                'local_tax_code' => 'BD-VAT-15',
                'rate'           => 15,
            ]
        );

        // VAT exempt – for items or services with no VAT
        TaxClass::firstOrCreate(
            ['name' => 'VAT exempt'],
            [
                'hsn_code'       => null,
                'local_tax_code' => null,
                'rate'           => 0,
            ]
        );
    }
}

