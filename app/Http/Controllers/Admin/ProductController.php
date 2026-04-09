<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentPriceList;
use App\Models\BomItem;
use App\Models\DeliveryItem;
use App\Models\GoodsReceiptItem;
use App\Models\InvoiceItem;
use App\Models\OrderItem;
use App\Models\Batch;
use App\Models\ProductionMaterialIssueItem;
use App\Models\StockEntry;
use App\Models\ProductionRun;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseOrderItem;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $search = request('q');

        $query = Product::with(['packagingType', 'taxClass'])
            ->latest();

        $products = $query
            ->where(function ($q) {
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->when($search, function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('sku', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('barcode', 'like', $term);
                });
            })
            ->paginate(12);
        $packagingCount = PackagingType::count();
        $taxClassCount = TaxClass::count();

        // Preserve search term when navigating pagination links.
        $products->appends(['q' => $search]);

        return view('admin.products.index', compact('products', 'packagingCount', 'taxClassCount', 'search'));
    }

    /**
     * List all materials (raw, service, in‑house) that are used in BOMs and costing.
     */
    public function materialsIndex()
    {
        $search = request('q');

        $materialsQuery = Product::whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->orderBy('name');

        if ($search) {
            $term = '%' . $search . '%';
            $materialsQuery->where(function ($q) use ($term) {
                $q->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('supplier_name', 'like', $term);
            });
        }

        $materials = $materialsQuery->paginate(20);
        $materials->appends(['q' => $search]);

        return view('admin.products.materials_index', compact('materials', 'search'));
    }

    public function create()
    {
        $packagingTypes = PackagingType::orderBy('name')->get();
        $taxClasses = TaxClass::orderBy('name')->get();

        $context = 'products';

        return view('admin.products.create', compact('packagingTypes', 'taxClasses', 'context'));
    }

    /**
     * Material create form – reuses the product create view but with a different context.
     */
    public function materialsCreate()
    {
        $packagingTypes = PackagingType::orderBy('name')->get();
        $taxClasses = TaxClass::orderBy('name')->get();
        $context = 'materials';

        return view('admin.products.create', compact('packagingTypes', 'taxClasses', 'context'));
    }

    public function store(Request $request)
    {
        $isMaterialsRoute = $request->routeIs('admin.materials.*');

        $data = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string',
            // When creating products from the main catalog screen we now focus on
            // sellable SKUs. The form silently posts "finished" as the type, but
            // we still allow other values for legacy records and API usage.
            'product_type' => 'nullable|in:finished,raw,service,inhouse',
            'description' => 'nullable|string',
            'size' => 'nullable|string',
            'uom' => 'nullable|string',
            'volume_ml' => 'nullable|numeric|min:0',
            'sku_code' => 'nullable|string',
            'packaging_type_id' => 'nullable|exists:packaging_types,id',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'mineral_source' => 'nullable|string',
            'ph' => 'nullable|numeric|min:0|max:14',
            'tds' => 'nullable|integer|min:0',
            'certifications' => 'nullable|string',
            'barcode' => 'nullable|string',
            'qr_code' => 'nullable|string',
            'image_path' => 'nullable|string',
            'base_price' => ($isMaterialsRoute ? 'nullable' : 'required') . '|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'supplier_name' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Default type to "finished" if the form did not explicitly send it
        $data['product_type'] = $data['product_type'] ?? 'finished';

        // Default standard_cost to base_price for finished SKUs if not provided
        if (! isset($data['standard_cost'])) {
            $data['standard_cost'] = $data['base_price'] ?? 0;
        }
        // For materials we treat missing base_price as 0 so later reports work
        if ($isMaterialsRoute && ! isset($data['base_price'])) {
            $data['base_price'] = 0;
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);

        if ($request->hasFile('product_image')) {
            $product->update(['image_path' => $request->file('product_image')->store('products', 'public')]);
        }

        if ($request->hasFile('qr_code_file')) {
            $product->update(['qr_code' => $request->file('qr_code_file')->store('qrcodes', 'public')]);
        }

        if ($isMaterialsRoute) {
            return redirect()
                ->route('admin.materials.index')
                ->with('status', 'Material added.');
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', 'Product added to catalog.');
    }

    public function edit(Product $product)
    {
        $packagingTypes = PackagingType::orderBy('name')->get();
        $taxClasses = TaxClass::orderBy('name')->get();
        $context = 'products';

        return view('admin.products.edit', compact('product', 'packagingTypes', 'taxClasses', 'context'));
    }

    public function materialsEdit(Product $product)
    {
        $packagingTypes = PackagingType::orderBy('name')->get();
        $taxClasses = TaxClass::orderBy('name')->get();
        $context = 'materials';

        return view('admin.products.edit', compact('product', 'packagingTypes', 'taxClasses', 'context'));
    }

    public function update(Request $request, Product $product)
    {
        $isMaterialsRoute = $request->routeIs('admin.materials.*');

        $data = $request->validate([
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'name' => 'required|string',
            'product_type' => 'nullable|in:finished,raw,service,inhouse',
            'description' => 'nullable|string',
            'size' => 'nullable|string',
            'uom' => 'nullable|string',
            'volume_ml' => 'nullable|numeric|min:0',
            'sku_code' => 'nullable|string',
            'packaging_type_id' => 'nullable|exists:packaging_types,id',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'mineral_source' => 'nullable|string',
            'ph' => 'nullable|numeric|min:0|max:14',
            'tds' => 'nullable|integer|min:0',
            'certifications' => 'nullable|string',
            'barcode' => 'nullable|string',
            'qr_code' => 'nullable|string',
            'image_path' => 'nullable|string',
            'base_price' => ($isMaterialsRoute ? 'nullable' : 'required') . '|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'supplier_name' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['product_type'] = $data['product_type'] ?? $product->product_type ?? 'finished';
        if (! isset($data['standard_cost'])) {
            $data['standard_cost'] = $data['base_price'] ?? 0;
        }
        if ($isMaterialsRoute && ! isset($data['base_price'])) {
            $data['base_price'] = 0;
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);

        if ($request->hasFile('product_image')) {
            $product->update(['image_path' => $request->file('product_image')->store('products', 'public')]);
        }

        if ($request->hasFile('qr_code_file')) {
            $product->update(['qr_code' => $request->file('qr_code_file')->store('qrcodes', 'public')]);
        }

        if ($isMaterialsRoute) {
            return redirect()
                ->route('admin.materials.index')
                ->with('status', 'Material updated.');
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', 'Product updated.');
    }

    public function show(Product $product)
    {
        $product->load(['packagingType', 'taxClass', 'batches']);
        return view('admin.products.show', compact('product'));
    }

    public function export()
    {
        $products = Product::with(['packagingType', 'taxClass'])->orderBy('sku')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_catalog.csv"',
        ];

        $callback = static function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'SKU',
                'Name',
                'Size',
                'Volume (ml)',
                'Packaging',
                'Tax class',
                'Tax rate',
                'Base price',
                'Barcode',
                'QR path',
            ]);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->size,
                    $product->volume_ml,
                    optional($product->packagingType)->name,
                    optional($product->taxClass)->name,
                    optional($product->taxClass)->rate,
                    $product->base_price,
                    $product->barcode,
                    $product->qr_code,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function priceList()
    {
        $search = request('q');

        $query = Product::withCount('agentPriceLists')
            ->where(function ($q) {
                // Only show sellable SKUs in the price list.
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->orderBy('sku');

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        $products = $query->paginate(20)->appends(['q' => $search]);

        return view('admin.products.price_list', compact('products'));
    }

    public function showPriceList(Product $product)
    {
        $product->load(['agentPriceLists.agent']);

        $agentPrices = $product->agentPriceLists
            ->sortBy(function ($row) {
                return $row->agent->name ?? '';
            });

        return view('admin.products.price_list_show', [
            'product' => $product,
            'agentPrices' => $agentPrices,
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        $isMaterialsRoute = $request->routeIs('admin.materials.*');

        $reasons = [];

        if (OrderItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'sales orders';
        }

        if (InvoiceItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'customer invoices';
        }

        if (AgentPriceList::where('product_id', $product->id)->exists()) {
            $reasons[] = 'agent price lists';
        }

        if (Batch::where('product_id', $product->id)->exists()) {
            $reasons[] = 'production batches';
        }

        if (ProductionRun::where('product_id', $product->id)->exists()) {
            $reasons[] = 'production runs';
        }

        if (StockEntry::where('product_id', $product->id)->exists()) {
            $reasons[] = 'stock entries';
        }

        if (BomItem::where('component_product_id', $product->id)->exists()) {
            $reasons[] = 'bills of material';
        }

        if (DeliveryItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'delivery records';
        }

        if (ProductionMaterialIssueItem::where('component_product_id', $product->id)->exists()) {
            $reasons[] = 'production material issues';
        }

        if (GoodsReceiptItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'goods receipts';
        }

        if (PurchaseBillItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'purchase bills';
        }

        if (PurchaseOrderItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'purchase orders';
        }

        if (! empty($reasons)) {
            $reasonText = implode(', ', $reasons);

            $label = $isMaterialsRoute ? 'Material' : 'Product';

            return redirect()->route($isMaterialsRoute ? 'admin.materials.index' : 'admin.products.index')
                ->with('status', $label . ' cannot be deleted because it is linked to: ' . $reasonText . '.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        if ($product->qr_code) {
            Storage::disk('public')->delete($product->qr_code);
        }

        $product->delete();

        if ($isMaterialsRoute) {
            return redirect()
                ->route('admin.materials.index')
                ->with('status', 'Material deleted.');
        }

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted.');
    }
}
