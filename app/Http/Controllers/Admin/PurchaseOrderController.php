<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with(['supplier', 'items'])
            ->latest('order_date')
            ->paginate(15);

        return view('admin.purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::stockTracked()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.purchase_orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(function (QueryBuilder $query) {
                    $query->where('is_active', true)
                        ->where(function (QueryBuilder $productQuery) {
                            $productQuery->whereNull('product_type')
                                ->orWhereIn('product_type', ['finished', 'raw', 'inhouse']);
                        });
                }),
            ],
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $po = null;

        DB::transaction(function () use ($data, &$po) {
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'number' => 'PO-' . str_pad((string) (PurchaseOrder::max('id') + 1), 6, '0', STR_PAD_LEFT),
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : null;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice !== null ? ($qty * $unitPrice) : null,
                    'received_quantity' => 0,
                ]);
            }
        });

        return redirect()
            ->route('admin.purchase-orders.show', $po)
            ->with('status', 'Purchase order created. Approve it before GRN posting.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product', 'goodsReceipts']);

        return view('admin.purchase_orders.show', ['order' => $purchaseOrder]);
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('status', 'Only draft purchase orders can be approved.');
        }

        $purchaseOrder->update(['status' => 'approved']);

        return back()->with('status', 'Purchase order approved. You can now create GRN.');
    }
}
