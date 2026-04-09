<?php

namespace Database\Seeders\Accounting;

use App\Models\LedgerEntry;
use App\Models\SalaryDistribution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Accounting → Payroll.
 */
class PayrollModuleSeeder extends Seeder
{
    public function run(): void
    {
        $salary = SalaryDistribution::with('employee')->latest('period_start')->first();

        if (! $salary) {
            return;
        }
        if (! $salary->employee) {
            return;
        }

        $gross = $salary->base_salary
            + $salary->bonus
            + $salary->ta_allowances
            + $salary->da_allowances
            + $salary->commission;
        $periodLabel = Carbon::parse($salary->period_start)->format('M Y');

        // DR Payroll Expense
        LedgerEntry::firstOrCreate([
            'account'     => 'Payroll Expense',
            'description' => 'Payroll expense for ' . $salary->employee->name . ' (' . $periodLabel . ')',
        ], [
            'debit'       => $gross,
            'credit'      => 0,
        ]);

        // CR Bank
        LedgerEntry::firstOrCreate([
            'account'     => 'Bank',
            'description' => 'Payroll payment for ' . $salary->employee->name . ' (' . $periodLabel . ')',
        ], [
            'debit'       => 0,
            'credit'      => $gross,
        ]);
    }
}
