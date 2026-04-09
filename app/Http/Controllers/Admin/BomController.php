<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\BomItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BomController extends Controller
{
    public function index()
    {
        $boms = BillOfMaterial::with('product', 'items.component')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.boms.index', compact('boms'));
    }

    public function create()
    {
        // Finished products that can be produced via BOMs
        $products = Product::where(function ($q) {
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $materials = Product::whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.boms.create', [
            'products' => $products,
            'materials' => $materials,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'material_unit_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.component_product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        $this->validateBomItems((int) $data['product_id'], $data['items']);

        $bom = BillOfMaterial::create([
            'product_id' => $data['product_id'],
            'name' => $data['name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $data['notes'] ?? null,
            'material_unit_cost' => $data['material_unit_cost'] ?? null,
        ]);

        // Ensure only one active BOM per product: deactivate older ones if this is active
        if ($bom->is_active) {
            BillOfMaterial::where('product_id', $bom->product_id)
                ->where('id', '!=', $bom->id)
                ->update(['is_active' => false]);
        }

        foreach ($data['items'] as $item) {
            $bom->items()->create([
                'component_product_id' => $item['component_product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'] ?? null,
                'unit' => $item['unit'] ?? null,
            ]);
        }

        return redirect()->route('admin.boms.index')->with('status', 'BOM created.');
    }

    public function edit(BillOfMaterial $bom)
    {
        $bom->load('items.component');
        $products = Product::where(function ($q) {
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $materials = Product::whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.boms.edit', compact('bom', 'products', 'materials'));
    }

    public function update(Request $request, BillOfMaterial $bom)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'material_unit_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.component_product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        $this->validateBomItems((int) $bom->product_id, $data['items']);

        $bom->update([
            'name' => $data['name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $data['notes'] ?? null,
            'material_unit_cost' => $data['material_unit_cost'] ?? null,
        ]);

        // Ensure only one active BOM per product: deactivate older ones if this is active
        if ($bom->is_active) {
            BillOfMaterial::where('product_id', $bom->product_id)
                ->where('id', '!=', $bom->id)
                ->update(['is_active' => false]);
        }

        $bom->items()->delete();
        foreach ($data['items'] as $item) {
            $bom->items()->create([
                'component_product_id' => $item['component_product_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'] ?? null,
                'unit' => $item['unit'] ?? null,
            ]);
        }

        return redirect()->route('admin.boms.index')->with('status', 'BOM updated.');
    }

    public function destroy(BillOfMaterial $bom)
    {
        $bom->delete();

        return redirect()->route('admin.boms.index')->with('status', 'BOM deleted.');
    }

    protected function validateBomItems(int $productId, array $items): void
    {
        $components = Product::whereIn('id', collect($items)->pluck('component_product_id')->all())
            ->get()
            ->keyBy('id');

        foreach ($items as $index => $item) {
            $componentId = (int) $item['component_product_id'];
            $component = $components->get($componentId);

            if ($componentId === $productId) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.component_product_id' => 'A BOM cannot use the finished product itself as a component.',
                ]);
            }

            if (! $component || ! in_array($component->product_type, ['raw', 'service', 'inhouse'], true)) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.component_product_id' => 'BOM components must be active material products.',
                ]);
            }

            if (! $component->is_active) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.component_product_id' => 'Inactive products cannot be used as BOM components.',
                ]);
            }
        }
    }
}
