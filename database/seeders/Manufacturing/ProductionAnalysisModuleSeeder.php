<?php

namespace Database\Seeders\Manufacturing;

use App\Models\ProductionRun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Manufacturing → Production analysis reports.
 */
class ProductionAnalysisModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure there is a small time‑series of production runs so
        // that the Manufacturing / Reports dashboards have trends
        // to show (last 7‑14 days).

        // If we already have more than 10 runs spanning several days,
        // we assume another seeder has populated data and we bail out.
        if (ProductionRun::count() > 10) {
            return;
        }

        $template = ProductionRun::with('product', 'batch', 'warehouse')
            ->orderBy('id')
            ->first();

        if (! $template) {
            return;
        }

        $today = Carbon::today();

        // Create a simple week of production output on two lines.
        for ($i = 1; $i <= 7; $i++) {
            $day = $today->copy()->subDays($i);

            ProductionRun::create([
                'order_number'       => 'HIST-' . $day->format('Ymd') . '-L1',
                'product_id'         => $template->product_id,
                'batch_id'           => $template->batch_id,
                'warehouse_id'       => $template->warehouse_id,
                'line'               => 'Line 1',
                'shift'              => 'Morning',
                'quantity'           => 15000 + ($i * 500),
                'status'             => 'completed',
                'qc_status'          => 'approved',
                'supervisor_id'      => $template->supervisor_id,
                'approved_by'        => $template->approved_by,
                'approved_at'        => $day->copy()->setTime(14, 0),
                'stock_confirmed_by' => $template->stock_confirmed_by,
                'stock_confirmed_at' => $day->copy()->setTime(16, 0),
                'materials_reserved' => 'Historical demo run for production analysis.',
                'created_at'         => $day->copy()->setTime(8, 30),
                'updated_at'         => $day->copy()->setTime(16, 0),
            ]);

            ProductionRun::create([
                'order_number'       => 'HIST-' . $day->format('Ymd') . '-L2',
                'product_id'         => $template->product_id,
                'batch_id'           => $template->batch_id,
                'warehouse_id'       => $template->warehouse_id,
                'line'               => 'Line 2',
                'shift'              => 'Evening',
                'quantity'           => 12000 + ($i * 400),
                'status'             => 'completed',
                'qc_status'          => 'approved',
                'supervisor_id'      => $template->supervisor_id,
                'approved_by'        => $template->approved_by,
                'approved_at'        => $day->copy()->setTime(21, 0),
                'stock_confirmed_by' => $template->stock_confirmed_by,
                'stock_confirmed_at' => $day->copy()->setTime(22, 0),
                'materials_reserved' => 'Historical demo run for production analysis.',
                'created_at'         => $day->copy()->setTime(17, 0),
                'updated_at'         => $day->copy()->setTime(22, 0),
            ]);
        }
    }
}
