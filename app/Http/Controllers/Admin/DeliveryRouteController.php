<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DeliveryRouteController extends Controller
{
    public function index()
    {
        $routes = DeliveryRoute::with('vehicle')->orderBy('name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('name')->get();

        return view('admin.deliveries.routes', compact('routes', 'vehicles'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'zone' => $this->normalizedNullableString($request->input('zone')),
            'day' => $this->normalizedNullableString($request->input('day')),
            'driver' => $this->normalizedNullableString($request->input('driver')),
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'zone' => 'nullable|string|max:255',
            'day' => 'nullable|string|max:50',
            'vehicle_id' => [
                'nullable',
                Rule::exists('vehicles', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
            ],
            'driver' => 'nullable|string|max:255',
        ]);

        $this->ensureRouteDefinitionIsUnique($data);

        DeliveryRoute::create($data);

        return redirect()->route('admin.delivery-routes.index')->with('status', 'Route saved.');
    }

    protected function ensureRouteDefinitionIsUnique(array $data): void
    {
        $exists = DeliveryRoute::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower((string) $data['name'])])
            ->whereRaw('LOWER(COALESCE(zone, \'\')) = ?', [mb_strtolower((string) ($data['zone'] ?? ''))])
            ->whereRaw('LOWER(COALESCE(day, \'\')) = ?', [mb_strtolower((string) ($data['day'] ?? ''))])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'A delivery route with the same name, zone, and day already exists.',
            ]);
        }
    }

    protected function normalizedNullableString($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
