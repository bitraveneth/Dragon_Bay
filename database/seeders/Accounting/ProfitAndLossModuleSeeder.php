<?php

namespace Database\Seeders\Accounting;

use App\Models\LedgerEntry;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Profit & Loss.
 */
class ProfitAndLossModuleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * We do not need to insert extra rows here – P&L is calculated
         * from existing ledger entries (Sales Revenue, COGS, Expenses).
         *
         * This seeder only exists so that the module structure mirrors
         * the sidebar. All of the interesting numbers come from the
         * other accounting seeders which already create LedgerEntry rows.
         */
        if (! LedgerEntry::query()->exists()) {
            return;
        }
    }
}
