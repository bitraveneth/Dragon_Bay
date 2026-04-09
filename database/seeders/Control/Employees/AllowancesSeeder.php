<?php

namespace Database\Seeders\Control\Employees;

use App\Models\Employee;
use App\Models\EmployeeAllowance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for:
 * - Employee allowances (TA/DA/BONUS etc.).
 */
class AllowancesSeeder extends Seeder
{
    public function run(): void
    {
        $salesRep01 = Employee::where('name', 'Field Sales Rep 01')->first();
        $warehouseManager = Employee::where('name', 'Warehouse Manager')->first();
        $qcOfficer = Employee::where('name', 'QC Officer')->first();

        if (! $salesRep01 && ! $warehouseManager && ! $qcOfficer) {
            return;
        }

        $today = Carbon::today();

        if ($salesRep01) {
            // Example: TA claim for a field visit day
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $salesRep01->id,
                    'date'        => $today->copy()->subDay(),
                    'type'        => 'TA',
                    'reference'   => 'TA-SR01-001',
                ],
                [
                    'amount'          => 800,
                    'description'     => 'Dhaka North route field visits',
                    'status'          => 'approved',
                    'attachment_path' => null,
                ]
            );

            // DA allowance for late‑night visit
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $salesRep01->id,
                    'date'        => $today->copy()->subDays(2),
                    'type'        => 'DA',
                    'reference'   => 'DA-SR01-001',
                ],
                [
                    'amount'          => 500,
                    'description'     => 'Dinner allowance during late route coverage',
                    'status'          => 'submitted',
                    'attachment_path' => null,
                ]
            );
        }

        if ($warehouseManager) {
            // Warehouse manager – local conveyance TA
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $warehouseManager->id,
                    'date'        => $today->copy()->subDays(3),
                    'type'        => 'TA',
                    'reference'   => 'TA-WHM-001',
                ],
                [
                    'amount'          => 300,
                    'description'     => 'Travel to Chattogram Depot for stock audit',
                    'status'          => 'approved',
                    'attachment_path' => null,
                ]
            );

            // Warehouse manager – additional DA allowance
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $warehouseManager->id,
                    'date'        => $today->copy()->subDays(4),
                    'type'        => 'DA',
                    'reference'   => 'DA-WHM-001',
                ],
                [
                    'amount'          => 400,
                    'description'     => 'Late shift allowance during stock reconciliation',
                    'status'          => 'approved',
                    'attachment_path' => null,
                ]
            );

            // Warehouse manager – performance bonus
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $warehouseManager->id,
                    'date'        => $today->copy()->subDays(6),
                    'type'        => 'BONUS',
                    'reference'   => 'BONUS-WHM-001',
                ],
                [
                    'amount'          => 1500,
                    'description'     => 'Bonus for reducing stock discrepancies',
                    'status'          => 'approved',
                    'attachment_path' => null,
                ]
            );
        }

        if ($qcOfficer) {
            // QC officer – TA allowance for plant visit
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $qcOfficer->id,
                    'date'        => $today->copy()->subDays(4),
                    'type'        => 'TA',
                    'reference'   => 'TA-QC-001',
                ],
                [
                    'amount'          => 600,
                    'description'     => 'Travel to factory for QC inspection',
                    'status'          => 'approved',
                    'attachment_path' => null,
                ]
            );

            // QC officer – DA allowance for extended shift
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $qcOfficer->id,
                    'date'        => $today->copy()->subDays(3),
                    'type'        => 'DA',
                    'reference'   => 'DA-QC-001',
                ],
                [
                    'amount'          => 400,
                    'description'     => 'Dinner allowance during night‑shift QC',
                    'status'          => 'approved',
                    'attachment_path' => null,
                ]
            );

            // QC officer – bonus allowance for successful audit
            EmployeeAllowance::firstOrCreate(
                [
                    'employee_id' => $qcOfficer->id,
                    'date'        => $today->copy()->subDays(5),
                    'type'        => 'BONUS',
                    'reference'   => 'BONUS-QC-001',
                ],
                [
                    'amount'          => 1000,
                    'description'     => 'Bonus for first batch QC and documentation',
                    'status'          => 'approved',
                    'attachment_path' => null,
                ]
            );
        }
    }
}
