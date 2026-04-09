@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-error-500 to-error-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-error-500 to-error-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-1.5-2.25h3M12 15.75h.007v.008H12v-.008zM12 21a9 9 0 100-18 9 9 0 000 18z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Stock Write-off
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Write off expired, wasted, or returned-to-supplier stock
                    </p>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.stock.movements') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Movements
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header with warning gradient -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-error-50 to-white px-8 py-6 dark:border-gray-800 dark:from-error-950/30 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-error-500 to-error-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-1.5-2.25h3M12 15.75h.007v.008H12v-.008zM12 21a9 9 0 100-18 9 9 0 000 18z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Write-off Details</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">Select stock to write off and provide a reason</p>
                </div>
            </div>
            
            <!-- Warning Banner -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-error-100/50 p-4 text-error-800 dark:bg-error-900/30 dark:text-error-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm">
                    <span class="font-semibold">Warning:</span> This action will permanently remove stock from inventory and cannot be undone.
                </p>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.stock.writeoff.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Stock Entry Selection -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="entry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Stock Entry <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-error-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <select id="entry_id" 
                                    name="entry_id" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-error-500 focus:ring-2 focus:ring-error-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select stock entry</option>
                                @foreach($entries as $entry)
                                    @php
                                        $availableQty = $entry->quantity;
                                        $hasBatch = isset($entry->batch) && $entry->batch;
                                    @endphp
                                    <option value="{{ $entry->id }}" 
                                            data-quantity="{{ $availableQty }}"
                                            data-warehouse="{{ $entry->warehouse->name }}"
                                            data-product="{{ $entry->product->name }}"
                                            data-uom="{{ $entry->product->uom ?? 'units' }}"
                                            {{ (isset($selectedEntryId) && (int) $selectedEntryId === $entry->id) ? 'selected' : '' }}>
                                        {{ $entry->product->name }} · 
                                        {{ $entry->warehouse->name }} · 
                                        {{ number_format($availableQty, 2) }} {{ $entry->product->uom ?? 'units' }}
                                        @if($hasBatch)
                                            · Batch: {{ $entry->batch->batch_code }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div id="selected-stock-info" class="hidden mt-2 p-3 rounded-lg bg-error-50 text-xs text-error-700 dark:bg-error-900/30 dark:text-error-400">
                            <!-- Dynamically updated with selected stock info -->
                        </div>
                        @error('entry_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantity to Write Off -->
                    <div class="space-y-2">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantity to Write Off <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-error-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                </svg>
                            </div>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   step="0.01" 
                                   min="0.01"
                                   placeholder="0.00"
                                   required
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-error-500 focus:ring-2 focus:ring-error-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <div id="quantity-limit" class="text-xs text-gray-500 dark:text-gray-400">
                            <!-- Dynamically updated with available quantity -->
                        </div>
                        @error('quantity')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="space-y-2">
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reason <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-error-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                            @php
                                $selectedReason = old('reason', $defaultReason ?? '');
                            @endphp
                            <select id="reason" 
                                    name="reason" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-error-500 focus:ring-2 focus:ring-error-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="expired" {{ $selectedReason === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="wasted" {{ $selectedReason === 'wasted' ? 'selected' : '' }}>Wasted / Damaged</option>
                                <option value="supplier-return" {{ $selectedReason === 'supplier-return' ? 'selected' : '' }}>Return to Supplier</option>
                                <option value="production-loss" {{ $selectedReason === 'production-loss' ? 'selected' : '' }}>Production Loss / Short Output</option>
                                <option value="other" {{ $selectedReason === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('reason')
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
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-error-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="notes" 
                                   name="notes" 
                                   value="{{ old('notes') }}"
                                   placeholder="e.g. Damaged during handling, Quality issues, Recall #12345"
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-error-500 focus:ring-2 focus:ring-error-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Provide additional context about this write-off</p>
                        @error('notes')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Write-off Summary Preview -->
                <div id="writeoff-summary" class="mt-6 rounded-xl bg-gradient-to-r from-error-50 to-white p-5 dark:from-error-950/30 dark:to-gray-900 border border-error-100 dark:border-error-800 hidden">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="h-4 w-4 text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Write-off Summary
                    </h4>
                    <div id="summary-content" class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        <!-- Dynamically updated -->
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.stock.movements') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-error-500 to-error-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-error-600 hover:to-error-700 focus:outline-none focus:ring-2 focus:ring-error-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Write Off Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const entrySelect = document.getElementById('entry_id');
    const quantityInput = document.getElementById('quantity');
    const reasonSelect = document.getElementById('reason');
    const selectedStockInfo = document.getElementById('selected-stock-info');
    const quantityLimit = document.getElementById('quantity-limit');
    const writeoffSummary = document.getElementById('writeoff-summary');
    const summaryContent = document.getElementById('summary-content');

    function updateStockInfo() {
        const selectedOption = entrySelect.options[entrySelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const availableQty = parseFloat(selectedOption.dataset.quantity || 0);
            const warehouseName = selectedOption.dataset.warehouse || 'Unknown';
            const productName = selectedOption.dataset.product || 'Unknown';
            const uom = selectedOption.dataset.uom || 'units';
            
            // Show selected stock info
            selectedStockInfo.classList.remove('hidden');
            selectedStockInfo.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-medium">Selected Stock:</span>
                    <span class="text-error-700 dark:text-error-400">${productName}</span>
                    <span class="text-gray-400">·</span>
                    <span>${warehouseName}</span>
                    <span class="ml-2 inline-flex items-center rounded-full bg-error-100 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-900/30 dark:text-error-400">
                        Available: ${availableQty.toFixed(2)} ${uom}
                    </span>
                </div>
            `;
            
            // Update quantity limit info
            quantityLimit.innerHTML = `Available quantity: <span class="font-semibold text-error-600 dark:text-error-400">${availableQty.toFixed(2)} ${uom}</span>`;
            quantityInput.max = availableQty;
            
            updateSummary();
        } else {
            selectedStockInfo.classList.add('hidden');
            quantityLimit.innerHTML = '';
            quantityInput.max = '';
            writeoffSummary.classList.add('hidden');
        }
    }

    function updateSummary() {
        const selectedOption = entrySelect.options[entrySelect.selectedIndex];
        const quantity = parseFloat(quantityInput.value) || 0;
        const reason = reasonSelect.options[reasonSelect.selectedIndex]?.text || 'Not selected';
        
        if (selectedOption && selectedOption.value && quantity > 0) {
            const availableQty = parseFloat(selectedOption.dataset.quantity || 0);
            const productName = selectedOption.dataset.product || 'Unknown';
            const warehouseName = selectedOption.dataset.warehouse || 'Unknown';
            const uom = selectedOption.dataset.uom || 'units';
            
            if (quantity <= availableQty) {
                writeoffSummary.classList.remove('hidden');
                summaryContent.innerHTML = `
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Product:</span>
                            <span class="font-medium text-gray-900 dark:text-white">${productName}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Source Warehouse:</span>
                            <span class="font-medium text-gray-900 dark:text-white">${warehouseName}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Write-off Quantity:</span>
                            <span class="font-bold text-error-600 dark:text-error-400">${quantity.toFixed(2)} ${uom}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Reason:</span>
                            <span class="font-medium text-gray-900 dark:text-white">${reason}</span>
                        </div>
                        <div class="border-t border-error-200 dark:border-error-800 pt-2 mt-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Remaining stock after write-off:</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">${(availableQty - quantity).toFixed(2)} ${uom}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                writeoffSummary.classList.remove('hidden');
                summaryContent.innerHTML = `
                    <div class="flex items-center gap-2 text-error-600 dark:text-error-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-medium">Quantity exceeds available stock!</span>
                    </div>
                `;
            }
        } else {
            writeoffSummary.classList.add('hidden');
        }
    }

    // Event listeners
    entrySelect.addEventListener('change', updateStockInfo);
    quantityInput.addEventListener('input', updateSummary);
    reasonSelect.addEventListener('change', updateSummary);
    
    // Initial update if entry is pre-selected
    if (entrySelect.value) {
        updateStockInfo();
    }
});
</script>
@endpush
@endsection