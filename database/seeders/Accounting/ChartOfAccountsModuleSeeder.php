<?php

namespace Database\Seeders\Accounting;

use App\Models\Account;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Chart of accounts.
 *
 * Creates a very small but realistic chart so that
 * ledger entries, P&L and balance sheet screens
 * have something to aggregate.
 */
class ChartOfAccountsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Assets
        Account::firstOrCreate(
            ['code' => '1000'],
            ['name' => 'Bank', 'type' => 'asset', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '1100'],
            ['name' => 'Accounts Receivable', 'type' => 'asset', 'is_active' => true]
        );

        // Liabilities
        Account::firstOrCreate(
            ['code' => '2000'],
            ['name' => 'VAT Payable', 'type' => 'liability', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '2100'],
            ['name' => 'Accounts Payable', 'type' => 'liability', 'is_active' => true]
        );

        // Equity
        Account::firstOrCreate(
            ['code' => '3000'],
            ['name' => 'Owner\'s Equity', 'type' => 'equity', 'is_active' => true]
        );

        // Income
        Account::firstOrCreate(
            ['code' => '4000'],
            ['name' => 'Sales Revenue', 'type' => 'income', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '4100'],
            ['name' => 'Other Income', 'type' => 'income', 'is_active' => true]
        );

        // Expenses
        Account::firstOrCreate(
            ['code' => '5000'],
            ['name' => 'Cost of Goods Sold', 'type' => 'expense', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '5100'],
            ['name' => 'Selling & Distribution Expense', 'type' => 'expense', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '5200'],
            ['name' => 'Marketing Expense', 'type' => 'expense', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '5300'],
            ['name' => 'Payroll Expense', 'type' => 'expense', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '5400'],
            ['name' => 'Utilities Expense', 'type' => 'expense', 'is_active' => true]
        );
    }
}
