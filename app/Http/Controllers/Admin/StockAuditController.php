<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockAudit;
use App\Models\StockEntry;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockAuditController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $audits = StockAudit::with('warehouse', 'product', 'batch')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest()
            ->paginate(20);
        $warehouses = Warehouse::when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();
        $productIds = StockEntry::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->distinct()
            ->pluck('product_id')
            ->filter()
            ->all();
        $products = Product::query()
            ->when($warehouseIds !== null, function ($query) use ($productIds) {
                $query->whereIn('id', $productIds ?: [0]);
            })
            ->orderBy('name')
            ->get();
        $batches = Batch::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->where(function ($batchQuery) use ($warehouseIds) {
                    $batchQuery
                        ->whereHas('stockEntries', function ($stockQuery) use ($warehouseIds) {
                            $stockQuery->whereIn('warehouse_id', $warehouseIds);
                        })
                        ->orWhereHas('productionRuns', function ($runQuery) use ($warehouseIds) {
                            $runQuery->whereIn('warehouse_id', $warehouseIds);
                        });
                });
            })
            ->orderBy('production_date', 'desc')
            ->get();

        return view('admin.stock.audit', compact('audits', 'warehouses', 'products', 'batches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'nullable|exists:batches,id',
            'counted_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->ensureWarehouseAccess((int) $data['warehouse_id']);

        if (! empty($data['batch_id'])) {
            $batchVisibleToWarehouse = Batch::query()
                ->whereKey($data['batch_id'])
                ->where('product_id', $data['product_id'])
                ->where(function ($query) use ($data) {
                    $query
                        ->whereHas('stockEntries', function ($stockQuery) use ($data) {
                            $stockQuery->where('warehouse_id', $data['warehouse_id']);
                        })
                        ->orWhereHas('productionRuns', function ($runQuery) use ($data) {
                            $runQuery->where('warehouse_id', $data['warehouse_id']);
                        });
                })
                ->exists();

            if (! $batchVisibleToWarehouse) {
                throw ValidationException::withMessages([
                    'batch_id' => 'Selected batch does not belong to the chosen product within the selected warehouse.',
                ]);
            }
        }

        $query = StockEntry::where('warehouse_id', $data['warehouse_id'])
            ->where('product_id', $data['product_id']);

        if (!empty($data['batch_id'])) {
            $query->where('batch_id', $data['batch_id']);
        }

        $systemQty = (float) $query->sum('quantity');
        $countedQty = (float) $data['counted_quantity'];
        $variance = $countedQty - $systemQty;

        StockAudit::create([
            'warehouse_id' => $data['warehouse_id'],
            'product_id' => $data['product_id'],
            'batch_id' => $data['batch_id'] ?? null,
            'system_quantity' => $systemQty,
            'counted_quantity' => $countedQty,
            'variance' => $variance,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.stock.audit')->with('status', 'Stock audit recorded.');
    }

    public function destroy(StockAudit $audit)
    {
        $this->ensureWarehouseAccess((int) $audit->warehouse_id);

        $audit->delete();

        return redirect()
            ->route('admin.stock.audit')
            ->with('status', 'Stock audit entry deleted.');
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }
}
