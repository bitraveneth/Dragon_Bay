<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ProductionRun;
use Carbon\Carbon;

class ManufacturingDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $runQuery = ProductionRun::with(['product', 'warehouse'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            });

        $runs = $runQuery->get();

        $totalRuns = $runs->count();
        $totalQuantity = $runs->sum('quantity');

        $approvedRuns = $runs->where('qc_status', 'approved');
        $approvedQuantity = $approvedRuns->sum('quantity');

        $pendingQcCount = $runs->where('qc_status', '!=', 'approved')->count();

        // Batches produced this month
        $batches = Batch::with('product')
            ->whereBetween('production_date', [$startOfMonth, $endOfMonth])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('productionRuns', function ($runQuery) use ($warehouseIds) {
                    $runQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->get();

        $batchCount = $batches->count();

        $expiringSoon = Batch::with('product')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [$today, $today->copy()->addDays(60)])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('stockEntries', function ($stockQuery) use ($warehouseIds) {
                    $stockQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->get();

        // Top products by produced quantity
        $topProducts = $runs
            ->groupBy(fn ($run) => (string) ($run->product_id ?? 'unknown'))
            ->map(function ($group) {
                $product = $group->first()->product;

                return [
                    'product_id' => $product?->id,
                    'product_name' => $product?->name ?? 'Unknown product',
                    'qty' => $group->sum('quantity'),
                    'runs' => $group->count(),
                ];
            })
            ->sortByDesc('qty')
            ->values()
            ->take(5);

        // Output by line / shift
        $byLine = $runs
            ->groupBy('line')
            ->map(fn ($group) => $group->sum('quantity'))
            ->sortDesc();

        $byShift = $runs
            ->groupBy('shift')
            ->map(fn ($group) => $group->sum('quantity'))
            ->sortDesc();

        return view('admin.manufacturing.dashboard', [
            'periodLabel' => $startOfMonth->format('d M Y') . ' – ' . $endOfMonth->format('d M Y'),
            'totalRuns' => $totalRuns,
            'totalQuantity' => $totalQuantity,
            'approvedQuantity' => $approvedQuantity,
            'approvedRunsCount' => $approvedRuns->count(),
            'pendingQcCount' => $pendingQcCount,
            'batchCount' => $batchCount,
            'expiringSoon' => $expiringSoon,
            'topProducts' => $topProducts,
            'byLine' => $byLine,
            'byShift' => $byShift,
        ]);
    }
}
