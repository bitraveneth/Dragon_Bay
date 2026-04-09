<?php

namespace Database\Seeders\Manufacturing;

use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\Employee;
use App\Models\ProductionRun;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Manufacturing → Production orders.
 *
 * Goal: keep the Production module and production analysis report looking
 * "alive" on a fresh demo database without the user having to create runs
 * manually.
 *
 * We seed a few demo ProductionRun records for SAF-500ML-CTN:
 * - One pending run (QC pending, today)
 * - One approved run waiting for warehouse stock confirmation (today)
 * - One completed run with material cost snapshot (yesterday)
 *
 * Pending receipts and the Production analysis report both read directly
 * from these runs, so we do not need a separate data set.
 */
class ProductionOrdersModuleSeeder extends Seeder
{
    public function run(): void
    {
        // If there are already production runs in the system we assume the
        // user has real data and we do not append demo rows.
        if (ProductionRun::query()->exists()) {
            return;
        }

        $product = Product::where('sku', 'SAF-500ML-CTN')->first();
        $factory = Warehouse::where('name', 'Factory')->first();

        if (! $product || ! $factory) {
            return;
        }

        // Use an existing batch for this product if available; otherwise
        // create a simple one so that views have something to link to.
        $batch = Batch::where('product_id', $product->id)
            ->orderByDesc('production_date')
            ->first();

        if (! $batch) {
            $today = Carbon::today();
            $batch = Batch::create([
                'product_id'      => $product->id,
                'batch_code'      => '500ML-CTN-' . $today->format('ymd') . '-SEED',
                'production_date' => $today,
                'expiry_date'     => $today->copy()->addMonths(6),
                'qc_status'       => 'pending',
                'notes'           => 'Seeded batch for demo production runs',
            ]);
        }

        // Supervisor / approver / stock confirmer references
        $supervisor      = Employee::where('name', 'Production Manager')->first();
        $adminUser       = User::where('role', 'admin')->first();
        $qcUser          = User::where('role', 'qc_officer')->first();
        $warehouseUser   = User::where('role', 'warehouse_officer')->first();
        $approverUserId  = optional($qcUser ?: $adminUser)->id;
        $confirmerUserId = optional($warehouseUser ?: $adminUser)->id;

        $today     = Carbon::today();
        $yesterday = $today->copy()->subDay();

        // Helper closure to keep order numbers consistent with the controller
        $generateOrderNumber = static function (Carbon $date, int $seq): string {
            $dateKey = $date->format('Ymd');
            $suffix  = str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

            return "PO-{$dateKey}-{$suffix}";
        };

        // 1) Today – pending QC
        ProductionRun::create([
            'order_number'    => $generateOrderNumber($today, 1),
            'product_id'      => $product->id,
            'batch_id'        => $batch->id,
            'warehouse_id'    => $factory->id,
            'line'            => 'Line 1',
            'shift'           => 'Morning',
            'quantity'        => 25000,
            'status'          => 'confirmed',
            'qc_status'       => 'pending',
            'supervisor_id'   => optional($supervisor)->id,
            'materials_reserved' => 'Auto-seeded demo run – materials reserved from Factory stock.',
            'created_at'      => $today->copy()->setTime(8, 30),
            'updated_at'      => $today->copy()->setTime(8, 30),
        ]);

        // 2) Today – QC approved, waiting for warehouse manager
        ProductionRun::create([
            'order_number'    => $generateOrderNumber($today, 2),
            'product_id'      => $product->id,
            'batch_id'        => $batch->id,
            'warehouse_id'    => $factory->id,
            'line'            => 'Line 1',
            'shift'           => 'Evening',
            'quantity'        => 18000,
            'status'          => 'confirmed',
            'qc_status'       => 'approved',
            'supervisor_id'   => optional($supervisor)->id,
            'approved_by'     => $approverUserId,
            'approved_at'     => $today->copy()->setTime(13, 0),
            'materials_reserved' => 'Demo run – QC approved, awaiting stock confirmation.',
            'created_at'      => $today->copy()->setTime(10, 0),
            'updated_at'      => $today->copy()->setTime(13, 0),
        ]);

        // 3) Yesterday – completed run with costing + stock confirmed
        $quantityCompleted = 30000;

        // Try to derive a realistic material_unit_cost from the active BOM so
        // that the production analysis / P&L reports have a sensible COGS.
        $materialUnitCost = null;
        $bom = BillOfMaterial::where('product_id', $product->id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        if ($bom && $bom->material_unit_cost !== null && $bom->material_unit_cost > 0) {
            $materialUnitCost = (float) $bom->material_unit_cost;
        }

        $completedRun = new ProductionRun([
            'order_number'       => $generateOrderNumber($yesterday, 1),
            'product_id'         => $product->id,
            'batch_id'           => $batch->id,
            'warehouse_id'       => $factory->id,
            'line'               => 'Line 2',
            'shift'              => 'Morning',
            'quantity'           => $quantityCompleted,
            'status'             => 'completed',
            'qc_status'          => 'approved',
            'supervisor_id'      => optional($supervisor)->id,
            'approved_by'        => $approverUserId,
            'approved_at'        => $yesterday->copy()->setTime(14, 0),
            'stock_confirmed_by' => $confirmerUserId,
            'stock_confirmed_at' => $yesterday->copy()->setTime(16, 0),
            'materials_reserved' => 'Demo run – stock already confirmed to Factory.',
            'created_at'         => $yesterday->copy()->setTime(9, 0),
            'updated_at'         => $yesterday->copy()->setTime(16, 0),
        ]);

        if ($materialUnitCost !== null) {
            $completedRun->material_unit_cost = $materialUnitCost;
            $completedRun->material_total_cost = $materialUnitCost * (float) $quantityCompleted;
        }

        $completedRun->save();
    }
}
