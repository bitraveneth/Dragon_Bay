<?php

namespace Database\Seeders\Control\Products;

use App\Models\PackagingType;
use App\Models\Product;
use App\Models\TaxClass;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Finished Products (sellable SKUs)
 */
class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $vat15 = TaxClass::where('name', 'Standard VAT 15%')->first();
        $carton500 = PackagingType::where('name', 'Carton 12 x 500ml')->first();
        $bottle500 = PackagingType::where('name', 'Bottle 500ml')->first();
        $carton1L  = PackagingType::where('name', 'Carton 12 x 1L')->first();
        $jar20L    = PackagingType::where('name', 'Jar 20L')->first();

        // Fallbacks in case other seeders have not run yet
        if (! $vat15) {
            $vat15 = TaxClass::create([
                'name' => 'Standard VAT 15%',
                'rate' => 15,
            ]);
        }

        if (! $carton500) {
            $carton500 = PackagingType::create([
                'name'        => 'Carton 12 x 500ml',
                'unit'        => 'carton',
                'description' => 'Carton containing 12 x 500ml bottles',
            ]);
        }

        if (! $bottle500) {
            $bottle500 = PackagingType::create([
                'name'        => 'Bottle 500ml',
                'unit'        => 'bottle',
                'description' => 'Single 500ml PET bottle',
            ]);
        }

        if (! $carton1L) {
            $carton1L = PackagingType::create([
                'name'        => 'Carton 12 x 1L',
                'unit'        => 'carton',
                'description' => 'Carton containing 12 x 1L bottles',
            ]);
        }

        if (! $jar20L) {
            $jar20L = PackagingType::create([
                'name'        => 'Jar 20L',
                'unit'        => 'jar',
                'description' => 'Refillable 20L water jar',
            ]);
        }

        // One demo finished product: SAF Mineral Water 500ml – Carton (12 bottles)
        Product::firstOrCreate(
            ['sku' => 'SAF-500ML-CTN'],
            [
                'product_type'      => 'finished',
                'name'             => 'SAF Mineral Water 500ml – Carton (12 bottles)',
                'description'      => 'Premium mineral water, 500ml × 12 bottles per carton',
                'size'             => '500ml carton',
                'uom'              => 'carton',
                'volume_ml'        => 500,
                'packaging_type_id'=> $carton500->id,
                'tax_class_id'     => $vat15->id,
                'mineral_source'   => 'SAF Plant',
                'ph'               => 7.2,
                'tds'              => 150,
                'base_price'       => 550, // demo selling price per carton
                'is_active'        => true,
            ]
        );

        // Single 500ml bottle SKU
        Product::firstOrCreate(
            ['sku' => 'SAF-500ML'],
            [
                'product_type'      => 'finished',
                'name'             => 'SAF Mineral Water 500ml – Bottle',
                'description'      => 'Single 500ml PET bottle',
                'size'             => '500ml',
                'uom'              => 'bottle',
                'volume_ml'        => 500,
                'packaging_type_id'=> $bottle500->id,
                'tax_class_id'     => $vat15->id,
                'mineral_source'   => 'SAF Plant',
                'ph'               => 7.2,
                'tds'              => 150,
                'base_price'       => 45,  // demo retail price per bottle
                'is_active'        => true,
            ]
        );

        // 1L carton (12 bottles)
        Product::firstOrCreate(
            ['sku' => 'SAF-1L-CTN'],
            [
                'product_type'      => 'finished',
                'name'             => 'SAF Mineral Water 1L – Carton (12 bottles)',
                'description'      => 'Premium mineral water, 1L × 12 bottles per carton',
                'size'             => '1L carton',
                'uom'              => 'carton',
                'volume_ml'        => 1000,
                'packaging_type_id'=> $carton1L->id,
                'tax_class_id'     => $vat15->id,
                'mineral_source'   => 'SAF Plant',
                'ph'               => 7.2,
                'tds'              => 150,
                'base_price'       => 900, // demo price per 1L carton
                'is_active'        => true,
            ]
        );

        // 20L jar
        Product::firstOrCreate(
            ['sku' => 'SAF-20L-JAR'],
            [
                'product_type'      => 'finished',
                'name'             => 'SAF Mineral Water 20L – Jar',
                'description'      => 'Refillable 20L jar for offices and homes',
                'size'             => '20L',
                'uom'              => 'jar',
                'volume_ml'        => 20000,
                'packaging_type_id'=> $jar20L->id,
                'tax_class_id'     => $vat15->id,
                'mineral_source'   => 'SAF Plant',
                'ph'               => 7.2,
                'tds'              => 150,
                'base_price'       => 250, // demo price per jar
                'is_active'        => true,
            ]
        );
    }
}
