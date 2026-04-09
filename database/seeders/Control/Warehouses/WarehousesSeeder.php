<?php

namespace Database\Seeders\Control\Warehouses;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Warehouses
 */
class WarehousesSeeder extends Seeder
{
    public function run(): void
    {
        // Main factory
        Warehouse::firstOrCreate(
            ['name' => 'Factory'],
            [
                'address' => 'Tongi, Gazipur',
                'type'    => 'factory',
            ]
        );

        // Central distribution depot
        Warehouse::firstOrCreate(
            ['name' => 'Central Depot'],
            [
                'address' => 'Mirpur, Dhaka',
                'type'    => 'depot',
            ]
        );

        // Optional: an empty regional depot to demonstrate multi‑warehouse
        Warehouse::firstOrCreate(
            ['name' => 'Chattogram Depot'],
            [
                'address' => 'Chattogram City',
                'type'    => 'depot',
            ]
        );

        // Sylhet regional depot
        Warehouse::firstOrCreate(
            ['name' => 'Sylhet Depot'],
            [
                'address' => 'Zindabazar, Sylhet',
                'type'    => 'depot',
            ]
        );

        // Khulna regional depot
        Warehouse::firstOrCreate(
            ['name' => 'Khulna Depot'],
            [
                'address' => 'Boyra, Khulna',
                'type'    => 'depot',
            ]
        );

        // Dedicated returns / scrap warehouse for damaged goods
        Warehouse::firstOrCreate(
            ['name' => 'Returns & Scrap Warehouse'],
            [
                'address' => 'Factory premises – returns & damaged stock',
                'type'    => 'returns',
            ]
        );

        // Small consignment warehouse for key corporate client
        Warehouse::firstOrCreate(
            ['name' => 'Corporate Consignment Store'],
            [
                'address' => 'Gulshan, Dhaka – corporate client stock',
                'type'    => 'consignment',
            ]
        );
    }
}
