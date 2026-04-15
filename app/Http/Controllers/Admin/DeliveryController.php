<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\DeliveryRoute;
use App\Models\Order;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\SystemAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle', 'pod'])
            ->orderByDesc('created_at')
            ->paginate(12);
        return view('admin.deliveries.index', compact('deliveries'));
    }

    /**
     * Logistics‑focused Deliveries & POD view (Inventory menu).
     */
    public function podIndex()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle', 'pod'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('admin.deliveries.pod_index', compact('deliveries'));
    }

    public function packingIndex()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle', 'pod'])
            ->whereIn('status', ['scheduled', 'in_transit'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('admin.deliveries.packing_index', compact('deliveries'));
    }

    public function create()
    {
        $orders = Order::with('agent')
            ->where(function ($query) {
                $query->where(function ($orderQuery) {
                    $orderQuery->whereIn('status', ['picked', 'packed']);
                })->orWhere(function ($orderQuery) {
                    $orderQuery->where('order_type', 'return')
                        ->where('status', 'confirmed');
                });
            })
            ->whereDoesntHave('deliveries')
            ->get();
        $routes = DeliveryRoute::orderBy('name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('name')->get();
        return view('admin.deliveries.create', compact('orders', 'routes', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where(function ($query) {
                    $query->where(function ($orderQuery) {
                        $orderQuery->whereIn('status', ['picked', 'packed']);
                    })->orWhere(function ($orderQuery) {
                        $orderQuery->where('order_type', 'return')
                            ->where('status', 'confirmed');
                    });
                }),
                Rule::unique('deliveries', 'order_id'),
            ],
            'route_id' => 'nullable|exists:delivery_routes,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'status' => 'required|in:scheduled,in_transit,exception',
            'exception_notes' => 'nullable|string',
            'pod_photo' => 'nullable|image',
        ]);
        $data = $this->normalizeRouteVehicleSelection($data);

        if ($request->hasFile('pod_photo')) {
            $data['pod_photo'] = $request->file('pod_photo')->store('deliveries', 'public');
        }

        $delivery = Delivery::create($data);

        // Log a status event on the order timeline so users can see
        // when delivery scheduling happened in relation to picking/packing.
        $order = $delivery->order;
        if ($order) {
            \App\Models\OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'delivery_scheduled',
                'changed_at' => now(),
            ]);
        }

        return redirect()->route('admin.deliveries.pod-index')->with('status', 'Delivery scheduled.');
    }

    public function edit(Delivery $delivery)
    {
        $delivery->load('order.agent', 'order.items.product', 'route', 'vehicle', 'pod', 'items');
        $routes = DeliveryRoute::orderBy('name')->get();
        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->when($delivery->vehicle_id, function ($query) use ($delivery) {
                $query->orWhere('id', $delivery->vehicle_id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.deliveries.edit', compact('delivery', 'routes', 'vehicles'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $data = $request->validate([
            'route_id' => 'nullable|exists:delivery_routes,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'status' => 'required|in:scheduled,in_transit,delivered,exception',
            'sequence' => 'nullable|integer|min:1',
            'exception_notes' => 'nullable|string',
            'pod_photo' => 'nullable|image',
            'pod_signed_by' => 'nullable|string|max:255',
            'pod_receiver_name' => 'nullable|string|max:255',
            'pod_receiver_phone' => 'nullable|string|max:50',
            'pod_notes' => 'nullable|string',
            'pod_delivered_at' => 'nullable|date',
            'pod_latitude' => 'nullable|numeric|between:-90,90',
            'pod_longitude' => 'nullable|numeric|between:-180,180',
            'items' => 'nullable|array',
            'items.*.order_item_id' => 'required_with:items|exists:order_items,id',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.qty_dispatched' => 'nullable|numeric|min:0',
            'items.*.qty_delivered' => 'nullable|numeric|min:0',
            'items.*.qty_short' => 'nullable|numeric|min:0',
            'items.*.qty_damaged' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);
        $data = $this->normalizeRouteVehicleSelection($data, $delivery);

        $originalStatus = $delivery->status;

        if ($request->hasFile('pod_photo')) {
            $data['pod_photo'] = $request->file('pod_photo')->store('deliveries', 'public');
        }

        DB::transaction(function () use ($delivery, $data, $originalStatus) {
            $delivery->update([
                'route_id' => $data['route_id'],
                'vehicle_id' => $data['vehicle_id'],
                'status' => $data['status'],
                'sequence' => array_key_exists('sequence', $data) ? $data['sequence'] : $delivery->sequence,
                'exception_notes' => array_key_exists('exception_notes', $data) ? $data['exception_notes'] : $delivery->exception_notes,
                'pod_photo' => $data['pod_photo'] ?? $delivery->pod_photo,
            ]);

            $deliveryItems = $this->syncDeliveryItems($delivery, $data);
            $this->syncDeliveryPod($delivery, $data);

            $order = $delivery->order;

            // If the status has just changed to in_transit, mark the order as dispatched.
            if ($originalStatus !== 'in_transit'
                && $delivery->status === 'in_transit'
                && $order
                && $order->status !== 'dispatched') {

                $order->update(['status' => 'dispatched']);

                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'dispatched',
                    'changed_at' => now(),
                ]);
            }

            // If the status has just changed to delivered, update the order and adjust stock.
            if ($originalStatus !== 'delivered' && $delivery->status === 'delivered' && $order) {
                if ($order->status !== 'delivered') {
                    $order->update(['status' => 'delivered']);
                }

                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'delivered',
                    'changed_at' => now(),
                ]);

                $order = $delivery->order()->with('items')->first();

                if ($order->order_type !== 'return') {
                    $deliveryItemSummaries = DeliveryItem::summarizeForOrderItems($deliveryItems);

                    foreach ($order->items as $item) {
                        $line = $deliveryItemSummaries->get($item->id);
                        $toShip = $line
                            ? (float) $line['qty_dispatched']
                            : (float) $item->quantity;

                        if ($toShip <= 0) {
                            continue;
                        }

                        $reservedEntries = StockEntry::where('order_id', $order->id)
                            ->where('product_id', $item->product_id)
                            ->where('status', 'reserved')
                            ->orderBy('created_at')
                            ->lockForUpdate()
                            ->get();

                        foreach ($reservedEntries as $entry) {
                            if ($toShip <= 0) {
                                break;
                            }

                            $entryQty = (float) $entry->quantity;
                            if ($entryQty <= 0) {
                                continue;
                            }

                            $shipQty = min($toShip, $entryQty);
                            $remaining = $entryQty - $shipQty;

                            StockMovement::recordFor(
                                $entry,
                                'delivery',
                                $shipQty * -1,
                                'Delivered on order #' . $order->id,
                                $order->id
                            );

                            if ($remaining <= 0) {
                                $entry->quantity = 0;
                                $entry->status = 'sold';
                                $entry->save();
                            } else {
                                $entry->quantity = $remaining;
                                $entry->save();
                            }

                            $toShip -= $shipQty;
                        }

                        if ($toShip > 0.00001) {
                            throw ValidationException::withMessages([
                                'items' => ['Reserved stock could not be safely matched to this order for delivery.'],
                            ]);
                        }
                    }
                }

                app(FinanceController::class)->ensureInvoiceForOrder($order->fresh());
            }
        });

        if ($originalStatus !== $delivery->status) {
            $this->notifyRoles(
                ['super_admin', 'admin', 'sales_officer', 'delivery_coordinator', 'warehouse_officer'],
                [
                    'title' => 'Delivery status updated',
                    'message' => 'Delivery #' . $delivery->id . ' for order #'
                        . ($delivery->order_id ?? 'N/A') . ' is now '
                        . ucwords(str_replace('_', ' ', $delivery->status)) . '.',
                    'variant' => 'info',
                    'source' => 'Logistics',
                    'link' => route('admin.deliveries.edit', $delivery),
                    'context' => [
                        'delivery_id' => $delivery->id,
                        'order_id' => $delivery->order_id,
                        'status' => $delivery->status,
                    ],
                ]
            );
        }

        return back()->with('status', 'Delivery updated.');
    }

    protected function normalizeRouteVehicleSelection(array $data, ?Delivery $delivery = null): array
    {
        $routeId = array_key_exists('route_id', $data)
            ? $data['route_id']
            : $delivery?->route_id;
        $vehicleId = array_key_exists('vehicle_id', $data)
            ? $data['vehicle_id']
            : $delivery?->vehicle_id;

        if ($routeId) {
            $route = DeliveryRoute::findOrFail($routeId);

            if ($route->vehicle_id) {
                if ($vehicleId && (int) $vehicleId !== (int) $route->vehicle_id) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => 'Selected vehicle must match the route default vehicle.',
                    ]);
                }

                $vehicleId = (int) $route->vehicle_id;
            }
        }

        $data['route_id'] = $routeId ?: null;
        $data['vehicle_id'] = $vehicleId ?: null;

        return $data;
    }

    public function optimize(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        $date = $data['date'];

        $query = Delivery::with('order.agent')
            ->where(function ($builder) use ($date) {
                $builder->whereHas('order', function ($orderQuery) use ($date) {
                    $orderQuery->whereDate('delivery_date', $date);
                })->orWhere(function ($legacyQuery) use ($date) {
                    $legacyQuery->whereDate('created_at', $date)
                        ->whereHas('order', function ($orderQuery) {
                            $orderQuery->whereNull('delivery_date');
                        });
                });
            });

        if (!empty($data['vehicle_id'])) {
            $query->where('vehicle_id', $data['vehicle_id']);
        }

        $deliveries = $query->get();

        // Simple heuristic: sort by agent zone, then order id and assign sequence
        $sorted = $deliveries->sortBy(function (Delivery $d) {
            $zone = $d->order && $d->order->agent ? ($d->order->agent->zone ?? '') : '';
            return $zone . '|' . str_pad((string)$d->order_id, 6, '0', STR_PAD_LEFT);
        })->values();

        foreach ($sorted as $index => $delivery) {
            $delivery->sequence = $index + 1;
            $delivery->save();
        }

        return redirect()->route('admin.deliveries.index')
            ->with('status', 'Deliveries sequenced for ' . $date . '.');
    }

    public function destroy(Delivery $delivery)
    {
        if (
            $delivery->status !== 'scheduled'
            || $delivery->items()->exists()
            || $delivery->pod()->exists()
            || ! empty($delivery->pod_photo)
        ) {
            return redirect()
                ->route('admin.deliveries.index')
                ->withErrors([
                    'delivery' => 'This delivery has operational activity recorded and cannot be deleted.',
                ]);
        }

        if ($delivery->pod_photo) {
            Storage::disk('public')->delete($delivery->pod_photo);
        }

        $delivery->delete();

        return redirect()->route('admin.deliveries.index')->with('status', 'Delivery deleted.');
    }

    public function packingSlip(Delivery $delivery)
    {
        $delivery->load('order.items.product', 'route', 'vehicle', 'order.agent', 'pod', 'items.product');
        return view('admin.deliveries.packing_slip', compact('delivery'));
    }

    protected function syncDeliveryItems(Delivery $delivery, array $data)
    {
        $order = $delivery->order()->with('items')->first();
        $orderItems = collect($order?->items ?? [])->keyBy('id');

        $rows = collect($data['items'] ?? [])->filter(function ($row) {
            return isset($row['order_item_id'], $row['product_id']);
        });

        if ($rows->isEmpty()) {
            if (! array_key_exists('items', $data) && $delivery->items()->exists()) {
                return $delivery->items()->get();
            }

            $rows = collect($order?->items ?? [])->map(function ($item) {
                $qty = (float) $item->quantity;
                return [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'batch_id' => null,
                    'qty_dispatched' => $qty,
                    'qty_delivered' => $qty,
                    'qty_short' => 0,
                    'qty_damaged' => 0,
                    'notes' => null,
                ];
            });
        }

        $normalizedRows = $rows->map(function ($row) use ($orderItems) {
            $orderItem = $orderItems->get((int) $row['order_item_id']);
            if (! $orderItem) {
                throw ValidationException::withMessages([
                    'items' => ['Delivery items must belong to the selected order.'],
                ]);
            }

            if ((int) $orderItem->product_id !== (int) $row['product_id']) {
                throw ValidationException::withMessages([
                    'items' => ['Delivery item product must match its order line.'],
                ]);
            }

            $qtyDelivered = isset($row['qty_delivered']) ? (float) $row['qty_delivered'] : 0;
            $qtyShort = isset($row['qty_short']) ? (float) $row['qty_short'] : 0;
            $qtyDamaged = isset($row['qty_damaged']) ? (float) $row['qty_damaged'] : 0;
            $qtyDispatched = isset($row['qty_dispatched'])
                ? (float) $row['qty_dispatched']
                : ($qtyDelivered + $qtyShort + $qtyDamaged);

            if ($qtyDispatched <= 0 && ($qtyDelivered + $qtyShort + $qtyDamaged) > 0) {
                $qtyDispatched = $qtyDelivered + $qtyShort + $qtyDamaged;
            }

            $reportedTotal = $qtyDelivered + $qtyShort + $qtyDamaged;
            if ($reportedTotal > (float) $orderItem->quantity + 0.00001) {
                throw ValidationException::withMessages([
                    'items' => ['Delivery line quantities cannot exceed the original ordered quantity.'],
                ]);
            }

            if ($qtyDispatched + 0.00001 < $reportedTotal) {
                throw ValidationException::withMessages([
                    'items' => ['Dispatched quantity cannot be less than delivered, short, plus damaged quantity.'],
                ]);
            }

            return [
                'order_item_id' => (int) $row['order_item_id'],
                'product_id' => (int) $row['product_id'],
                'batch_id' => $row['batch_id'] ?? null,
                'qty_dispatched' => $qtyDispatched,
                'qty_delivered' => $qtyDelivered,
                'qty_short' => $qtyShort,
                'qty_damaged' => $qtyDamaged,
                'notes' => $row['notes'] ?? null,
                'reported_total' => $reportedTotal,
            ];
        })->values();

        foreach ($normalizedRows->groupBy('order_item_id') as $orderItemId => $groupedRows) {
            $orderItem = $orderItems->get((int) $orderItemId);
            $reportedTotal = (float) $groupedRows->sum('reported_total');

            if ($reportedTotal > (float) $orderItem->quantity + 0.00001) {
                throw ValidationException::withMessages([
                    'items' => ['Combined delivery quantities cannot exceed the original ordered quantity.'],
                ]);
            }
        }

        $delivery->items()->delete();

        foreach ($normalizedRows as $row) {
            $delivery->items()->create([
                'order_item_id' => $row['order_item_id'],
                'product_id' => $row['product_id'],
                'batch_id' => $row['batch_id'] ?? null,
                'qty_dispatched' => $row['qty_dispatched'],
                'qty_delivered' => $row['qty_delivered'],
                'qty_short' => $row['qty_short'],
                'qty_damaged' => $row['qty_damaged'],
                'notes' => $row['notes'] ?? null,
            ]);
        }

        return $delivery->items()->get();
    }

    protected function syncDeliveryPod(Delivery $delivery, array $data): void
    {
        $existingPod = $delivery->pod;
        $podPayload = [
            'signed_by' => array_key_exists('pod_signed_by', $data) ? $data['pod_signed_by'] : $existingPod?->signed_by,
            'signature_path' => $data['pod_photo'] ?? $existingPod?->signature_path,
            'receiver_name' => array_key_exists('pod_receiver_name', $data) ? $data['pod_receiver_name'] : $existingPod?->receiver_name,
            'receiver_phone' => array_key_exists('pod_receiver_phone', $data) ? $data['pod_receiver_phone'] : $existingPod?->receiver_phone,
            'notes' => array_key_exists('pod_notes', $data) ? $data['pod_notes'] : $existingPod?->notes,
            'delivered_at' => array_key_exists('pod_delivered_at', $data) ? $data['pod_delivered_at'] : $existingPod?->delivered_at,
            'latitude' => array_key_exists('pod_latitude', $data) ? $data['pod_latitude'] : $existingPod?->latitude,
            'longitude' => array_key_exists('pod_longitude', $data) ? $data['pod_longitude'] : $existingPod?->longitude,
        ];

        $shouldPersist = collect($podPayload)->filter(function ($value) {
            return $value !== null && $value !== '';
        })->isNotEmpty() || $delivery->status === 'delivered';

        if (! $shouldPersist) {
            return;
        }

        if (empty($podPayload['delivered_at']) && $delivery->status === 'delivered') {
            $podPayload['delivered_at'] = now();
        }

        $delivery->pod()->updateOrCreate([], $podPayload);
    }

    protected function notifyRoles(array $roles, array $payload): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasTable('users')) {
            return;
        }

        User::query()
            ->get()
            ->filter(fn (User $user) => $user->hasAnyRole($roles))
            ->each(fn (User $user) => $user->notify(new SystemAlertNotification($payload)));
    }
}
