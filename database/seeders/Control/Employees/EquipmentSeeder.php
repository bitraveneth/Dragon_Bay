<?php

namespace Database\Seeders\Control\Employees;

use App\Models\Employee;
use App\Models\EmployeeEquipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for:
 * - Employee equipment (devices, tabs, phones).
 */
class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $salesRep01       = Employee::where('name', 'Field Sales Rep 01')->first();
        $warehouseManager = Employee::where('name', 'Warehouse Manager')->first();
        $qcOfficer        = Employee::where('name', 'QC Officer')->first();

        if (! $salesRep01 && ! $warehouseManager && ! $qcOfficer) {
            return;
        }

        $effectiveDate = Carbon::today()->copy()->subMonths(1);

        if ($salesRep01) {
            // Field sales rep – tablet for order taking
            EmployeeEquipment::firstOrCreate(
                [
                    'employee_id'    => $salesRep01->id,
                    'product_name'   => 'Android tablet',
                    'effective_date' => $effectiveDate,
                ],
                [
                    'device_identifier' => 'TAB-SR01-0001',
                    'status'            => 'assigned',
                    'notes'             => 'Issued for order taking and location tracking app.',
                ]
            );

            // Field sales rep – smartphone for communication
            EmployeeEquipment::firstOrCreate(
                [
                    'employee_id'    => $salesRep01->id,
                    'product_name'   => 'Smartphone',
                    'effective_date' => $effectiveDate->copy()->subWeeks(2),
                ],
                [
                    'device_identifier' => 'PHN-SR01-0001',
                    'status'            => 'assigned',
                    'notes'             => 'Company SIM with data pack for CRM and WhatsApp orders.',
                ]
            );
        }

        if ($warehouseManager) {
            // Warehouse manager – handheld barcode scanner
            EmployeeEquipment::firstOrCreate(
                [
                    'employee_id'    => $warehouseManager->id,
                    'product_name'   => 'Handheld barcode scanner',
                    'effective_date' => $effectiveDate->copy()->subWeeks(3),
                ],
                [
                    'device_identifier' => 'SCAN-WHM-0001',
                    'status'            => 'assigned',
                    'notes'             => 'Used for stock counting and picking verification.',
                ]
            );

            // Warehouse manager – safety helmet
            EmployeeEquipment::firstOrCreate(
                [
                    'employee_id'    => $warehouseManager->id,
                    'product_name'   => 'Safety helmet',
                    'effective_date' => $effectiveDate->copy()->subWeeks(4),
                ],
                [
                    'device_identifier' => null,
                    'status'            => 'assigned',
                    'notes'             => 'Personal protective equipment for warehouse floor.',
                ]
            );
        }

        if ($qcOfficer) {
            // QC officer – TDS & pH meter
            EmployeeEquipment::firstOrCreate(
                [
                    'employee_id'    => $qcOfficer->id,
                    'product_name'   => 'TDS & pH meter',
                    'effective_date' => $effectiveDate->copy()->subWeeks(1),
                ],
                [
                    'device_identifier' => 'QC-MTR-0001',
                    'status'            => 'assigned',
                    'notes'             => 'Used for lab testing of each production batch.',
                ]
            );
        }
    }
}
