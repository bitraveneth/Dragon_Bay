<?php

namespace Database\Seeders\Accounting;

use App\Models\LedgerEntry;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Cashflow.
 */
class CashflowModuleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * Cashflow reporting is also based on LedgerEntry rows
         * (primarily movements on Bank and similar accounts),
         * so there is nothing specific to seed here. We keep this
         * class so that the structure matches the sidebar.
         */
        if (! LedgerEntry::query()->exists()) {
            return;
        }
    }
}
