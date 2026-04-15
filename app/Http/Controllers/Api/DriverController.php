<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\SystemAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DriverController extends Controller
{
    protected function requireEmployee(Request $request)
    {
        $user = $request->user();

        if (! $user->employee) {
            abort(403, 'Employee access required.');
        }

        return $user->employee;
    }

    protected function canAccessAllDeliveries($user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'warehouse_officer']);
    }

    protected function authorizedVehicleIds($user, $employee): array
    {
        if ($this->canAccessAllDeliveries($user)) {
            return Vehicle::query()->pluck('id')->all();
        }

        $candidates = collect([
            $user->email,
            $user->name,
            $employee->name,
            $employee->work_email,
        ])->filter()->map(function ($value) {
            return mb_strtolower(trim((string) $value));
        })->unique()->values();

        if ($candidates->isEmpty()) {
            return [];
        }

        return Vehicle::query()
            ->whereNotNull('driver')
            ->get()
            ->filter(function (Vehicle $vehicle) use ($candidates) {
                return $candidates->contains(mb_strtolower(trim((string) $vehicle->driver)));
            })
            ->pluck('id')
            ->values()
            ->all();
    }

    protected function ensureDeliveryAccess($user, $employee, Delivery $delivery): void
    {
        if ($this->canAccessAllDeliveries($user)) {
            return;
        }

        $authorizedVehicleIds = $this->authorizedVehicleIds($user, $employee);
        if (! in_array($delivery->vehicle_id, $authorizedVehicleIds, true)) {
            abort(404);
        }
    }

    public function deliveries(Request $request)
    {
        $user = $request->user();
        $employee = $this->requireEmployee($request);

        $date = $request->query('date')
            ? now()->parse($request->query('date'))
            : now();

        $vehicleId = $request->query('vehicle_id');
        $authorizedVehicleIds = $this->authorizedVehicleIds($user, $employee);

        if (! $this->canAccessAllDeliveries($user) && empty($authorizedVehicleIds)) {
            return response()->json(Delivery::query()->whereRaw('1 = 0')->paginate(20));
        }

        if ($vehicleId && ! $this->canAccessAllDeliveries($user) && ! in_array((int) $vehicleId, $authorizedVehicleIds, true)) {
            abort(403, 'You are not assigned to this vehicle.');
        }

        $query = Delivery::with(['order.agent', 'route', 'vehicle'])
            ->whereDate('created_at', $date->toDateString());

        if (! $this->canAccessAllDeliveries($user)) {
            $query->whereIn('vehicle_id', $authorizedVehicleIds);
        }

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $deliveries = $query->orderBy('sequence')->orderBy('id')->paginate(20);

        return response()->json($deliveries);
    }

    public function show(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        $employee = $this->requireEmployee($request);
        $this->ensureDeliveryAccess($user, $employee, $delivery);

        $delivery->loadMissing('order.items.product.packagingType', 'route', 'vehicle', 'order.agent', 'pod', 'items');

        return response()->json($delivery);
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        $employee = $this->requireEmployee($request);
        $this->ensureDeliveryAccess($user, $employee, $delivery);

        $data = $request->validate([
            'status' => 'required|in:scheduled,in_transit,delivered,exception',
            'exception_notes' => 'nullable|string',
        ]);

        $originalStatus = $delivery->status;
        $delivery->status = $data['status'];
        if (! empty($data['exception_notes'])) {
            $delivery->exception_notes = $data['exception_notes'];
        }
        $delivery->save();

        if ($delivery->status === 'delivered') {
            $this->syncDeliveryItemsFromOrder($delivery);
            $delivery->pod()->updateOrCreate([], [
                'signed_by' => $employee->name ?? $user->name,
                'delivered_at' => now(),
            ]);
        }

        if ($originalStatus !== $delivery->status) {
            $this->notifyDeliveryStatusChanged($delivery);
        }

        return response()->json($delivery);
    }

    public function uploadPod(Request $request, Delivery $delivery)
    {
        $user = $request->user();
        $employee = $this->requireEmployee($request);
        $this->ensureDeliveryAccess($user, $employee, $delivery);

        $data = $request->validate([
            'pod_photo' => 'required|image|max:8192',
            'signed_by' => 'nullable|string|max:255',
            'receiver_name' => 'nullable|string|max:255',
            'receiver_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'delivered_at' => 'nullable|date',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'exception_notes' => 'nullable|string',
        ]);

        $path = $request->file('pod_photo')->store('deliveries', 'public');

        $originalStatus = $delivery->status;
        $delivery->pod_photo = $path;

        if (! empty($data['exception_notes'])) {
            $delivery->status = 'exception';
            $delivery->exception_notes = $data['exception_notes'];
        } else {
            $delivery->status = 'delivered';
        }

        $delivery->save();

        $this->syncDeliveryItemsFromOrder($delivery);

        $delivery->pod()->updateOrCreate([], [
            'signed_by' => $data['signed_by'] ?? ($employee->name ?? $user->name),
            'signature_path' => $path,
            'receiver_name' => $data['receiver_name'] ?? null,
            'receiver_phone' => $data['receiver_phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'delivered_at' => $data['delivered_at'] ?? now(),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);

        if ($originalStatus !== $delivery->status) {
            $this->notifyDeliveryStatusChanged($delivery);
        }

        return response()->json($delivery);
    }

    protected function syncDeliveryItemsFromOrder(Delivery $delivery): void
    {
        $delivery->loadMissing('order.items');
        if (! $delivery->order) {
            return;
        }

        if ($delivery->items()->exists()) {
            return;
        }

        foreach ($delivery->order->items as $item) {
            $qty = (float) $item->quantity;
            $delivery->items()->create([
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'qty_dispatched' => $qty,
                'qty_delivered' => $qty,
                'qty_short' => 0,
                'qty_damaged' => 0,
            ]);
        }
    }

    public function vehicleLoad(Request $request)
    {
        $user = $request->user();
        $employee = $this->requireEmployee($request);

        $date = $request->query('date')
            ? now()->parse($request->query('date'))
            : now();

        $vehicleId = $request->query('vehicle_id');

        if (! $vehicleId) {
            return response()->json([
                'message' => 'vehicle_id query parameter is required',
            ], 422);
        }

        $vehicle = Vehicle::findOrFail($vehicleId);

        if (! $this->canAccessAllDeliveries($user)) {
            $authorizedVehicleIds = $this->authorizedVehicleIds($user, $employee);
            if (! in_array((int) $vehicleId, $authorizedVehicleIds, true)) {
                abort(403, 'You are not assigned to this vehicle.');
            }
        }

        $deliveries = Delivery::with('order.items')
            ->whereDate('created_at', $date->toDateString())
            ->where('vehicle_id', $vehicleId)
            ->get();

        $crateLoad = 0;

        foreach ($deliveries as $delivery) {
            foreach ($delivery->order->items as $item) {
                /** @var OrderItem $item */
                $crateLoad += (int) ceil($item->quantity / 12);
            }
        }

        $capacity = $vehicle->capacity_crates ?? null;
        $utilization = $capacity && $capacity > 0
            ? round(($crateLoad / $capacity) * 100, 2)
            : null;

        return response()->json([
            'date' => $date->toDateString(),
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'capacity_crates' => $vehicle->capacity_crates,
            ],
            'planned_crates' => $crateLoad,
            'utilization_percent' => $utilization,
        ]);
    }

    protected function notifyDeliveryStatusChanged(Delivery $delivery): void
    {
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
