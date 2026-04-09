<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $receipts = GoodsReceipt::with(['supplier', 'warehouse', 'purchaseOrder'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest('received_at')
            ->paginate(15);

        return view('admin.goods_receipts.index', compact('receipts'));
    }

    public function create(Request $request)
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $suppliers = Supplier::orderBy('name')->get();
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
            ->orderBy('code')
            ->get();
        $products = Product::orderBy('name')->get();
        $purchaseBills = PurchaseBill::orderByDesc('bill_date')->limit(100)->get();

        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.product'])
            ->whereIn('status', ['approved', 'partial_received'])
            ->orderByDesc('order_date')
            ->get();

        $selectedPo = null;
        if ($request->filled('purchase_order_id')) {
            $selectedPo = $purchaseOrders->firstWhere('id', (int) $request->input('purchase_order_id'));
        }

        return view('admin.goods_receipts.create', compact(
            'suppliers',
            'warehouses',
            'locations',
            'products',
            'purchaseBills',
            'purchaseOrders',
            'selectedPo'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'purchase_bill_id' => 'nullable|exists:purchase_bills,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'received_at' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.warehouse_location_id' => 'nullable|exists:warehouse_locations,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.qc_status' => 'required|in:pending,approved,rejected',
            'items.*.remarks' => 'nullable|string',
        ]);

        $this->ensureWarehouseAccess((int) $data['warehouse_id']);

        $purchaseOrder = null;
        if (! empty($data['purchase_order_id'])) {
            $purchaseOrder = PurchaseOrder::with('items')
                ->findOrFail($data['purchase_order_id']);

            if (! in_array($purchaseOrder->status, ['approved', 'partial_received'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order_id' => 'Only approved or partially received purchase orders can be received.',
                ]);
            }

            if ((int) $purchaseOrder->supplier_id !== (int) $data['supplier_id']) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Selected supplier does not match the purchase order supplier.',
                ]);
            }
        }

        $purchaseBill = null;
        if (! empty($data['purchase_bill_id'])) {
            $purchaseBill = PurchaseBill::findOrFail($data['purchase_bill_id']);

            if ((int) $purchaseBill->supplier_id !== (int) $data['supplier_id']) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Selected supplier does not match the purchase bill supplier.',
                ]);
            }

            if (StockEntry::where('purchase_bill_id', $purchaseBill->id)->exists()) {
                throw ValidationException::withMessages([
                    'purchase_bill_id' => 'This purchase bill has already posted inventory. Create the receipt without re-posting stock from the bill.',
                ]);
            }
        }

        $validatedItems = $this->validateReceiptItems($data, $purchaseOrder);

        $receipt = null;

        DB::transaction(function () use ($data, $validatedItems, $purchaseOrder, &$receipt) {
            $receipt = GoodsReceipt::create([
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'purchase_bill_id' => $data['purchase_bill_id'] ?? null,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => auth()->id(),
                'grn_number' => 'GRN-' . str_pad((string) (GoodsReceipt::max('id') + 1), 6, '0', STR_PAD_LEFT),
                'received_at' => $data['received_at'],
                'status' => 'posted',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($validatedItems as $item) {
                $grnItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'warehouse_location_id' => $item['warehouse_location_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['unit_cost'] !== null ? ($item['quantity'] * $item['unit_cost']) : null,
                    'qc_status' => $item['qc_status'],
                    'remarks' => $item['remarks'] ?? null,
                ]);

                if ($grnItem->qc_status === 'approved' && $grnItem->purchase_order_item_id) {
                    $poItem = $grnItem->purchaseOrderItem;
                    $poItem->received_quantity = (float) $poItem->received_quantity + (float) $item['quantity'];
                    $poItem->save();
                }

                if ($grnItem->qc_status === 'approved' && $item['stock_tracked']) {
                    $entry = StockEntry::create([
                        'purchase_bill_id' => $receipt->purchase_bill_id,
                        'goods_receipt_id' => $receipt->id,
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_location_id' => $grnItem->warehouse_location_id,
                        'product_id' => $grnItem->product_id,
                        'batch_id' => $grnItem->batch_id,
                        'quantity' => $item['quantity'],
                        'status' => 'available',
                    ]);

                    StockMovement::recordFor(
                        $entry,
                        'goods-receipt',
                        (float) $item['quantity'],
                        'Posted from GRN ' . $receipt->grn_number
                    );
                }
            }

            if ($purchaseOrder) {
                $purchaseOrder->refresh()->load('items');
                $this->recalculatePurchaseOrderStatus($purchaseOrder);
            }
        });

        return redirect()
            ->route('admin.goods-receipts.show', $receipt)
            ->with('status', 'Goods receipt posted and stock updated.');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->ensureWarehouseAccess((int) $goodsReceipt->warehouse_id);

        $goodsReceipt->load([
            'supplier',
            'warehouse',
            'purchaseOrder',
            'purchaseBill',
            'creator',
            'items.product',
            'items.batch',
            'items.location',
            'items.purchaseOrderItem',
        ]);

        return view('admin.goods_receipts.show', [
            'receipt' => $goodsReceipt,
            'reversalBlockers' => $this->reversalBlockers($goodsReceipt),
        ]);
    }

    public function reverse(GoodsReceipt $goodsReceipt)
    {
        $this->ensureWarehouseAccess((int) $goodsReceipt->warehouse_id);

        if ($goodsReceipt->status !== 'posted') {
            return redirect()
                ->route('admin.goods-receipts.show', $goodsReceipt)
                ->withErrors(['receipt' => 'Only posted goods receipts can be reversed.']);
        }

        $goodsReceipt->loadMissing(['items.purchaseOrderItem', 'stockEntries.movements', 'warehouse']);

        $blockers = $this->reversalBlockers($goodsReceipt);
        if ($blockers !== []) {
            return redirect()
                ->route('admin.goods-receipts.show', $goodsReceipt)
                ->withErrors(['receipt' => 'This GRN cannot be reversed: ' . implode(' ', $blockers)]);
        }

        DB::transaction(function () use ($goodsReceipt) {
            $goodsReceipt->loadMissing(['items.purchaseOrderItem', 'stockEntries']);

            foreach ($goodsReceipt->items as $item) {
                if ($item->qc_status === 'approved' && $item->purchaseOrderItem) {
                    $purchaseOrderItem = $item->purchaseOrderItem;
                    $purchaseOrderItem->received_quantity = max(
                        0,
                        (float) $purchaseOrderItem->received_quantity - (float) $item->quantity
                    );
                    $purchaseOrderItem->save();
                }
            }

            foreach ($goodsReceipt->stockEntries as $stockEntry) {
                if ((float) $stockEntry->quantity > 0) {
                    StockMovement::recordFor(
                        $stockEntry,
                        'goods-receipt-reversal',
                        (float) $stockEntry->quantity * -1,
                        'Reversed from GRN ' . $goodsReceipt->grn_number
                    );
                }

                $stockEntry->quantity = 0;
                $stockEntry->save();
            }

            $goodsReceipt->status = 'cancelled';
            $goodsReceipt->notes = trim((string) ($goodsReceipt->notes ? $goodsReceipt->notes . "\n" : '') . 'Reversed on ' . now()->format('Y-m-d H:i'));
            $goodsReceipt->save();

            if ($goodsReceipt->purchaseOrder) {
                $goodsReceipt->purchaseOrder->refresh()->load('items');
                $this->recalculatePurchaseOrderStatus($goodsReceipt->purchaseOrder);
            }
        });

        return redirect()
            ->route('admin.goods-receipts.index')
            ->with('status', 'Goods receipt reversed and stock rolled back.');
    }

    protected function ensureWarehouseAccess(int $warehouseId): void
    {
        if (! auth()->user()?->canAccessWarehouse($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }

    protected function validateReceiptItems(array $data, ?PurchaseOrder $purchaseOrder): array
    {
        $purchaseOrderItems = $purchaseOrder
            ? $purchaseOrder->items->keyBy('id')
            : collect();

        $productIds = collect($data['items'])
            ->pluck('product_id')
            ->filter()
            ->values()
            ->all();

        if ($purchaseOrder) {
            $productIds = array_values(array_unique(array_merge(
                $productIds,
                $purchaseOrder->items->pluck('product_id')->filter()->all()
            )));
        }

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $locations = WarehouseLocation::whereIn(
            'id',
            collect($data['items'])->pluck('warehouse_location_id')->filter()->all()
        )->get()->keyBy('id');

        $validatedItems = [];

        foreach ($data['items'] as $index => $item) {
            $lineKey = 'items.' . $index;
            $poItem = null;

            if (! empty($item['purchase_order_item_id'])) {
                if (! $purchaseOrder) {
                    throw ValidationException::withMessages([
                        $lineKey . '.purchase_order_item_id' => 'Purchase order items can only be used when a purchase order is selected.',
                    ]);
                }

                $poItem = $purchaseOrderItems->get((int) $item['purchase_order_item_id']);
                if (! $poItem instanceof PurchaseOrderItem) {
                    throw ValidationException::withMessages([
                        $lineKey . '.purchase_order_item_id' => 'Selected purchase order item does not belong to the chosen purchase order.',
                    ]);
                }
            }

            $productId = $item['product_id'] ?? $poItem?->product_id;
            $product = $productId ? $products->get((int) $productId) : null;

            if ($poItem && (int) $productId !== (int) $poItem->product_id) {
                throw ValidationException::withMessages([
                    $lineKey . '.product_id' => 'Receipt line product must match the selected purchase order item.',
                ]);
            }

            if ($item['qc_status'] === 'approved') {
                if (! $product) {
                    throw ValidationException::withMessages([
                        $lineKey . '.product_id' => 'Approved receipt lines must reference a valid product.',
                    ]);
                }

                if (! $product->isStockTracked()) {
                    throw ValidationException::withMessages([
                        $lineKey . '.product_id' => 'Non-stock products cannot be posted through goods receipts.',
                    ]);
                }

                if ($poItem) {
                    $remainingQty = max(
                        (float) $poItem->quantity - (float) $poItem->received_quantity,
                        0
                    );

                    if ((float) $item['quantity'] > $remainingQty) {
                        throw ValidationException::withMessages([
                            $lineKey . '.quantity' => 'Approved quantity exceeds the remaining open quantity on the purchase order line.',
                        ]);
                    }
                }
            }

            if (! empty($item['warehouse_location_id'])) {
                $location = $locations->get((int) $item['warehouse_location_id']);
                if (! $location || (int) $location->warehouse_id !== (int) $data['warehouse_id']) {
                    throw ValidationException::withMessages([
                        $lineKey . '.warehouse_location_id' => 'Selected warehouse location does not belong to the chosen warehouse.',
                    ]);
                }
            }

            $validatedItems[] = [
                'purchase_order_item_id' => $poItem?->id,
                'product_id' => $product?->id,
                'batch_id' => $item['batch_id'] ?? null,
                'warehouse_location_id' => $item['warehouse_location_id'] ?? null,
                'quantity' => (float) $item['quantity'],
                'unit_cost' => isset($item['unit_cost']) ? (float) $item['unit_cost'] : null,
                'qc_status' => $item['qc_status'],
                'remarks' => $item['remarks'] ?? null,
                'stock_tracked' => $product?->isStockTracked() ?? false,
            ];
        }

        return $validatedItems;
    }

    protected function recalculatePurchaseOrderStatus(PurchaseOrder $purchaseOrder): void
    {
        $allReceived = $purchaseOrder->items->isNotEmpty()
            && $purchaseOrder->items->every(function (PurchaseOrderItem $item) {
                return (float) $item->received_quantity >= (float) $item->quantity;
            });

        $hasAnyReceived = $purchaseOrder->items->contains(function (PurchaseOrderItem $item) {
            return (float) $item->received_quantity > 0;
        });

        if ($allReceived) {
            $purchaseOrder->status = 'received';
        } elseif ($hasAnyReceived) {
            $purchaseOrder->status = 'partial_received';
        } else {
            $purchaseOrder->status = 'approved';
        }

        $purchaseOrder->save();
    }

    protected function reversalBlockers(GoodsReceipt $goodsReceipt): array
    {
        $goodsReceipt->loadMissing(['stockEntries.product', 'stockEntries.movements']);

        if ($goodsReceipt->status !== 'posted') {
            return ['its status is not posted'];
        }

        $blockers = [];

        foreach ($goodsReceipt->stockEntries as $entry) {
            $productName = $entry->product?->name ?? ('product #' . $entry->product_id);

            if ($entry->status !== 'available') {
                $blockers[] = $productName . ' is no longer in available stock.';
                continue;
            }

            if (! is_null($entry->order_id)) {
                $blockers[] = $productName . ' is linked to an order reservation.';
                continue;
            }

            $nonReceiptMovements = $entry->movements
                ->reject(fn ($movement) => in_array($movement->type, ['goods-receipt', 'goods-receipt-reversal'], true));

            if ($nonReceiptMovements->isNotEmpty()) {
                $blockers[] = $productName . ' already has stock movements recorded.';
                continue;
            }
        }

        return array_values(array_unique($blockers));
    }
}
