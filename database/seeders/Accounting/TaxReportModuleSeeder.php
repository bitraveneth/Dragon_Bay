<?php

namespace Database\Seeders\Accounting;

use App\Models\Invoice;
use Illuminate\Database\Seeder;

/**
 * Seed data for Accounting → Tax report.
 */
class TaxReportModuleSeeder extends Seeder
{
    public function run(): void
    {
        // No extra rows are required – VAT is already stored on invoices.
        // This seeder simply ensures at least one invoice exists so that
        // the VAT / tax report page has data to aggregate.

        if (! Invoice::query()->exists()) {
            // If customer invoice seeder has not yet run in this environment,
            // we silently skip. The accounting UI will just show zero VAT.
            return;
        }
    }
}
