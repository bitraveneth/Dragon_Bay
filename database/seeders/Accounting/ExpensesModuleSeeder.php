<?php

namespace Database\Seeders\Accounting;

use App\Models\Expense;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Expenses.
 */
class ExpensesModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Marketing expense
        Expense::firstOrCreate(
            [
                'date'      => now()->subDays(5)->toDateString(),
                'category'  => 'Marketing',
                'reference' => 'MKT-2026-001',
            ],
            [
                'description' => 'Facebook and Google Ads spend (demo)',
                'amount'      => 30000,
                'status'      => 'recorded',
            ]
        );

        // Utilities
        Expense::firstOrCreate(
            [
                'date'      => now()->subDays(3)->toDateString(),
                'category'  => 'Utilities',
                'reference' => 'UTIL-2026-001',
            ],
            [
                'description' => 'Factory electricity bill – demo',
                'amount'      => 12000,
                'status'      => 'recorded',
            ]
        );

        // Travel & TA
        Expense::firstOrCreate(
            [
                'date'      => now()->subDay()->toDateString(),
                'category'  => 'Travel & TA',
                'reference' => 'TA-2026-001',
            ],
            [
                'description' => 'Field sales visit TA/DA – demo',
                'amount'      => 8000,
                'status'      => 'recorded',
            ]
        );
    }
}
