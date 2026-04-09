<?php

namespace Database\Seeders\Control\Employees;

use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for:
 * - Employee contracts.
 */
class ContractsSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $warehouseManager = Employee::where('name', 'Warehouse Manager')->first();
        $salesRep01       = Employee::where('name', 'Field Sales Rep 01')->first();
        $qcOfficer        = Employee::where('name', 'QC Officer')->first();

        if ($warehouseManager) {
            EmployeeContract::firstOrCreate(
                [
                    'employee_id' => $warehouseManager->id,
                    'reference'   => 'WHM-CT-001',
                ],
                [
                    'start_date'        => $today->copy()->subMonths(6),
                    'end_date'          => null,
                    'working_schedule'  => 'Sunday–Thursday, 9am–6pm',
                    'salary_amount'     => 45000,
                    'travel_allowance'  => 1500,
                    'dearness_allowance'=> 800,
                    'bonus'             => 5000,
                    'status'            => 'active',
                ]
            );
        }

        if ($salesRep01) {
            EmployeeContract::firstOrCreate(
                [
                    'employee_id' => $salesRep01->id,
                    'reference'   => 'SR01-CT-001',
                ],
                [
                    'start_date'        => $today->copy()->subMonths(3),
                    'end_date'          => null,
                    'working_schedule'  => 'Field visits, 6 days/week',
                    'salary_amount'     => 25000,
                    'travel_allowance'  => 5000,
                    'dearness_allowance'=> 2000,
                    'bonus'             => 3000,
                    'status'            => 'active',
                ]
            );
        }

        if ($qcOfficer) {
            EmployeeContract::firstOrCreate(
                [
                    'employee_id' => $qcOfficer->id,
                    'reference'   => 'QC-CT-001',
                ],
                [
                    'start_date'        => $today->copy()->subMonths(3),
                    'end_date'          => null,
                    'working_schedule'  => 'Sunday–Thursday, 9am–6pm',
                    'salary_amount'     => 30000,
                    'travel_allowance'  => 1000,
                    'dearness_allowance'=> 500,
                    'bonus'             => 2000,
                    'status'            => 'active',
                ]
            );
        }
    }
}
