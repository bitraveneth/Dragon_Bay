<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\NewBatchCreated;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Validation\Rule;

class BatchController extends Controller
{
    public function index()
    {
        $warehouseIds = $this->accessibleWarehouseIds();

        $batches = Batch::with('product')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->where(function ($batchQuery) use ($warehouseIds) {
                    $batchQuery
                        ->whereHas('stockEntries', function ($stockQuery) use ($warehouseIds) {
                            $stockQuery->whereIn('warehouse_id', $warehouseIds);
                        })
                        ->orWhereHas('productionRuns', function ($runQuery) use ($warehouseIds) {
                            $runQuery->whereIn('warehouse_id', $warehouseIds);
                        })
                        ->orWhere(function ($unlinkedQuery) {
                            $unlinkedQuery->whereDoesntHave('stockEntries')
                                ->whereDoesntHave('productionRuns');
                        });
                });
            })
            ->latest('production_date')
            ->paginate(10);
        $products = $this->batchProducts();

        return view('admin.batches.index', compact('batches', 'products'));
    }

    public function show(Batch $batch)
    {
        $warehouseIds = $this->ensureBatchAccess($batch);

        $batch->load([
            'product',
            'productionRuns' => function ($query) use ($warehouseIds) {
                $query->when($warehouseIds !== null, function ($runQuery) use ($warehouseIds) {
                    $runQuery->whereIn('warehouse_id', $warehouseIds);
                })->with('warehouse');
            },
            'stockEntries' => function ($query) use ($warehouseIds) {
                $query->when($warehouseIds !== null, function ($stockQuery) use ($warehouseIds) {
                    $stockQuery->whereIn('warehouse_id', $warehouseIds);
                })->with(['warehouse', 'location', 'movements.order.agent']);
            },
        ]);

        $producedQty = $batch->productionRuns->sum('quantity');
        $onHandQty   = $batch->stockEntries->where('status', 'available')->sum('quantity');
        $reservedQty = $batch->stockEntries->where('status', 'reserved')->sum('quantity');

        $movements = $batch->stockEntries
            ->flatMap(function ($entry) {
                return $entry->movements;
            })
            ->sortByDesc('created_at');

        $writtenOffQty = $movements
            ->whereIn('type', ['expired', 'wasted', 'supplier-return', 'production-loss', 'other'])
            ->sum(function ($m) {
                return abs($m->quantity);
            });

        $customerReturnQty = $movements
            ->where('type', 'customer-return')
            ->sum('quantity');

        $productBatchIds = Batch::where('product_id', $batch->product_id)
            ->pluck('id')
            ->values();

        $legacyCustomerReturnQty = StockMovement::with(['stockEntry', 'order.deliveries.items'])
            ->where('type', 'customer-return')
            ->whereHas('stockEntry', function ($query) use ($batch, $warehouseIds) {
                $query->where('product_id', $batch->product_id)
                    ->whereNull('batch_id')
                    ->when($warehouseIds !== null, function ($stockQuery) use ($warehouseIds) {
                        $stockQuery->whereIn('warehouse_id', $warehouseIds);
                    });
            })
            ->get()
            ->filter(function (StockMovement $movement) use ($batch, $productBatchIds) {
                $deliveryBatchIds = collect($movement->order?->deliveries ?? [])
                    ->flatMap(function ($delivery) use ($batch) {
                        return $delivery->items->where('product_id', $batch->product_id);
                    })
                    ->pluck('batch_id')
                    ->filter()
                    ->unique()
                    ->values();

                if ($deliveryBatchIds->count() === 1) {
                    return (int) $deliveryBatchIds->first() === (int) $batch->id;
                }

                if ($deliveryBatchIds->isEmpty() && $productBatchIds->count() === 1) {
                    return (int) $productBatchIds->first() === (int) $batch->id;
                }

                return false;
            })
            ->sum('quantity');

        $customerReturnQty += $legacyCustomerReturnQty;

        return view('admin.batches.show', [
            'batch'             => $batch,
            'producedQty'       => $producedQty,
            'onHandQty'         => $onHandQty,
            'reservedQty'       => $reservedQty,
            'writtenOffQty'     => $writtenOffQty,
            'customerReturnQty' => $customerReturnQty,
            'movements'         => $movements,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $batch = Batch::create($data);

        // Notify admins and QC officers that a new batch has been recorded
        $recipients = User::query()
            ->with('userRoles')
            ->get()
            ->filter(fn (User $user) => $user->hasAnyRole(['admin', 'qc_officer']))
            ->unique('id')
            ->values();

        foreach ($recipients as $user) {
            $user->notify(new NewBatchCreated($batch));
        }

        return back()->with('status', 'Batch recorded.');
    }

    public function edit(Batch $batch)
    {
        $this->ensureBatchAccess($batch);
        $products = $this->batchProducts();
        return view('admin.batches.edit', compact('batch', 'products'));
    }

    public function update(Request $request, Batch $batch)
    {
        $this->ensureBatchAccess($batch);
        $data = $this->validated($request, $batch);

        $batch->update($data);
        return redirect()->route('admin.batches.index')->with('status', 'Batch updated.');
    }

    public function destroy(Batch $batch)
    {
        $this->ensureBatchAccess($batch);

        if ($batch->productionRuns()->exists()) {
            return redirect()->route('admin.batches.index')
                ->with('error', 'Batch is linked to production runs and cannot be deleted.');
        }

        if ($batch->stockEntries()->exists()) {
            return redirect()->route('admin.batches.index')
                ->with('error', 'Batch has stock entries and cannot be deleted.');
        }

        $batch->delete();

        return redirect()->route('admin.batches.index')->with('status', 'Batch deleted.');
    }

    protected function batchProducts()
    {
        return Product::sellable()
            ->orderBy('name')
            ->get();
    }

    protected function validated(Request $request, ?Batch $batch = null): array
    {
        return $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(function (QueryBuilder $query) {
                    $query->where('is_active', true)
                        ->where(function (QueryBuilder $productQuery) {
                            $productQuery->whereNull('product_type')
                                ->orWhere('product_type', 'finished');
                        });
                }),
            ],
            'batch_code' => [
                'required',
                'string',
                Rule::unique('batches', 'batch_code')
                    ->ignore($batch?->id)
                    ->where(function ($query) use ($request) {
                        $query->where('product_id', $request->input('product_id'));
                    }),
            ],
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:production_date',
            'qc_status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);
    }

    protected function accessibleWarehouseIds(): ?array
    {
        return auth()->user()?->accessibleWarehouseIds();
    }

    protected function ensureBatchAccess(Batch $batch): ?array
    {
        $warehouseIds = $this->accessibleWarehouseIds();

        if ($warehouseIds === null) {
            return null;
        }

        $hasAnyWarehouseLinkedData = $batch->stockEntries()->exists() || $batch->productionRuns()->exists();
        if (! $hasAnyWarehouseLinkedData) {
            return $warehouseIds;
        }

        $isVisible = $batch->stockEntries()
            ->whereIn('warehouse_id', $warehouseIds)
            ->exists()
            || $batch->productionRuns()
                ->whereIn('warehouse_id', $warehouseIds)
                ->exists();

        if (! $isVisible) {
            abort(403, 'You do not have access to this batch.');
        }

        return $warehouseIds;
    }
}
