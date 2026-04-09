<?php

namespace Database\Seeders\Accounting;

use App\Models\LedgerEntry;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Balance sheet.
 */
class BalanceSheetModuleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Similar to P&L, the balance sheet is derived from ledger
         * entries and Chart of Accounts. Nothing to insert here – we
         * simply rely on the other accounting seeders to populate the
         * necessary LedgerEntry rows.
         */
        if (! LedgerEntry::query()->exists()) {
            return;
        }
    }
}
