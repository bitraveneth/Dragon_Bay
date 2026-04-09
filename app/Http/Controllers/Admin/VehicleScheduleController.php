<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\Delivery;
use App\Models\Vehicle;
use App\Models\VehicleSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VehicleScheduleController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $schedules = VehicleSchedule::with('vehicle', 'route')
            ->whereDate('scheduled_date', $date)
            ->orderBy('vehicle_id')
            ->get();

        $deliveries = Delivery::with(['vehicle', 'route', 'order.agent'])
            ->where(function ($query) use ($date) {
                $query->whereHas('order', function ($orderQuery) use ($date) {
                    $orderQuery->whereDate('delivery_date', $date);
                })->orWhere(function ($legacyQuery) use ($date) {
                    $legacyQuery->whereDate('created_at', $date)
                        ->whereHas('order', function ($orderQuery) {
                            $orderQuery->whereNull('delivery_date');
                        });
                });
            })
            ->orderBy('vehicle_id')
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();

        $vehicles = Vehicle::where('is_active', true)->orderBy('name')->get();
        $routes = DeliveryRoute::orderBy('name')->get();

        return view('admin.deliveries.schedule', compact('schedules', 'vehicles', 'routes', 'date', 'deliveries'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'scheduled_date' => 'required|date',
            'driver' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $data = $this->normalizeRouteVehicleSelection($data);

        $exists = VehicleSchedule::where('vehicle_id', $data['vehicle_id'])
            ->whereDate('scheduled_date', $data['scheduled_date'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'This vehicle is already scheduled for the selected date.',
            ]);
        }

        VehicleSchedule::create($data);

        return redirect()->route('admin.vehicle-schedule.index', ['date' => $data['scheduled_date']])
            ->with('status', 'Vehicle schedule saved.');
    }

    protected function normalizeRouteVehicleSelection(array $data): array
    {
        $vehicleId = $data['vehicle_id'] ?? null;

        if (! empty($data['route_id'])) {
            $route = DeliveryRoute::findOrFail($data['route_id']);

            if ($route->vehicle_id) {
                if ($vehicleId && (int) $vehicleId !== (int) $route->vehicle_id) {
                    throw ValidationException::withMessages([
                        'vehicle_id' => 'Selected vehicle must match the route default vehicle.',
                    ]);
                }

                $vehicleId = (int) $route->vehicle_id;
            }
        }

        if (! $vehicleId) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Select a vehicle or choose a route with a default vehicle.',
            ]);
        }

        $data['vehicle_id'] = $vehicleId;

        return $data;
    }
}
