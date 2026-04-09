<?php

namespace Database\Seeders\Accounting;

use App\Models\Employee;
use App\Models\SalaryDistribution;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Salary distributions.
 */
class SalaryDistributionsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Use a small group of demo employees so the Salary
        // distributions screen looks alive after seeding.
        $employees = Employee::orderBy('name')->take(6)->get();

        if ($employees->isEmpty()) {
            return;
        }

        $from = now()->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->toDateString();

        $patterns = [
            [
                'base_salary'   => 35000,
                'bonus'         => 5000,
                'ta_allowances' => 2000,
                'da_allowances' => 1500,
                'commission'    => 0,
                'payment_method'=> 'bank',
            ],
            [
                'base_salary'   => 28000,
                'bonus'         => 3000,
                'ta_allowances' => 1500,
                'da_allowances' => 1200,
                'commission'    => 0,
                'payment_method'=> 'cash',
            ],
            [
                'base_salary'   => 42000,
                'bonus'         => 8000,
                'ta_allowances' => 2500,
                'da_allowances' => 2000,
                'commission'    => 3500,
                'payment_method'=> 'bank',
            ],
        ];

        foreach ($employees as $index => $employee) {
            $pattern = $patterns[$index % count($patterns)];

            SalaryDistribution::firstOrCreate(
                [
                    'employee_id'  => $employee->id,
                    'period_start' => $from,
                    'period_end'   => $to,
                ],
                $pattern + [
                    'document_path' => null,
                    'remarks'       => 'Demo monthly salary for '.$employee->name,
                ]
            );
        }
    }
}
