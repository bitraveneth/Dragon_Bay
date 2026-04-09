<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\DeliveryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    public function index()
    {
        $returns = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order.agent'])
            ->where('type', 'customer-return')
            ->latest()
            ->paginate(15);

        return view('admin.returns.customer.index', compact('returns'));
    }

    public function create()
    {
        $orders = Order::with(['agent', 'deliveries.items.batch'])
            ->where('status', 'delivered')
            ->latest()
            ->limit(50)
            ->get();
        $products = Product::orderBy('name')->get();
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();

        $returnableBatches = $orders->mapWithKeys(function (Order $order) {
            $batchMap = collect($order->deliveries)
                ->flatMap(fn ($delivery) => $delivery->items)
                ->groupBy(fn (DeliveryItem $item) => (int) $item->product_id)
                ->map(function ($items) {
                    return $items
                        ->groupBy(fn (DeliveryItem $item) => $item->batch_id ?: '__unbatched__')
                        ->map(function ($batchItems, $batchKey) {
                            $quantity = (float) $batchItems->sum(function (DeliveryItem $item) {
                                return DeliveryItem::realizedQuantityFromTotals(
                                    (float) $item->qty_dispatched,
                                    (float) $item->qty_delivered,
                                    (float) $item->qty_short,
                                    (float) $item->qty_damaged
                                );
                            });

                            $batch = $batchItems->first()?->batch;

                            return [
                                'batch_id' => $batchKey === '__unbatched__' ? null : (int) $batchKey,
                                'label' => $batch?->batch_code ?? 'No batch recorded',
                                'quantity' => $quantity,
                            ];
                        })
                        ->values()
                        ->all();
                })
                ->all();

            return [$order->id => $batchMap];
        });

        return view('admin.returns.customer.create', compact('orders', 'products', 'warehouses', 'returnableBatches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'nullable|exists:batches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $order = Order::with(['deliveries.items.batch'])->findOrFail($data['order_id']);
        $this->ensureWarehouseAccess((int) $data['warehouse_id']);

        if ($order->status !== 'delivered') {
            return back()
                ->withErrors(['order_id' => 'Returns can only be recorded for delivered orders.'])
                ->withInput();
        }

        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $orderItem) {
            return back()
                ->withErrors(['product_id' => 'Selected product is not part of this order.'])
                ->withInput();
        }

        $deliveredByBatch = collect($order->deliveries)
            ->flatMap(fn ($delivery) => $delivery->items)
            ->filter(function (DeliveryItem $item) use ($orderItem, $data) {
                return (int) $item->order_item_id === (int) $orderItem->id
                    && (int) $item->product_id === (int) $data['product_id'];
            })
            ->groupBy(fn (DeliveryItem $item) => $item->batch_id ?: '__unbatched__')
            ->map(function ($items, $batchKey) {
                return [
                    'batch_id' => $batchKey === '__unbatched__' ? null : (int) $batchKey,
                    'quantity' => (float) $items->sum(function (DeliveryItem $item) {
                        return DeliveryItem::realizedQuantityFromTotals(
                            (float) $item->qty_dispatched,
                            (float) $item->qty_delivered,
                            (float) $item->qty_short,
                            (float) $item->qty_damaged
                        );
                    }),
                ];
            })
            ->values();

        if ($deliveredByBatch->isEmpty()) {
            $deliveredByBatch = collect([[
                'batch_id' => null,
                'quantity' => (float) $orderItem->quantity,
            ]]);
        }

        $selectedBatchId = $data['batch_id'] ?? null;
        $batchedDeliveries = $deliveredByBatch->whereNotNull('batch_id')->values();

        if ($selectedBatchId === null && $batchedDeliveries->count() === 1) {
            $selectedBatchId = (int) $batchedDeliveries->first()['batch_id'];
        }

        if ($selectedBatchId === null && $batchedDeliveries->count() > 1) {
            return back()
                ->withErrors(['batch_id' => 'Select the batch being returned for this product.'])
                ->withInput();
        }

        if ($selectedBatchId !== null) {
            $belongsToProduct = Batch::whereKey($selectedBatchId)
                ->where('product_id', $data['product_id'])
                ->exists();

            if (! $belongsToProduct) {
                return back()
                    ->withErrors(['batch_id' => 'Selected batch does not belong to the chosen product.'])
                    ->withInput();
            }
        }

        $batchDelivery = $deliveredByBatch->first(function (array $batchRow) use ($selectedBatchId) {
            return (int) ($batchRow['batch_id'] ?? 0) === (int) ($selectedBatchId ?? 0);
        });

        if ($selectedBatchId !== null && ! $batchDelivery) {
            return back()
                ->withErrors(['batch_id' => 'Selected batch was not delivered for this order line.'])
                ->withInput();
        }

        $deliveredQuantity = (float) ($batchDelivery['quantity'] ?? $deliveredByBatch->sum('quantity'));

        $alreadyReturned = (float) StockMovement::where('order_id', $order->id)
            ->where('type', 'customer-return')
            ->whereHas('stockEntry', function ($query) use ($data, $selectedBatchId) {
                $query->where('product_id', $data['product_id']);

                if ($selectedBatchId === null) {
                    $query->whereNull('batch_id');
                } else {
                    $query->where('batch_id', $selectedBatchId);
                }
            })
            ->sum('quantity');

        $remainingReturnable = max($deliveredQuantity - $alreadyReturned, 0);
        if ((float) $data['quantity'] > $remainingReturnable) {
            return back()
                ->withErrors(['quantity' => 'Return quantity exceeds the delivered quantity remaining for this product.'])
                ->withInput();
        }

        DB::transaction(function () use ($data, $order, $selectedBatchId) {
            $entry = StockEntry::query()
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $data['product_id'])
                ->where('status', 'available')
                ->whereNull('order_id')
                ->when(
                    $selectedBatchId === null,
                    fn ($query) => $query->whereNull('batch_id'),
                    fn ($query) => $query->where('batch_id', $selectedBatchId)
                )
                ->orderByRaw('case when warehouse_location_id is null then 0 else 1 end')
                ->lockForUpdate()
                ->first();

            if (! $entry) {
                $entry = StockEntry::create([
                    'warehouse_id' => $data['warehouse_id'],
                    'warehouse_location_id' => null,
                    'product_id' => $data['product_id'],
                    'batch_id' => $selectedBatchId,
                    'quantity' => 0,
                    'status' => 'available',
                ]);
            }

            $entry->quantity = (float) $entry->quantity + (float) $data['quantity'];
            $entry->save();

            StockMovement::recordFor(
                $entry,
                'customer-return',
                (float) $data['quantity'],
                $data['notes'],
                $order->id
            );
        });

        return redirect()->route('admin.returns.customer.index')->with('status', 'Customer return recorded and stock updated.');
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }
}
