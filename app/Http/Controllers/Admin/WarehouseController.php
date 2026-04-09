<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockEntry;
use App\Models\ProductionRun;
use App\Models\WarehouseLocation;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $warehouses = Warehouse::withCount('entries')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->paginate(8);

        $entries = StockEntry::with(['product', 'batch', 'warehouse'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        return view('admin.warehouses.index', compact('warehouses', 'entries'));
    }

    public function create()
    {
        return view('admin.warehouses.create');
    }

    public function show(Warehouse $warehouse)
    {
        $this->ensureWarehouseAccess($warehouse);

        // Load all stock entries for this warehouse so the view can present
        // finished goods and raw materials in separate sections.
        $entries = StockEntry::with(['product', 'batch'])
            ->where('warehouse_id', $warehouse->id)
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.warehouses.show', compact('warehouse', 'entries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        Warehouse::create($data);
        return back()->with('status', 'Warehouse added.');
    }

    public function edit(Warehouse $warehouse)
    {
        $this->ensureWarehouseAccess($warehouse);
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->ensureWarehouseAccess($warehouse);

        $data = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $warehouse->update($data);

        return redirect()->route('admin.warehouses.index')->with('status', 'Warehouse updated.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->ensureWarehouseAccess($warehouse);

        if ($warehouse->entries()->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Warehouse has stock entries and cannot be deleted.');
        }

        if (ProductionRun::where('warehouse_id', $warehouse->id)->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Warehouse is linked to production runs and cannot be deleted.');
        }

         if (WarehouseLocation::where('warehouse_id', $warehouse->id)->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Warehouse has locations configured and cannot be deleted. Delete those locations first.');
        }

        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('status', 'Warehouse deleted.');
    }

    public function clearStock(Warehouse $warehouse)
    {
        $this->ensureWarehouseAccess($warehouse);

        $warehouse->load('entries.movements');

        $hasProtectedEntries = $warehouse->entries->contains(function (StockEntry $entry) {
            return $entry->status !== 'available' || ! is_null($entry->order_id);
        });

        if ($hasProtectedEntries) {
            return redirect()
                ->route('admin.warehouses.index')
                ->with('error', 'Warehouse contains reserved or order-linked stock and cannot be cleared automatically.');
        }

        DB::transaction(function () use ($warehouse) {
            foreach ($warehouse->entries as $entry) {
                $quantity = (float) $entry->quantity;
                if ($quantity <= 0) {
                    continue;
                }

                $entry->quantity = 0;
                $entry->save();

                $entry->movements()->create([
                    'type' => 'other',
                    'quantity' => $quantity * -1,
                    'notes' => 'Cleared from warehouse maintenance screen.',
                ]);
            }
        });

        return redirect()->route('admin.warehouses.index')->with('status', 'All stock cleared from warehouse.');
    }

    protected function ensureWarehouseAccess(Warehouse $warehouse): void
    {
        if (! auth()->user()?->canAccessWarehouse((int) $warehouse->id)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }
}
