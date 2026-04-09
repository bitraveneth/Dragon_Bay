<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $movements = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereHas('stockEntry', function ($stockQuery) use ($warehouseIds) {
                    $stockQuery->whereIn('warehouse_id', $warehouseIds);
                });
            })
            ->latest()
            ->paginate(12);
        return view('admin.stock.movements', compact('movements'));
    }

    public function create()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $entries = StockEntry::with(['product', 'warehouse', 'batch'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->whereHas('product')
            ->whereHas('warehouse')
            ->where('status', 'available')
            ->orderByDesc('updated_at')
            ->get();

        // Split into two trays: material stock (raw / service) and finished products.
        $materialEntries = $entries->filter(function (StockEntry $entry) {
            $type = $entry->product->product_type ?? null;
            return in_array($type, ['raw', 'service']);
        });

        $finishedEntries = $entries->reject(function (StockEntry $entry) {
            $type = $entry->product->product_type ?? null;
            return in_array($type, ['raw', 'service']);
        });

        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();
        $locations = WarehouseLocation::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->orderBy('warehouse_id')
            ->orderBy('code')
            ->get(['id', 'warehouse_id', 'code']);

        return view('admin.stock.transfer', compact('materialEntries', 'finishedEntries', 'warehouses', 'locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entry_id' => 'required|exists:stock_entries,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $this->ensureWarehouseAccess((int) $data['destination_warehouse_id']);
        $destinationLocationId = $this->resolveDestinationLocationId($data);

        DB::transaction(function () use ($data, $destinationLocationId) {
            $entry = StockEntry::whereKey($data['entry_id'])->lockForUpdate()->firstOrFail();
            $this->ensureStockEntryAccess($entry);

            if ($entry->status !== 'available') {
                throw ValidationException::withMessages([
                    'entry_id' => 'Only available stock can be transferred.',
                ]);
            }

            if ((int) $entry->warehouse_id === (int) $data['destination_warehouse_id']) {
                throw ValidationException::withMessages([
                    'destination_warehouse_id' => 'Choose a different destination warehouse for transfers.',
                ]);
            }

            if ((float) $entry->quantity < (float) $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Cannot transfer more than available quantity.',
                ]);
            }

            $entry->quantity = (float) $entry->quantity - (float) $data['quantity'];
            $entry->save();

            $newEntry = StockEntry::firstOrCreate(
                [
                    'warehouse_id' => $data['destination_warehouse_id'],
                    'warehouse_location_id' => $destinationLocationId,
                    'product_id' => $entry->product_id,
                    'batch_id' => $entry->batch_id,
                    'status' => 'available',
                ],
                [
                    'quantity' => 0,
                ]
            );
            $newEntry->quantity = (float) $newEntry->quantity + (float) $data['quantity'];
            $newEntry->save();

            $destinationWarehouse = Warehouse::find($data['destination_warehouse_id']);
            $destinationLocation = $destinationLocationId
                ? WarehouseLocation::find($destinationLocationId)
                : null;
            $sourceWarehouse = Warehouse::find($entry->warehouse_id);
            $sourceLocation = $entry->warehouse_location_id
                ? WarehouseLocation::find($entry->warehouse_location_id)
                : null;

            $destinationNote = 'Transferred to ' . ($destinationWarehouse?->name ?? ('warehouse #' . $data['destination_warehouse_id']));
            if ($destinationLocation) {
                $destinationNote .= ' / ' . $destinationLocation->code;
            }

            StockMovement::recordFor(
                $entry,
                'transfer-out',
                (float) $data['quantity'] * -1,
                $destinationNote
            );

            $notes = $data['notes'] ?? 'Transferred from ' . ($sourceWarehouse?->name ?? ('warehouse #' . $entry->warehouse_id));
            if ($sourceLocation) {
                $notes .= ' / ' . $sourceLocation->code;
            }

            StockMovement::recordFor(
                $newEntry,
                'transfer-in',
                (float) $data['quantity'],
                $notes
            );
        });

        return redirect()->route('admin.stock.movements')->with('status', 'Stock transferred.');
    }

    public function writeOffForm(Request $request)
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $selectedEntryId = $request->input('entry_id');
        $filterWarehouse = $request->input('warehouse_id');
        $filterProduct   = $request->input('product_id');
        $filterBatch     = $request->input('batch_id');
        $defaultReason   = $request->input('reason');

        $entries = StockEntry::with(['product', 'warehouse'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->where('status', 'available')
            ->when($filterWarehouse, function ($q) use ($filterWarehouse) {
                $q->where('warehouse_id', $filterWarehouse);
            })
            ->when($filterProduct, function ($q) use ($filterProduct) {
                $q->where('product_id', $filterProduct);
            })
            ->when($filterBatch, function ($q) use ($filterBatch) {
                $q->where('batch_id', $filterBatch);
            })
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.stock.writeoff', compact('entries', 'selectedEntryId', 'defaultReason'));
    }

    public function writeOffStore(Request $request)
    {
        $data = $request->validate([
            'entry_id' => 'required|exists:stock_entries,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|in:expired,wasted,supplier-return,production-loss,other',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $entry = StockEntry::whereKey($data['entry_id'])->lockForUpdate()->firstOrFail();
            $this->ensureStockEntryAccess($entry);

            if ($entry->status !== 'available') {
                throw ValidationException::withMessages([
                    'entry_id' => 'Only available stock can be written off.',
                ]);
            }

            if ((float) $entry->quantity < (float) $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Cannot write off more than available quantity.',
                ]);
            }

            $entry->quantity = (float) $entry->quantity - (float) $data['quantity'];
            $entry->save();

            StockMovement::recordFor(
                $entry,
                $data['reason'],
                (float) $data['quantity'] * -1,
                $data['notes']
            );
        });

        return redirect()->route('admin.stock.movements')->with('status', 'Stock written off.');
    }

    public function writeOffEntry(StockEntry $entry)
    {
        $result = DB::transaction(function () use ($entry) {
            $lockedEntry = StockEntry::whereKey($entry->id)->lockForUpdate()->firstOrFail();
            $this->ensureStockEntryAccess($lockedEntry);

            if ($lockedEntry->status !== 'available') {
                throw ValidationException::withMessages([
                    'entry' => 'Only available stock can be written off from the inventory dashboard.',
                ]);
            }

            if ((float) $lockedEntry->quantity <= 0) {
                return false;
            }

            $quantity = (float) $lockedEntry->quantity;

            $lockedEntry->quantity = 0;
            $lockedEntry->save();

            StockMovement::recordFor(
                $lockedEntry,
                'expired',
                $quantity * -1,
                'Written off as expired from inventory view.'
            );

            return true;
        });

        if (! $result) {
            return back()->with('status', 'Entry already has zero quantity.');
        }

        return back()->with('status', 'Batch written off as expired.');
    }

    protected function resolveDestinationLocationId(array $data): ?int
    {
        $destinationWarehouseId = (int) $data['destination_warehouse_id'];
        $locationId = isset($data['destination_warehouse_location_id'])
            ? (int) $data['destination_warehouse_location_id']
            : null;

        $destinationLocations = WarehouseLocation::where('warehouse_id', $destinationWarehouseId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! empty($destinationLocations) && ! $locationId) {
            throw ValidationException::withMessages([
                'destination_warehouse_location_id' => 'Select a destination location for the chosen warehouse.',
            ]);
        }

        if ($locationId && ! in_array($locationId, $destinationLocations, true)) {
            throw ValidationException::withMessages([
                'destination_warehouse_location_id' => 'Selected destination location does not belong to the chosen warehouse.',
            ]);
        }

        return $locationId ?: null;
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }

    protected function ensureStockEntryAccess(StockEntry $entry): void
    {
        $this->ensureWarehouseAccess((int) $entry->warehouse_id);
    }
}
