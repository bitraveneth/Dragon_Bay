<?php

namespace Database\Seeders\Manufacturing;

use App\Models\ProductionRun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Manufacturing → Pending receipts.
 *
 * In most cases this will be derived from production orders,
 * but a dedicated seeder is kept for clarity.
 */
class PendingReceiptsModuleSeeder extends Seeder
{
    public function run(): void
    {
        // We want a few QC‑approved production runs where stock has
        // not yet been confirmed to any warehouse so that the
        // "Pending stock receipts" screen has rich demo data.

        // If such runs already exist, don't duplicate them.
        $existing = ProductionRun::where('qc_status', 'approved')
            ->whereNull('stock_confirmed_at')
            ->count();

        if ($existing > 0) {
            return;
        }

        $today = Carbon::today();

        // Pick a couple of existing runs as templates if available.
        $template = ProductionRun::with('product', 'batch', 'warehouse')
            ->orderBy('id')
            ->first();

        if (! $template) {
            return;
        }

        // Duplicate a few "pending receipt" runs with different
        // quantities / lines / shifts.
        $definitions = [
            ['line' => 'Line 1', 'shift' => 'Morning', 'qty' => 15000, 'offset' => 0],
            ['line' => 'Line 1', 'shift' => 'Evening', 'qty' => 12000, 'offset' => 0],
            ['line' => 'Line 2', 'shift' => 'Morning', 'qty' => 18000, 'offset' => -1],
        ];

        foreach ($definitions as $idx => $def) {
            ProductionRun::create([
                'order_number'       => 'PEND-' . $today->copy()->addDays($def['offset'])->format('Ymd') . '-' . ($idx + 1),
                'product_id'         => $template->product_id,
                'batch_id'           => $template->batch_id,
                'warehouse_id'       => $template->warehouse_id,
                'line'               => $def['line'],
                'shift'              => $def['shift'],
                'quantity'           => $def['qty'],
                'status'             => 'confirmed',
                'qc_status'          => 'approved',
                'supervisor_id'      => $template->supervisor_id,
                'approved_by'        => $template->approved_by,
                'approved_at'        => $today->copy()->addDays($def['offset'])->setTime(14, 0),
                'materials_reserved' => 'Demo run – waiting for warehouse stock confirmation.',
                'created_at'         => $today->copy()->addDays($def['offset'])->setTime(8 + $idx, 0),
                'updated_at'         => $today->copy()->addDays($def['offset'])->setTime(14, 0),
            ]);
        }
    }
}
