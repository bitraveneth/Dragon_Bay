<?php

namespace Database\Seeders\Control\Suppliers;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Supplier master records
 */
class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::firstOrCreate(
            ['name' => 'ABC Plastics'],
            [
                'contact_person' => 'Mr. Rahman',
                'email'          => 'abc.plastics@example.com',
                'phone'          => '01711-000001',
                'address'        => 'Tongi Industrial Area, Gazipur',
                'tax_id'         => 'BIN-PLAST-001',
            ]
        );

        Supplier::firstOrCreate(
            ['name' => 'XYZ Labels'],
            [
                'contact_person' => 'Ms. Akter',
                'email'          => 'xyz.labels@example.com',
                'phone'          => '01711-000002',
                'address'        => 'Dhaka Printing Zone',
                'tax_id'         => 'BIN-LABEL-001',
            ]
        );

        Supplier::firstOrCreate(
            ['name' => 'CartonCo'],
            [
                'contact_person' => 'Mr. Hossain',
                'email'          => 'sales@cartonco.example.com',
                'phone'          => '01711-000003',
                'address'        => 'Savar, Dhaka',
                'tax_id'         => 'BIN-CARTON-001',
            ]
        );

        Supplier::firstOrCreate(
            ['name' => 'Packaging Ltd'],
            [
                'contact_person' => 'Mr. Karim',
                'email'          => 'info@packaging.example.com',
                'phone'          => '01711-000004',
                'address'        => 'Kashimpur, Gazipur',
                'tax_id'         => 'BIN-PACK-001',
            ]
        );

        Supplier::firstOrCreate(
            ['name' => 'Local Water Provider'],
            [
                'contact_person' => 'Water Plant Manager',
                'email'          => 'water@saf-utilities.example.com',
                'phone'          => '01711-000005',
                'address'        => 'Local municipality water treatment plant',
                'tax_id'         => 'BIN-WATER-001',
            ]
        );

        // Service providers used in BOM cost examples
        Supplier::firstOrCreate(
            ['name' => 'John Contractor'],
            [
                'contact_person' => 'John Contractor',
                'email'          => 'john.contractor@example.com',
                'phone'          => '01711-000006',
                'address'        => 'Factory labour contractor',
                'tax_id'         => 'BIN-LABOUR-001',
            ]
        );

        Supplier::firstOrCreate(
            ['name' => 'WaterTech Services'],
            [
                'contact_person' => 'Service Manager',
                'email'          => 'support@watertech.example.com',
                'phone'          => '01711-000007',
                'address'        => 'RO plant maintenance services',
                'tax_id'         => 'BIN-RO-001',
            ]
        );

        Supplier::firstOrCreate(
            ['name' => 'XYZ Logistics'],
            [
                'contact_person' => 'Logistics Coordinator',
                'email'          => 'ops@xyzlogistics.example.com',
                'phone'          => '01711-000008',
                'address'        => 'Factory to warehouse transport',
                'tax_id'         => 'BIN-LOG-001',
            ]
        );
    }
}
