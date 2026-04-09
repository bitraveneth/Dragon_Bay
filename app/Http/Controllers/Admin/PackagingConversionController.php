<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackagingConversion;
use Illuminate\Http\Request;

class PackagingConversionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_packaging_type_id' => 'required|exists:packaging_types,id',
            'to_packaging_type_id' => 'required|exists:packaging_types,id|different:from_packaging_type_id',
            'factor' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
        ]);

        PackagingConversion::updateOrCreate([
            'from_packaging_type_id' => $data['from_packaging_type_id'],
            'to_packaging_type_id' => $data['to_packaging_type_id'],
        ], $data);

        return back()->with('status', 'Conversion saved.');
    }

    public function destroy(PackagingConversion $conversion)
    {
        $conversion->delete();

        return back()->with('status', 'Conversion deleted.');
    }
}
