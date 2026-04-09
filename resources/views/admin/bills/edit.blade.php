@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Edit Purchase Bill
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    {{ $bill->number ?? 'Bill' }}
                </span>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Update supplier, dates, or line items for this bill.
                </p>
                @if($bill->status !== 'draft')
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        Status: {{ ucfirst($bill->status) }}
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bills.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Bills
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bill #{{ $bill->number ?? 'Draft' }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Created: {{ $bill->created_at->format('d M Y, h:i A') }} · 
                        Last updated: {{ $bill->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.bills.update', $bill) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Header Section -->
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bill Header</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Supplier and date information</p>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Supplier -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="supplier_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Supplier <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                            </div>
                            <select id="supplier_id" name="supplier_id" required
                                    class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $bill->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bill Date -->
                    <div>
                        <label for="bill_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Bill Date <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input type="date" 
                                   id="bill_date" 
                                   name="bill_date" 
                                   value="{{ old('bill_date', $bill->bill_date->format('Y-m-d')) }}" 
                                   required
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        @error('bill_date')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Due Date
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <input type="date" 
                                   id="due_date" 
                                   name="due_date" 
                                   value="{{ old('due_date', optional($bill->due_date)->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        @error('due_date')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="warehouse_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Receive into Warehouse
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4"/>
                                </svg>
                            </div>
                            <select id="warehouse_id" name="warehouse_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Default Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $bill->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('warehouse_id')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Line Items Section -->
            <div class="mt-8 space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Line Items</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Use one row per material or service on this supplier bill.
                            </p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300" id="item-count">
                            {{ $bill->items->count() }} {{ Str::plural('item', $bill->items->count()) }}
                        </span>
                    </div>
                </div>

                <!-- Bill Items Container -->
                <div id="bill-items" class="space-y-4">
                    @foreach($bill->items as $index => $item)
                        <div class="bill-item-row rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20">
                                        <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Line Item {{ $index + 1 }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Material or service</p>
                                    </div>
                                </div>
                                @if($index > 0)
                                    <button type="button" 
                                            class="bill-item-remove inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Remove
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                                <!-- Description (Full Width) -->
                                <div class="sm:col-span-2 lg:col-span-4">
                                    <label for="items[{{ $index }}][description]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Description <span class="text-error-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="items[{{ $index }}][description]" 
                                           value="{{ old('items.' . $index . '.description', $item->description) }}"
                                           required
                                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                </div>

                                <!-- Product (Optional) -->
                                <div>
                                    <label for="items[{{ $index }}][product_id]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Product (Optional)
                                    </label>
                                    <select name="items[{{ $index }}][product_id]"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">None</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-default-vat="{{ $product->taxClass->rate ?? 0 }}" {{ old('items.' . $index . '.product_id', $item->product_id) == $product->id ? 'selected' : '' }}>
                                                {{ $product->sku }} — {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label for="items[{{ $index }}][quantity]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Quantity <span class="text-error-500">*</span>
                                    </label>
                                    <input type="number" 
                                           name="items[{{ $index }}][quantity]" 
                                           min="1" 
                                           value="{{ old('items.' . $index . '.quantity', $item->quantity) }}" 
                                           required
                                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                </div>

                                <!-- Unit Price -->
                                <div>
                                    <label for="items[{{ $index }}][unit_price]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Unit Price <span class="text-error-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
                                        <input type="number" 
                                               name="items[{{ $index }}][unit_price]" 
                                               step="0.01" 
                                               min="0" 
                                               value="{{ old('items.' . $index . '.unit_price', $item->unit_price) }}" 
                                               required
                                               class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                    </div>
                                </div>

                                <div>
                                    <label for="items[{{ $index }}][vat_rate]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        VAT Rate %
                                    </label>
                                    <input type="number" 
                                           name="items[{{ $index }}][vat_rate]" 
                                           step="0.01" 
                                           min="0"
                                           max="100"
                                           value="{{ old('items.' . $index . '.vat_rate', $item->vat_rate ?? 0) }}" 
                                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                </div>

                                <!-- Line Total (Display Only) -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Line Total
                                    </label>
                                    <div class="line-total-display flex h-10 items-center rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        BDT {{ number_format($item->quantity * $item->unit_price, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Add Line Button -->
                <div class="flex items-center gap-3">
                    <button type="button" 
                            id="add-bill-item"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Line Item
                    </button>
                </div>
            </div>

            <!-- Bill Summary -->
            @php
                $subtotal = $bill->items->sum(fn($item) => $item->quantity * $item->unit_price);
            @endphp
            <div class="mt-8 rounded-xl bg-gray-50 p-5 dark:bg-gray-800/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Bill Summary</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Subtotal, VAT, and total amount</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Subtotal</p>
                        <p id="bill-subtotal" class="text-lg font-semibold text-gray-900 dark:text-white">BDT {{ number_format($subtotal, 2) }}</p>
                        <p id="bill-vat" class="text-xs text-gray-500 dark:text-gray-400">VAT: BDT {{ number_format($bill->vat_amount, 2) }}</p>
                        <p id="bill-total" class="text-sm font-medium text-gray-900 dark:text-white">Total: BDT {{ number_format($subtotal + $bill->vat_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.bills.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Bill
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('bill-items');
    const addBtn = document.getElementById('add-bill-item');
    const itemCount = document.getElementById('item-count');
    let index = {{ $bill->items->count() }};

    function updateItemCount() {
        const count = wrapper.children.length;
        if (itemCount) {
            itemCount.textContent = `${count} ${count === 1 ? 'item' : 'items'}`;
        }
    }

    function bindRemoveButtons() {
        wrapper.querySelectorAll('.bill-item-remove').forEach(btn => {
            btn.onclick = () => {
                const row = btn.closest('.bill-item-row');
                if (row) {
                    row.remove();
                    updateItemCount();
                    
                    // Renumber items
                    wrapper.querySelectorAll('.bill-item-row').forEach((row, idx) => {
                        const numberBadge = row.querySelector('.flex.h-8.w-8 span');
                        if (numberBadge) {
                            numberBadge.textContent = idx + 1;
                        }
                        const title = row.querySelector('h4');
                        if (title) {
                            title.textContent = `Line Item ${idx + 1}`;
                        }
                        
                        // Update input names
                        row.querySelectorAll('input[name^="items["], select[name^="items["]').forEach(input => {
                            const name = input.getAttribute('name');
                            const newName = name.replace(/items\[\d+\]/, `items[${idx}]`);
                            input.setAttribute('name', newName);
                            input.id = input.id?.replace(/_\d+_/, `_${idx}_`);
                        });
                    });
                }
            };
        });
    }

    function calculateLineTotal(row) {
        const qty = row.querySelector('input[name*="[quantity]"]')?.value || 0;
        const price = row.querySelector('input[name*="[unit_price]"]')?.value || 0;
        const total = parseFloat(qty) * parseFloat(price);
        const totalEl = row.querySelector('.line-total-display');
        if (totalEl) {
            totalEl.textContent = `BDT ${total.toFixed(2)}`;
        }
        return total;
    }

    function calculateBillTotal() {
        let subtotal = 0;
        let vatTotal = 0;
        wrapper.querySelectorAll('.bill-item-row').forEach(row => {
            subtotal += calculateLineTotal(row);
            const qty = parseFloat(row.querySelector('input[name*="[quantity]"]')?.value || 0);
            const price = parseFloat(row.querySelector('input[name*="[unit_price]"]')?.value || 0);
            const vatRate = parseFloat(row.querySelector('input[name*="[vat_rate]"]')?.value || 0);
            vatTotal += (qty * price) * (vatRate / 100);
        });

        document.getElementById('bill-subtotal').textContent = `BDT ${subtotal.toFixed(2)}`;
        document.getElementById('bill-vat').textContent = `VAT: BDT ${vatTotal.toFixed(2)}`;
        document.getElementById('bill-total').textContent = `Total: BDT ${(subtotal + vatTotal).toFixed(2)}`;
    }

    addBtn?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'bill-item-row rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50';
        
        row.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20">
                        <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">${index + 1}</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Line Item ${index + 1}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Material or service</p>
                    </div>
                </div>
                <button type="button" 
                        class="bill-item-remove inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Remove
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="sm:col-span-2 lg:col-span-4">
                    <label for="items[${index}][description]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description <span class="text-error-500">*</span>
                    </label>
                    <input type="text" 
                           name="items[${index}][description]" 
                           placeholder="e.g., PET Bottle 500ml, Packaging Service, etc."
                           required
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>

                <div>
                    <label for="items[${index}][product_id]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Product (Optional)
                    </label>
                    <select name="items[${index}][product_id]"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">None</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-default-vat="{{ $product->taxClass->rate ?? 0 }}">{{ $product->sku }} — {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="items[${index}][quantity]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Quantity <span class="text-error-500">*</span>
                    </label>
                    <input type="number" 
                           name="items[${index}][quantity]" 
                           min="1" 
                           value="1" 
                           required
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>

                <div>
                    <label for="items[${index}][unit_price]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Unit Price <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
                        <input type="number" 
                               name="items[${index}][unit_price]" 
                               step="0.01" 
                               min="0" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label for="items[${index}][vat_rate]" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        VAT Rate %
                    </label>
                    <input type="number" 
                           name="items[${index}][vat_rate]" 
                           step="0.01" 
                           min="0"
                           max="100"
                           value="0"
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                           placeholder="0.00">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Line Total
                    </label>
                    <div class="line-total-display flex h-10 items-center rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        BDT 0.00
                    </div>
                </div>
            </div>
        `;

        wrapper.appendChild(row);
        index += 1;
        bindRemoveButtons();
        updateItemCount();

        // Add event listeners for quantity and price changes
        row.querySelectorAll('input[name*="[quantity]"], input[name*="[unit_price]"], input[name*="[vat_rate]"]').forEach(input => {
            input.addEventListener('input', () => calculateBillTotal());
        });
        row.querySelector('select[name*="[product_id]"]')?.addEventListener('change', event => {
            const selected = event.target.selectedOptions[0];
            const vatInput = row.querySelector('input[name*="[vat_rate]"]');
            if (vatInput && selected && parseFloat(vatInput.value || 0) === 0) {
                vatInput.value = selected.dataset.defaultVat || 0;
                calculateBillTotal();
            }
        });
    });

    bindRemoveButtons();
    updateItemCount();

    // Initial calculation listeners
    document.querySelectorAll('input[name*="[quantity]"], input[name*="[unit_price]"], input[name*="[vat_rate]"]').forEach(input => {
        input.addEventListener('input', () => calculateBillTotal());
    });
    document.querySelectorAll('select[name*="[product_id]"]').forEach(select => {
        select.addEventListener('change', event => {
            const row = event.target.closest('.bill-item-row');
            const vatInput = row?.querySelector('input[name*="[vat_rate]"]');
            const selected = event.target.selectedOptions[0];
            if (vatInput && selected && parseFloat(vatInput.value || 0) === 0) {
                vatInput.value = selected.dataset.defaultVat || 0;
                calculateBillTotal();
            }
        });
    });
    calculateBillTotal();
});
</script>
@endpush
@endsection
