<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackagingConversion;
use App\Models\PackagingType;
use Illuminate\Http\Request;

class PackagingTypeController extends Controller
{
    public function index()
    {
        $packagingTypes = PackagingType::orderBy('name')->paginate(10);
        $conversions = PackagingConversion::with(['fromType', 'toType'])
            ->orderBy('from_packaging_type_id')
            ->paginate(6, ['*'], 'conversions_page');

        return view('admin.packaging.index', compact('packagingTypes', 'conversions'));
    }

    public function edit(PackagingType $packagingType)
    {
        return view('admin.packaging.edit', compact('packagingType'));
    }

    public function update(Request $request, PackagingType $packagingType)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:packaging_types,name,' . $packagingType->id,
            'unit' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $packagingType->update($data);

        return redirect()->route('admin.packaging.index')->with('status', 'Packaging type updated.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:packaging_types,name',
            'unit' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        PackagingType::create($data);
        return back()->with('status', 'Packaging type saved.');
    }

    public function destroy(PackagingType $packagingType)
    {
        if ($packagingType->products()->exists()) {
            return redirect()->route('admin.packaging.index')
                ->with('error', 'Packaging type is linked to products and cannot be deleted.');
        }

        if (
            PackagingConversion::where('from_packaging_type_id', $packagingType->id)->exists()
            || PackagingConversion::where('to_packaging_type_id', $packagingType->id)->exists()
        ) {
            return redirect()->route('admin.packaging.index')
                ->with('error', 'Packaging type is used in conversions and cannot be deleted.');
        }

        $packagingType->delete();

        return redirect()->route('admin.packaging.index')->with('status', 'Packaging type deleted.');
    }
}
