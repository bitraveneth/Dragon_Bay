<?php

namespace Database\Seeders\Control\Employees;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for:
 * - Employee leaves.
 */
class LeavesSeeder extends Seeder
{
    public function run(): void
    {
        $warehouseManager = Employee::where('name', 'Warehouse Manager')->first();
        $salesRep01       = Employee::where('name', 'Field Sales Rep 01')->first();
        $qcOfficer        = Employee::where('name', 'QC Officer')->first();

        if (! $warehouseManager && ! $salesRep01 && ! $qcOfficer) {
            return;
        }

        // Warehouse Manager – upcoming approved annual leave
        if ($warehouseManager) {
            $start = Carbon::today()->copy()->addDays(7);
            $end   = $start->copy()->addDay();

            EmployeeLeave::firstOrCreate(
                [
                    'employee_id' => $warehouseManager->id,
                    'start_date'  => $start,
                    'end_date'    => $end,
                    'type'        => 'annual',
                ],
                [
                    'reason'       => 'Planned annual leave',
                    'status'       => 'approved',
                    'approved_by'  => 'Admin Staff',
                    'approved_at'  => Carbon::now(),
                ]
            );
        }

        // Field Sales Rep – sick leave (pending approval)
        if ($salesRep01) {
            $sickStart = Carbon::today()->copy()->addDays(2);
            $sickEnd   = $sickStart->copy()->addDays(2);

            EmployeeLeave::firstOrCreate(
                [
                    'employee_id' => $salesRep01->id,
                    'start_date'  => $sickStart,
                    'end_date'    => $sickEnd,
                    'type'        => 'sick',
                ],
                [
                    'reason'      => 'Flu and high fever – demo data',
                    'status'      => 'pending',
                    'approved_by' => null,
                    'approved_at' => null,
                ]
            );
        }

        // QC officer – past casual leave (rejected example)
        if ($qcOfficer) {
            $casualStart = Carbon::today()->copy()->subDays(10);
            $casualEnd   = $casualStart->copy()->addDay();

            EmployeeLeave::firstOrCreate(
                [
                    'employee_id' => $qcOfficer->id,
                    'start_date'  => $casualStart,
                    'end_date'    => $casualEnd,
                    'type'        => 'casual',
                ],
                [
                    'reason'       => 'Personal work – demo rejected leave',
                    'status'       => 'rejected',
                    'approved_by'  => 'Admin Staff',
                    'approved_at'  => Carbon::now()->subDays(9),
                ]
            );
        }
    }
}
