@extends('layouts.app')

@section('content')
@php
    $fieldClass = 'w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-500';
    $labelClass = 'mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
    $productOptionsData = $products
        ->map(fn ($product) => [
            'id' => $product->id,
            'label' => $product->sku . ' - ' . $product->name,
        ])
        ->values();

    $locationOptionsData = $locations
        ->map(fn ($location) => [
            'id' => $location->id,
            'label' => $location->code,
        ])
        ->values();

    $purchaseOrderOptionsData = $purchaseOrders
        ->map(function ($purchaseOrder) {
            return [
                'id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'items' => $purchaseOrder->items
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'description' => $item->description,
                            'quantity' => (float) $item->quantity,
                            'received_quantity' => (float) $item->received_quantity,
                            'unit_price' => $item->unit_price !== null ? (float) $item->unit_price : null,
                        ];
                    })
                    ->values(),
            ];
        })
        ->values();
@endphp
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/20">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V6a2.25 2.25 0 012.25-2.25z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12h7.5m-7.5 3h4.5" />
                </svg>
            </div>
            <div>
                <div class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    Inventory intake
                </div>
                <h1 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">Create GRN</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Receive supplier materials, attach them to a warehouse, and post stock in one step.</p>
            </div>
        </div>
        <a href="{{ route('admin.goods-receipts.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Back to GRN list</a>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.goods-receipts.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr),320px]">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Receipt details</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose the source document, supplier, warehouse, and receiving time.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label class="{{ $labelClass }}">Purchase Order</label>
                        <select id="po-select" name="purchase_order_id" class="{{ $fieldClass }}">
                            <option value="">No PO</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}" @selected(old('purchase_order_id', $selectedPo?->id) == $po->id)>{{ $po->number }} - {{ $po->supplier->name ?? 'Supplier' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Supplier</label>
                        <select id="supplier-select" name="supplier_id" required class="{{ $fieldClass }}">
                            <option value="">Select supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $selectedPo?->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Warehouse</label>
                        <select name="warehouse_id" required class="{{ $fieldClass }}">
                            <option value="">Select warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Purchase Bill</label>
                        <select name="purchase_bill_id" class="{{ $fieldClass }}">
                            <option value="">None</option>
                            @foreach($purchaseBills as $bill)
                                <option value="{{ $bill->id }}" @selected(old('purchase_bill_id') == $bill->id)>{{ $bill->number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Received At</label>
                        <input type="datetime-local" name="received_at" value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}" required class="{{ $fieldClass }}">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="{{ $labelClass }}">Notes</label>
                        <textarea name="notes" rows="3" class="{{ $fieldClass }}">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            <aside class="rounded-3xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-950">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Before posting</h2>
                <div class="mt-4 grid gap-3">
                    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Open purchase orders</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $purchaseOrders->count() }}</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Warehouse locations</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $locations->count() }}</div>
                    </div>
                </div>
                <ul class="mt-5 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-brand-500"></span>
                        Selecting a PO will auto-load only pending items from that purchase order.
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-brand-500"></span>
                        Line items can still be added manually if the receipt is not linked to a PO.
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-brand-500"></span>
                        Posting the GRN will create stock entries immediately for approved lines.
                    </li>
                </ul>
            </aside>
        </div>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">GRN items</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add received materials, quantities, unit cost, location, and QC status.</p>
                </div>
                <button type="button" id="add-grn-item" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Add line</button>
            </div>
            <div id="grn-items" class="space-y-3"></div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.goods-receipts.index') }}" class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
            <button type="submit" class="inline-flex items-center rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20">Post GRN</button>
        </div>
    </form>
</div>

<script>
(() => {
    const products = @json($productOptionsData);
    const locations = @json($locationOptionsData);
    const purchaseOrders = @json($purchaseOrderOptionsData);

    const productOptions = ['<option value="">None</option>'].concat(products.map(p => `<option value="${p.id}">${p.label}</option>`)).join('');
    const locationOptions = ['<option value="">None</option>'].concat(locations.map(l => `<option value="${l.id}">${l.label}</option>`)).join('');

    const container = document.getElementById('grn-items');
    const addBtn = document.getElementById('add-grn-item');
    const poSelect = document.getElementById('po-select');
    const supplierSelect = document.getElementById('supplier-select');
    let idx = 0;

    function addRow(row = null) {
        const quantity = row ? Math.max((row.quantity || 0) - (row.received_quantity || 0), 0) : 1;
        const div = document.createElement('div');
        div.className = 'rounded-2xl border border-gray-200 bg-gray-50/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/40';
        div.innerHTML = `
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Line item</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Received material and warehouse placement</div>
                </div>
                <button type="button" data-remove class="rounded-xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">Remove</button>
            </div>
            <input type="hidden" name="items[${idx}][purchase_order_item_id]" value="${row?.id || ''}">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Description</label>
                    <input type="text" name="items[${idx}][description]" value="${row?.description || ''}" placeholder="Description" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" disabled>
                </div>
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Product</label>
                    <select name="items[${idx}][product_id]" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">${productOptions}</select>
                </div>
                <div>
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quantity</label>
                    <input type="number" min="0.01" step="0.01" name="items[${idx}][quantity]" value="${quantity}" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" required>
                </div>
                <div>
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unit cost</label>
                    <input type="number" min="0" step="0.0001" name="items[${idx}][unit_cost]" value="${row?.unit_price ?? ''}" placeholder="Unit cost" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="xl:col-span-3">
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Warehouse location</label>
                    <select name="items[${idx}][warehouse_location_id]" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">${locationOptions}</select>
                </div>
                <div class="xl:col-span-2">
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">QC status</label>
                    <select name="items[${idx}][qc_status]" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="xl:col-span-6">
                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Remarks</label>
                    <input type="text" name="items[${idx}][remarks]" placeholder="Optional line note or QC remark" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
        `;

        const productSelect = div.querySelector(`select[name="items[${idx}][product_id]"]`);
        if (row?.product_id) productSelect.value = String(row.product_id);

        div.querySelector('[data-remove]').addEventListener('click', () => div.remove());
        container.appendChild(div);
        idx++;
    }

    function loadPoLines(poId) {
        container.innerHTML = '';
        idx = 0;

        if (!poId) {
            addRow();
            return;
        }

        const po = purchaseOrders.find(p => String(p.id) === String(poId));
        if (!po) {
            addRow();
            return;
        }

        if (po.supplier_id) supplierSelect.value = String(po.supplier_id);

        po.items.forEach(item => {
            const pendingQty = (item.quantity || 0) - (item.received_quantity || 0);
            if (pendingQty > 0) addRow(item);
        });

        if (!container.children.length) addRow();
    }

    poSelect.addEventListener('change', (e) => loadPoLines(e.target.value));
    addBtn.addEventListener('click', () => addRow());

    loadPoLines(poSelect.value);
})();
</script>
@endsection
