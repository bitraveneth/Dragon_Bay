<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxClassController extends Controller
{
    public function index()
    {
        $taxClasses = TaxClass::orderBy('name')->paginate(10);
        return view('admin.tax.index', compact('taxClasses'));
    }

    public function edit(TaxClass $taxClass)
    {
        return view('admin.tax.edit', compact('taxClass'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        TaxClass::create($data);
        return back()->with('status', 'Tax class created.');
    }

    public function update(Request $request, TaxClass $taxClass)
    {
        $data = $this->validated($request, $taxClass);

        $taxClass->update($data);

        return redirect()->route('admin.tax-classes.index')->with('status', 'Tax class updated.');
    }

    public function destroy(TaxClass $taxClass)
    {
        if ($taxClass->products()->exists()) {
            return redirect()->route('admin.tax-classes.index')
                ->with('error', 'Tax class is linked to products and cannot be deleted.');
        }

        $taxClass->delete();

        return redirect()->route('admin.tax-classes.index')->with('status', 'Tax class deleted.');
    }

    protected function validated(Request $request, ?TaxClass $taxClass = null): array
    {
        $taxClassId = $taxClass?->id;
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'hsn_code' => $this->normalizedNullableString($request->input('hsn_code')),
            'local_tax_code' => $this->normalizedNullableString($request->input('local_tax_code')),
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tax_classes', 'name')->ignore($taxClassId),
            ],
            'hsn_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tax_classes', 'hsn_code')->ignore($taxClassId),
            ],
            'local_tax_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('tax_classes', 'local_tax_code')->ignore($taxClassId),
            ],
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        foreach (['name', 'hsn_code', 'local_tax_code'] as $field) {
            $value = array_key_exists($field, $data) ? trim((string) $data[$field]) : null;
            $data[$field] = $value === '' ? null : $value;
        }

        return $data;
    }

    protected function normalizedNullableString($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
