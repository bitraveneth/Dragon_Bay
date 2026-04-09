<?php

namespace Database\Seeders\Control\Products;

use App\Models\PackagingType;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Packaging types (bottles, cartons, etc.).
 */
class PackagingTypesSeeder extends Seeder
{
    public function run(): void
    {
        PackagingType::firstOrCreate(
            ['name' => 'Bottle 500ml'],
            [
                'unit'        => 'bottle',
                'description' => 'Single 500ml PET bottle',
            ]
        );

        PackagingType::firstOrCreate(
            ['name' => 'Carton 12 x 500ml'],
            [
                'unit'        => 'carton',
                'description' => 'Carton containing 12 x 500ml bottles',
            ]
        );

        PackagingType::firstOrCreate(
            ['name' => 'Carton 12 x 1L'],
            [
                'unit'        => 'carton',
                'description' => 'Carton containing 12 x 1L bottles',
            ]
        );

        PackagingType::firstOrCreate(
            ['name' => 'Jar 20L'],
            [
                'unit'        => 'jar',
                'description' => 'Refillable 20L water jar',
            ]
        );
    }
}
