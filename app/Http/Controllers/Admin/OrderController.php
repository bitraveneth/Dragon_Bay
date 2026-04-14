<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\AgentPriceList;
use App\Models\AgentCommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Invoice;
use App\Models\Delivery;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\StockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Admin\FinanceController;
use App\Support\CommissionCalculator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orderTypeFilter = $request->query('type', 'all');
        if (! in_array($orderTypeFilter, ['all', 'sales', 'return'], true)) {
            $orderTypeFilter = 'all';
        }

        $orders = Order::with('client', 'agent')
            ->when($orderTypeFilter === 'sales', function ($query) {
                $query->where('order_type', '!=', 'return');
            })
            ->when($orderTypeFilter === 'return', function ($query) {
                $query->where('order_type', 'return');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'orderTypeFilter'));
    }

    public function pickingOverview()
    {
        $orders = Order::with('client', 'agent')
            ->whereIn('status', ['confirmed', 'picked'])
            ->latest()
            ->paginate(10);

        return view('admin.orders.picking_overview', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['client', 'agent', 'items.product.taxClass', 'statusHistory']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['client', 'agent', 'items.product']);
        return view('admin.orders.edit', compact('order'));
    }

    public function pickingList(Order $order)
    {
        $order->load('client', 'agent', 'items.product', 'delivery');

        $lines = [];

        foreach ($order->items as $item) {
            if (!$item->product) {
                continue;
            }

            $remaining = $item->quantity;

            $entries = StockEntry::with('warehouse', 'batch')
                ->where('product_id', $item->product_id)
                ->where('status', 'available')
                ->get()
                ->sortBy(function ($entry) {
                    if ($entry->batch && $entry->batch->expiry_date) {
                        return $entry->batch->expiry_date;
                    }
                    if ($entry->batch && $entry->batch->production_date) {
                        return $entry->batch->production_date;
                    }
                    return $entry->created_at;
                });

            foreach ($entries as $entry) {
                if ($remaining <= 0) {
                    break;
                }

                $pickQty = min($remaining, (int) $entry->quantity);
                if ($pickQty <= 0) {
                    continue;
                }

                $lines[] = [
                    'sku' => $item->product->sku ?? '',
                    'name' => $item->product->name ?? '',
                    'quantity' => $pickQty,
                    'warehouse' => $entry->warehouse->name ?? null,
                    'batch' => $entry->batch->batch_code ?? null,
                ];

                $remaining -= $pickQty;
            }

            if ($remaining > 0) {
                $lines[] = [
                    'sku' => $item->product->sku ?? '',
                    'name' => $item->product->name ?? '',
                    'quantity' => $remaining,
                    'warehouse' => 'Unassigned (shortage)',
                    'batch' => null,
                ];
            }
        }

        return view('admin.orders.picking_list', compact('order', 'lines'));
    }

    public function create()
    {
        $clients = \App\Models\Client::where('is_active', true)->orderBy('name')->get();
        // Only sellable finished products should be available on the order form
        $products = Product::where(function ($q) {
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Simple availability hint per product (sum of all available stock across warehouses)
        $availability = StockEntry::selectRaw('product_id, SUM(quantity) as total')
            ->where('status', 'available')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $priceLists = AgentPriceList::all()
            ->groupBy('agent_id')
            ->map(function ($rows) {
                return $rows->pluck('price', 'product_id');
            });

        // Map client → agent so the view can look up price lists
        $clientAgentMap = \App\Models\Client::whereNotNull('agent_id')
            ->pluck('agent_id', 'id');

        return view('admin.orders.create', [
            'clients' => $clients,
            'clientAgentMap' => $clientAgentMap,
            'products' => $products,
            'availability' => $availability,
            'priceLists' => $priceLists,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'agent_id' => 'nullable|exists:agents,id',
            'order_type' => 'required|in:regular,bulk,sample,return',
            'agent_reference' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'delivery_contact_name' => 'nullable|string|max:255',
            'delivery_contact_phone' => 'nullable|string|max:50',
            'delivery_address' => 'nullable|string',
            'payment_mode' => 'nullable|in:cash,credit,bkash,bank_transfer',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if (($data['order_type'] ?? null) === 'bulk' && empty($data['payment_mode'])) {
            $data['payment_mode'] = 'credit';
        }

        $order = DB::transaction(function () use ($data) {
            $calculator = app(CommissionCalculator::class);
            $order = Order::create([
                'client_id' => $data['client_id'],
                'agent_id' => $data['agent_id'] ?? null,
                'order_type' => $data['order_type'],
                'agent_reference' => $data['agent_reference'] ?? null,
                'delivery_date' => $data['delivery_date'] ?? null,
                'delivery_contact_name' => $data['delivery_contact_name'] ?? null,
                'delivery_contact_phone' => $data['delivery_contact_phone'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'status' => 'confirmed',
                'total' => 0,
                'notes' => $data['notes'] ?? null,
                'payment_mode' => $data['payment_mode'] ?? null,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_at' => now(),
            ]);

            // Resolve the internal agent — either explicitly passed or derived from the client
            $resolvedAgentId = $data['agent_id']
                ?? \App\Models\Client::find($data['client_id'])?->agent_id;

            $agentPrices = $resolvedAgentId
                ? AgentPriceList::where('agent_id', $resolvedAgentId)->pluck('price', 'product_id')
                : collect();
            $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))
                ->get()
                ->keyBy('id');
            $commissionRules = $resolvedAgentId
                ? AgentCommissionRule::where('agent_id', $resolvedAgentId)->get()
                : collect();

            $total = 0;
            $commissionTotal = 0;

            foreach ($data['items'] as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                $unitPrice = $this->resolveUnitPrice(
                    $data['order_type'],
                    (float) ($agentPrices[$productId] ?? $item['unit_price'])
                );
                $lineTotal = $quantity * $unitPrice;
                $total += $lineTotal;

                $product = $products[$productId] ?? null;
                $sku = $product ? $product->sku : null;

                $lineCommission = $this->orderTypeSupportsCommission($data['order_type'])
                    ? $calculator->calculatePerOrderCommission($commissionRules, $sku, $data['order_type'], $lineTotal)
                    : 0.0;

                $commissionTotal += $lineCommission;
                $commissionRate = $lineTotal > 0 ? ($lineCommission / $lineTotal) * 100 : null;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'order_type' => $data['order_type'],
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $lineCommission,
                ]);

                if ($this->orderTypeRequiresReservation($data['order_type'])) {
                    $entries = StockEntry::where('product_id', $productId)
                        ->where('status', 'available')
                        ->orderBy('created_at')
                        ->lockForUpdate()
                        ->get();

                    $availableQty = (float) $entries->sum('quantity');
                    if ($availableQty < (float) $quantity) {
                        throw ValidationException::withMessages([
                            'items' => ['Insufficient available stock for product ' . ($product?->name ?? ('#' . $productId)) . '.'],
                        ]);
                    }

                    $toReserve = $quantity;
                    foreach ($entries as $entry) {
                        if ($toReserve <= 0) {
                            break;
                        }

                        $reserved = min((float) $entry->quantity, (float) $toReserve);
                        $entry->quantity = (float) $entry->quantity - $reserved;
                        if ((float) $entry->quantity <= 0.0) {
                            $entry->status = 'reserved';
                        }
                        $entry->save();

                        StockMovement::recordFor(
                            $entry,
                            'reservation-out',
                            $reserved * -1,
                            'Reserved for order #' . $order->id,
                            $order->id
                        );

                        $reservedEntry = StockEntry::create([
                            'order_id' => $order->id,
                            'warehouse_id' => $entry->warehouse_id,
                            'warehouse_location_id' => $entry->warehouse_location_id,
                            'product_id' => $entry->product_id,
                            'batch_id' => $entry->batch_id,
                            'quantity' => $reserved,
                            'status' => 'reserved',
                        ]);

                        StockMovement::recordFor(
                            $reservedEntry,
                            'reservation-in',
                            $reserved,
                            'Reserved for order #' . $order->id,
                            $order->id
                        );
                        $toReserve -= $reserved;
                    }
                }
            }

            $this->enforceCreditLimit($order, (float) $total, $data['payment_mode'] ?? null);

            $order->update([
                'total' => $total,
                'commission_total' => $commissionTotal,
                'is_credit_used' => $this->usesCredit($data['payment_mode'] ?? null, $data['order_type']),
            ]);

            return $order;
        });

        return redirect()->route('admin.orders.index')->with('status', 'Order created.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,confirmed,picked,packed,dispatched,delivered',
        ]);

        $newStatus = $data['status'];
        $oldStatus = $order->status;

        // Enforce simple forward-only workflow: draft -> confirmed -> packed -> dispatched -> delivered
        $workflow = ['draft','confirmed','picked','packed','dispatched','delivered'];
        $currentIndex = array_search($oldStatus, $workflow, true);
        $targetIndex = array_search($newStatus, $workflow, true);

        // Do not allow skipping more than one step forward or moving backwards
        if ($targetIndex === false || $currentIndex === false || $targetIndex > $currentIndex + 1 || $targetIndex < $currentIndex) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Status change not allowed by workflow.');
        }

        // Once delivered, lock status
        if ($oldStatus === 'delivered' && $newStatus !== 'delivered') {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Delivered orders cannot change status.');
        }

        $order->update(['status' => $newStatus]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'changed_at' => now(),
        ]);

        // Auto-create invoice when an order is marked as delivered and has no invoice yet
        if ($order->status === 'delivered') {
            app(FinanceController::class)->ensureInvoiceForOrder($order->fresh());
        }

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order status updated.');
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'agent_reference' => 'nullable|string|max:255',
            'delivery_contact_name' => 'nullable|string|max:255',
            'delivery_contact_phone' => 'nullable|string|max:50',
            'delivery_address' => 'nullable|string',
            'payment_mode' => 'nullable|in:cash,credit,bkash,bank_transfer',
        ]);

        $order->update($data);

        return redirect()->route('admin.orders.index')->with('status', 'Order updated.');
    }

    public function destroy(Order $order)
    {
        $hasInvoice = Invoice::where('order_id', $order->id)->exists();

        if ($hasInvoice) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Order has an invoice and cannot be deleted. Use credit notes instead.');
        }

        DB::transaction(function () use ($order) {
            // Release any reserved stock back to available before deleting the order.
            $reservedItems = $order->items()->with('product')->get();

            foreach ($reservedItems as $item) {
                $remaining = $item->quantity;

                // Find reserved entries for this product, newest first (reverse of reservation order)
                $reservedEntries = StockEntry::where('order_id', $order->id)
                    ->where('product_id', $item->product_id)
                    ->where('status', 'reserved')
                    ->orderByDesc('created_at')
                    ->lockForUpdate()
                    ->get();

                foreach ($reservedEntries as $reservedEntry) {
                    if ($remaining <= 0) {
                        break;
                    }

                    // How much of this entry we will release
                    $releaseQty = min((float) $remaining, (float) $reservedEntry->quantity);

                    if ($releaseQty <= 0) {
                        continue;
                    }

                    // Reduce reserved entry quantity
                    $reservedEntry->quantity -= $releaseQty;

                    if ($releaseQty > 0) {
                        StockMovement::recordFor(
                            $reservedEntry,
                            'reservation-release-out',
                            $releaseQty * -1,
                            'Released from deleted order #' . $order->id,
                            $order->id
                        );
                    }

                    if ($reservedEntry->quantity <= 0) {
                        $reservedEntry->quantity = 0;
                    }

                    $reservedEntry->save();

                    // Add released quantity back to available for same warehouse/batch
                    $availableEntry = StockEntry::firstOrCreate(
                        [
                            'warehouse_id' => $reservedEntry->warehouse_id,
                            'warehouse_location_id' => $reservedEntry->warehouse_location_id,
                            'product_id' => $reservedEntry->product_id,
                            'batch_id' => $reservedEntry->batch_id,
                            'status' => 'available',
                        ],
                        [
                            'quantity' => 0,
                        ]
                    );

                    $availableEntry->quantity += $releaseQty;
                    $availableEntry->save();

                    StockMovement::recordFor(
                        $availableEntry,
                        'reservation-release-in',
                        $releaseQty,
                        'Released from deleted order #' . $order->id,
                        $order->id
                    );

                    $remaining -= $releaseQty;
                }

                if ($remaining > 0.00001) {
                    throw ValidationException::withMessages([
                        'order' => ['Reserved stock could not be safely matched to this order. Review legacy reservations before deleting it.'],
                    ]);
                }
            }

            // Delete dependent records first to satisfy foreign key constraints
            Delivery::where('order_id', $order->id)->delete();
            StockMovement::where('order_id', $order->id)->delete();
            $order->items()->delete();
            $order->statusHistory()->delete();

            $order->delete();
        });

        return redirect()->route('admin.orders.index')->with('status', 'Order deleted.');
    }

    protected function resolveUnitPrice(string $orderType, float $unitPrice): float
    {
        if (in_array($orderType, ['sample', 'return'], true)) {
            return 0.0;
        }

        return round($unitPrice, 2);
    }

    protected function orderTypeRequiresReservation(string $orderType): bool
    {
        return $orderType !== 'return';
    }

    protected function orderTypeSupportsCommission(string $orderType): bool
    {
        return in_array($orderType, ['regular', 'bulk'], true);
    }

    protected function usesCredit(?string $paymentMode, string $orderType): bool
    {
        return $paymentMode === 'credit' && in_array($orderType, ['regular', 'bulk'], true);
    }

    protected function enforceCreditLimit(Order $order, float $proposedTotal, ?string $paymentMode): void
    {
        if (! $this->usesCredit($paymentMode, $order->order_type)) {
            return;
        }

        $client = \App\Models\Client::find($order->client_id);
        if (! $client) {
            return;
        }

        $creditLimit = (float) ($client->credit_limit ?? 0);
        if ($creditLimit <= 0) {
            return;
        }

        $existingOutstanding = Invoice::whereHas('order', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })->get()->sum(fn (Invoice $invoice) => (float) $invoice->outstanding);

        // Also check any advances linked to the client's assigned agent
        $agentId = $client->agent_id;
        $availableAdvances = $agentId
            ? AgentAdvance::where('agent_id', $agentId)
                ->whereIn('status', ['open', 'partial'])
                ->get()
                ->sum(fn (AgentAdvance $advance) => $advance->available_amount)
            : 0;

        $projectedExposure = max($existingOutstanding + $proposedTotal - $availableAdvances, 0);

        if ($projectedExposure > $creditLimit + 0.00001) {
            throw ValidationException::withMessages([
                'client_id' => ['This order exceeds the client credit limit after considering open advances/prepayments.'],
            ]);
        }
    }
}
