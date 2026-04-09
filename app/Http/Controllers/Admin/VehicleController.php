<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::orderBy('name')->paginate(15);
        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'type' => $this->normalizedNullableString($request->input('type')),
            'license_plate' => $this->normalizedNullableString($request->input('license_plate'), true),
            'driver' => $this->normalizedNullableString($request->input('driver')),
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles', 'name'),
            ],
            'type' => 'nullable|string|max:100',
            'license_plate' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('vehicles', 'license_plate'),
            ],
            'driver' => 'nullable|string|max:255',
            'capacity_crates' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]) + [
            'is_active' => $request->boolean('is_active', true),
        ];

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')->with('status', 'Vehicle added.');
    }

    protected function normalizedNullableString($value, bool $upper = false): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $upper ? mb_strtoupper($value) : $value;
    }
}
