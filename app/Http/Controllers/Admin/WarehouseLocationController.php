<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\StockEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseLocationController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $warehouses = Warehouse::with(['locations' => function ($query) {
                $query->orderBy('code');
            }])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();

        return view('admin.warehouses.locations', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => [
                'required',
                'string',
                Rule::unique('warehouse_locations', 'code')->where(function ($query) use ($request) {
                    $query->where('warehouse_id', $request->input('warehouse_id'));
                }),
            ],
            'description' => 'nullable|string',
        ]);

        $this->ensureWarehouseAccess((int) $data['warehouse_id']);

        WarehouseLocation::create($data);

        return redirect()->route('admin.warehouse-locations.index')->with('status', 'Location added.');
    }

    public function destroy(WarehouseLocation $location)
    {
        $this->ensureWarehouseAccess((int) $location->warehouse_id);

        if (StockEntry::where('warehouse_location_id', $location->id)->exists()) {
            return redirect()->route('admin.warehouse-locations.index')
                ->with('error', 'Location is used in stock entries and cannot be deleted. Move or clear stock first.');
        }

        if ($location->goodsReceiptItems()->exists()) {
            return redirect()->route('admin.warehouse-locations.index')
                ->with('error', 'Location is referenced by goods receipts and cannot be deleted because it would erase historical bin information.');
        }

        $location->delete();

        return redirect()->route('admin.warehouse-locations.index')
            ->with('status', 'Location deleted.');
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }
}
