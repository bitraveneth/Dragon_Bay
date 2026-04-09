@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create Purchase Order</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Create PO for RM/PM procurement.</p>
        </div>
        <a href="{{ route('admin.purchase-orders.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Back</a>
    </div>

    <form action="{{ route('admin.purchase-orders.store') }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                <select name="supplier_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Order date</label>
                <input type="date" name="order_date" required value="{{ old('order_date', now()->toDateString()) }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Expected date</label>
                <input type="date" name="expected_date" value="{{ old('expected_date') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div class="sm:col-span-3">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">PO Items</h3>
                <button type="button" id="add-po-item" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Add line</button>
            </div>
            <div id="po-items" class="space-y-3"></div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">Save PO</button>
        </div>
    </form>
</div>

<script>
(() => {
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'label' => $p->sku . ' - ' . $p->name])->values());
    const container = document.getElementById('po-items');
    const addBtn = document.getElementById('add-po-item');
    let idx = 0;

    const productOptions = ['<option value="">None</option>'].concat(
        products.map(p => `<option value="${p.id}">${p.label}</option>`)
    ).join('');

    function addRow() {
        const div = document.createElement('div');
        div.className = 'grid grid-cols-1 gap-3 rounded-xl border border-gray-200 p-3 sm:grid-cols-4 dark:border-gray-700';
        div.innerHTML = `
            <input type="text" name="items[${idx}][description]" required placeholder="Description" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <select name="items[${idx}][product_id]" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">${productOptions}</select>
            <input type="number" name="items[${idx}][quantity]" min="0.01" step="0.01" required value="1" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <div class="flex gap-2">
                <input type="number" name="items[${idx}][unit_price]" min="0" step="0.01" placeholder="Unit price" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <button type="button" class="rounded-lg border border-red-300 px-2 text-xs text-red-600" data-remove>Del</button>
            </div>
        `;
        div.querySelector('[data-remove]').addEventListener('click', () => div.remove());
        container.appendChild(div);
        idx++;
    }

    addBtn.addEventListener('click', addRow);
    addRow();
})();
</script>
@endsection
