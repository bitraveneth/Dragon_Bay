@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Record Customer Return
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Capture a return against an order and add stock back into a warehouse
                    </p>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.returns.customer.index') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Returns
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header with return icon -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-brand-50 to-white px-8 py-6 dark:border-gray-800 dark:from-brand-950/30 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Return Details</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">Select the order, product, and quantity being returned</p>
                </div>
            </div>
            
            <!-- Info Banner -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-brand-100/50 p-4 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">
                    <span class="font-semibold">Note:</span> This will add the returned quantity back to the selected warehouse inventory.
                </p>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.returns.customer.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order Selection -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="order_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Order <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                            </div>
                            <select id="order_id" 
                                    name="order_id" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select order</option>
                                @foreach($orders as $order)
                                    <option value="{{ $order->id }}" 
                                            data-agent="{{ $order->agent->name ?? '' }}"
                                            data-date="{{ $order->created_at->format('Y-m-d') }}"
                                            {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                        #{{ $order->id }} – {{ $order->agent->name ?? 'No agent' }} ({{ ucfirst($order->order_type) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div id="selected-order-info" class="hidden mt-2 p-3 rounded-lg bg-brand-50 text-xs text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                            <!-- Dynamically updated with selected order info -->
                        </div>
                        @error('order_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Selection -->
                    <div class="space-y-2">
                        <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Product <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <select id="product_id" 
                                    name="product_id" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-sku="{{ $product->sku }}"
                                            data-uom="{{ $product->uom ?? 'units' }}"
                                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} @if($product->sku)({{ $product->sku }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('product_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Warehouse Selection -->
                    <div class="space-y-2">
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Warehouse <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                            </div>
                            <select id="warehouse_id" 
                                    name="warehouse_id" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" 
                                            data-code="{{ $warehouse->code ?? '' }}"
                                            {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} @if($warehouse->code)({{ $warehouse->code }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('warehouse_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="batch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Batch
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                </svg>
                            </div>
                            <select id="batch_id"
                                    name="batch_id"
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Auto-detect batch</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div id="batch-hint" class="text-xs text-gray-500 dark:text-gray-400">
                            If the delivery used a single batch it will be selected automatically.
                        </div>
                        @error('batch_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantity -->
                    <div class="space-y-2">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantity <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                </svg>
                            </div>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   step="0.01" 
                                   min="0.01"
                                   value="{{ old('quantity') }}"
                                   placeholder="0.00"
                                   required
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <div id="quantity-hint" class="text-xs text-gray-500 dark:text-gray-400">
                            Enter the quantity being returned
                        </div>
                        @error('quantity')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="notes" 
                                   name="notes" 
                                   value="{{ old('notes') }}"
                                   placeholder="e.g. Damaged packaging, Wrong item, Customer dissatisfaction"
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Optional: Add reason or condition of returned item</p>
                        @error('notes')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Return Summary Preview -->
                <div id="return-summary" class="mt-6 rounded-xl bg-gradient-to-r from-brand-50 to-white p-5 dark:from-brand-950/30 dark:to-gray-900 border border-brand-100 dark:border-brand-800 hidden">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Return Summary
                    </h4>
                    <div id="summary-content" class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        <!-- Dynamically updated -->
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.returns.customer.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Record Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderSelect = document.getElementById('order_id');
    const productSelect = document.getElementById('product_id');
    const warehouseSelect = document.getElementById('warehouse_id');
    const batchSelect = document.getElementById('batch_id');
    const batchHint = document.getElementById('batch-hint');
    const quantityInput = document.getElementById('quantity');
    const notesInput = document.getElementById('notes');
    const selectedOrderInfo = document.getElementById('selected-order-info');
    const returnSummary = document.getElementById('return-summary');
    const summaryContent = document.getElementById('summary-content');
    const returnableBatches = @json($returnableBatches ?? []);
    const oldBatchId = @json(old('batch_id'));

    function updateBatchOptions() {
        const orderId = orderSelect.value;
        const productId = productSelect.value;
        const batchRows = returnableBatches?.[orderId]?.[productId] || [];

        batchSelect.innerHTML = '<option value="">Auto-detect batch</option>';

        batchRows.forEach(function(batch) {
            const option = document.createElement('option');
            option.value = batch.batch_id ?? '';
            option.textContent = batch.batch_id
                ? `${batch.label} (${Number(batch.quantity).toFixed(2)})`
                : `${batch.label} (${Number(batch.quantity).toFixed(2)})`;
            batchSelect.appendChild(option);
        });

        if (oldBatchId && batchRows.some(batch => String(batch.batch_id) === String(oldBatchId))) {
            batchSelect.value = String(oldBatchId);
        } else if (batchRows.length === 1 && batchRows[0].batch_id) {
            batchSelect.value = String(batchRows[0].batch_id);
        } else {
            batchSelect.value = '';
        }

        if (!orderId || !productId) {
            batchHint.textContent = 'Choose an order and product to see delivered batches.';
        } else if (batchRows.length === 0) {
            batchHint.textContent = 'No delivered batch was recorded for this order line. The return will be stored without a batch.';
        } else if (batchRows.length === 1 && batchRows[0].batch_id) {
            batchHint.textContent = 'One delivered batch found. It will be used automatically unless you change it.';
        } else if (batchRows.length > 1) {
            batchHint.textContent = 'Multiple delivered batches found. Select the batch being returned.';
        } else {
            batchHint.textContent = 'This order line was delivered without a recorded batch.';
        }
    }

    function updateOrderInfo() {
        const selectedOption = orderSelect.options[orderSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const agentName = selectedOption.dataset.agent || 'Unknown';
            const orderDate = selectedOption.dataset.date || 'Unknown';
            
            selectedOrderInfo.classList.remove('hidden');
            selectedOrderInfo.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-medium">Selected Order:</span>
                    <span class="text-brand-700 dark:text-brand-400">#${selectedOption.value}</span>
                    <span class="text-gray-400">·</span>
                    <span>${agentName}</span>
                    <span class="text-gray-400">·</span>
                    <span>${orderDate}</span>
                </div>
            `;
        } else {
            selectedOrderInfo.classList.add('hidden');
        }
        updateBatchOptions();
        updateSummary();
    }

    function updateSummary() {
        const orderOption = orderSelect.options[orderSelect.selectedIndex];
        const productOption = productSelect.options[productSelect.selectedIndex];
        const warehouseOption = warehouseSelect.options[warehouseSelect.selectedIndex];
        const batchOption = batchSelect.options[batchSelect.selectedIndex];
        const quantity = parseFloat(quantityInput.value) || 0;
        
        if (orderOption && orderOption.value && 
            productOption && productOption.value && 
            warehouseOption && warehouseOption.value && 
            quantity > 0) {
            
            const orderId = orderOption.value;
            const agentName = orderOption.dataset.agent || 'Unknown';
            const productName = productOption.text.split('(')[0].trim();
            const sku = productOption.dataset.sku || '';
            const warehouseName = warehouseOption.text.split('(')[0].trim();
            const uom = productOption.dataset.uom || 'units';
            
            returnSummary.classList.remove('hidden');
            summaryContent.innerHTML = `
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Order:</span>
                        <span class="font-medium text-gray-900 dark:text-white">#${orderId} · ${agentName}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Product:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${productName} ${sku ? `(SKU: ${sku})` : ''}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Return to Warehouse:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${warehouseName}</span>
                    </div>
                    ${batchOption && batchOption.value ? `
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Batch:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${batchOption.text}</span>
                    </div>
                    ` : ''}
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Quantity:</span>
                        <span class="font-bold text-brand-600 dark:text-brand-400">${quantity.toFixed(2)} ${uom}</span>
                    </div>
                    ${notesInput.value ? `
                    <div class="border-t border-brand-200 dark:border-brand-800 pt-2 mt-2">
                        <span class="text-gray-500 dark:text-gray-400">Notes:</span>
                        <span class="ml-2 text-gray-700 dark:text-gray-300">${notesInput.value}</span>
                    </div>
                    ` : ''}
                </div>
            `;
        } else {
            returnSummary.classList.add('hidden');
        }
    }

    // Event listeners
    orderSelect.addEventListener('change', updateOrderInfo);
    productSelect.addEventListener('change', function() {
        updateBatchOptions();
        updateSummary();
    });
    warehouseSelect.addEventListener('change', updateSummary);
    batchSelect.addEventListener('change', updateSummary);
    quantityInput.addEventListener('input', updateSummary);
    notesInput.addEventListener('input', updateSummary);
    
    // Initial update if values are pre-selected
    if (orderSelect.value) {
        updateOrderInfo();
    } else {
        updateBatchOptions();
    }
});
</script>
@endpush
@endsection
