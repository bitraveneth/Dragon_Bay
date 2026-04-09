<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(15);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => $request->filled('email') ? strtolower(trim((string) $request->input('email'))) : null,
            'tax_id' => $request->filled('tax_id') ? trim((string) $request->input('tax_id')) : null,
        ]);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:255|unique:suppliers,tax_id',
        ]);

        Supplier::create($data);

        return redirect()->route('admin.suppliers.index')->with('status', 'Supplier created.');
    }
}
