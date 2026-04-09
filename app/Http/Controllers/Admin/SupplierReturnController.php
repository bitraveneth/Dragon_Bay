<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class SupplierReturnController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $returns = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('stockEntry', function ($stockQuery) use ($warehouseIds) {
                    $stockQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->where('type', 'supplier-return')
            ->latest()
            ->paginate(15);

        return view('admin.returns.supplier.index', compact('returns'));
    }
}
