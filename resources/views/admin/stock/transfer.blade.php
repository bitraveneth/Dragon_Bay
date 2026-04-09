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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Stock Transfer
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Move available stock between warehouses
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-8 py-6 dark:border-gray-800 dark:from-gray-900/50 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7H8m8 4H8m8 4h-4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Transfer Details</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Select source stock, destination warehouse, and quantity</p>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form action="{{ route('admin.stock.transfers.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- Source Stock Selection -->
                    <div class="space-y-2">
                        <label for="entry_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Source Stock <span class="text-error-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Material stock entries are listed first, followed by finished products.
                        </p>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <select id="entry_id" 
                                    name="entry_id" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select stock entry</option>
                                
                                @if(isset($materialEntries) && $materialEntries->isNotEmpty())
                                    <optgroup label="📦 Material Stock" class="font-semibold text-gray-700 dark:text-gray-300">
                                        @foreach($materialEntries as $entry)
                                            @php
                                                $availableQty = $entry->quantity;
                                                $productName = $entry->product->name ?? 'Unknown product';
                                                $productUom = $entry->product->uom ?? 'units';
                                                $warehouseName = $entry->warehouse->name ?? 'Unknown warehouse';
                                                $batchCode = $entry->batch->batch_code ?? null;
                                            @endphp
                                            <option value="{{ $entry->id }}" data-quantity="{{ $availableQty }}" {{ old('entry_id') == $entry->id ? 'selected' : '' }}>
                                                {{ $warehouseName }} · {{ $productName }}
                                                · {{ number_format($availableQty, 0) }} {{ $productUom }}
                                                @if($batchCode)
                                                    · Batch: {{ $batchCode }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                @if(isset($finishedEntries) && $finishedEntries->isNotEmpty())
                                    <optgroup label="✅ Finished Products" class="font-semibold text-gray-700 dark:text-gray-300">
                                        @foreach($finishedEntries as $entry)
                                            @php
                                                $availableQty = $entry->quantity;
                                                $productName = $entry->product->name ?? 'Unknown product';
                                                $productUom = $entry->product->uom ?? 'units';
                                                $warehouseName = $entry->warehouse->name ?? 'Unknown warehouse';
                                                $batchCode = $entry->batch->batch_code ?? null;
                                            @endphp
                                            <option value="{{ $entry->id }}" data-quantity="{{ $availableQty }}" {{ old('entry_id') == $entry->id ? 'selected' : '' }}>
                                                {{ $warehouseName }} · {{ $productName }}
                                                @if($batchCode)
                                                    · Batch {{ $batchCode }}
                                                @endif
                                                · {{ number_format($availableQty, 0) }} {{ $productUom }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div id="selected-stock-info" class="hidden mt-2 p-3 rounded-lg bg-brand-50 text-xs text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                            <!-- Dynamically updated with selected stock info -->
                        </div>
                        @error('entry_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Destination Warehouse -->
                    <div class="space-y-2">
                        <label for="destination_warehouse_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Destination Warehouse <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                            </div>
                            <select id="destination_warehouse_id" 
                                    name="destination_warehouse_id" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select destination warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('destination_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} · {{ $warehouse->type ?? 'Depot' }}
                                        @if($warehouse->code)
                                            ({{ $warehouse->code }})
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
                        @error('destination_warehouse_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="destination_warehouse_location_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Destination Location
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21a8.966 8.966 0 01-5.657-2A8.966 8.966 0 013 12a9 9 0 1118 0 8.966 8.966 0 01-3.343 7A8.966 8.966 0 0112 21zm0 0v-6m0 0a3 3 0 100-6 3 3 0 000 6z" />
                                </svg>
                            </div>
                            <select id="destination_warehouse_location_id"
                                    name="destination_warehouse_location_id"
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select destination location</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div id="destination-location-help" class="text-xs text-gray-500 dark:text-gray-400">
                            Select a warehouse first to see available locations.
                        </div>
                        @error('destination_warehouse_location_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantity -->
                    <div class="space-y-2">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantity to Transfer <span class="text-error-500">*</span>
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
                                   value="{{ old('quantity') }}"
                                   step="0.01" 
                                   min="0.01"
                                   placeholder="0.00"
                                   required
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <div id="quantity-limit" class="text-xs text-gray-500 dark:text-gray-400">
                            <!-- Dynamically updated with available quantity -->
                        </div>
                        @error('quantity')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="space-y-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Transfer Notes
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
                                   placeholder="e.g. Rebalancing stock, Transfer for order #12345"
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Optional: Add a reason or reference for this transfer</p>
                        @error('notes')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Transfer Summary Preview -->
                    <div class="mt-6 rounded-xl bg-gradient-to-r from-gray-50 to-white p-5 dark:from-gray-800/30 dark:to-gray-900 border border-gray-100 dark:border-gray-800">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Transfer Summary
                        </h4>
                        <div id="transfer-summary" class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            <p>Select source stock and destination warehouse to see transfer details.</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                        <a href="{{ route('admin.inventory.index') }}" 
                           class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Transfer Stock
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @php
        $locationPayload = ($locations ?? collect())
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'warehouse_id' => $location->warehouse_id,
                    'code' => $location->code,
                ];
            })
            ->values();
    @endphp

    const entrySelect = document.getElementById('entry_id');
    const destWarehouseSelect = document.getElementById('destination_warehouse_id');
    const destLocationSelect = document.getElementById('destination_warehouse_location_id');
    const destinationLocationHelp = document.getElementById('destination-location-help');
    const quantityInput = document.getElementById('quantity');
    const selectedStockInfo = document.getElementById('selected-stock-info');
    const quantityLimit = document.getElementById('quantity-limit');
    const transferSummary = document.getElementById('transfer-summary');
    const locations = @json($locationPayload);
    const oldLocationId = @json(old('destination_warehouse_location_id'));

    function updateDestinationLocations() {
        const warehouseId = parseInt(destWarehouseSelect.value || 0, 10);
        const matchingLocations = locations.filter(location => location.warehouse_id === warehouseId);

        destLocationSelect.innerHTML = '<option value="">Select destination location</option>';

        if (! warehouseId) {
            destLocationSelect.disabled = true;
            destinationLocationHelp.textContent = 'Select a warehouse first to see available locations.';
            return;
        }

        if (matchingLocations.length === 0) {
            destLocationSelect.disabled = true;
            destinationLocationHelp.textContent = 'This warehouse has no configured locations. Stock will be placed without a bin location.';
            return;
        }

        matchingLocations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.code;
            if (String(oldLocationId || '') === String(location.id)) {
                option.selected = true;
            }
            destLocationSelect.appendChild(option);
        });

        destLocationSelect.disabled = false;
        destinationLocationHelp.textContent = 'Choose the destination bin or rack for this transfer.';
    }

    function updateStockInfo() {
        const selectedOption = entrySelect.options[entrySelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const availableQty = parseFloat(selectedOption.dataset.quantity || 0);
            const optionText = selectedOption.textContent;
            
            // Parse source warehouse and product from option text
            const parts = optionText.split('·').map(p => p.trim());
            const sourceWarehouse = parts[0] || 'Unknown';
            const productName = parts[1] || 'Unknown';
            
            // Show selected stock info
            selectedStockInfo.classList.remove('hidden');
            selectedStockInfo.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-medium">Selected:</span>
                    <span>${sourceWarehouse}</span>
                    <span class="text-gray-400">→</span>
                    <span class="font-medium">${productName}</span>
                    <span class="ml-2 inline-flex items-center rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                        Available: ${availableQty.toFixed(2)}
                    </span>
                </div>
            `;
            
            // Update quantity limit info
            quantityLimit.innerHTML = `Available quantity: <span class="font-semibold text-brand-600 dark:text-brand-400">${availableQty.toFixed(2)}</span> units`;
            quantityInput.max = availableQty;
            
            // Update transfer summary
            updateTransferSummary();
        } else {
            selectedStockInfo.classList.add('hidden');
            quantityLimit.innerHTML = '';
            quantityInput.max = '';
        }
    }

    function updateTransferSummary() {
        const selectedOption = entrySelect.options[entrySelect.selectedIndex];
        const destOption = destWarehouseSelect.options[destWarehouseSelect.selectedIndex];
        const destLocationOption = destLocationSelect.options[destLocationSelect.selectedIndex];
        const quantity = parseFloat(quantityInput.value) || 0;
        
        if (selectedOption && selectedOption.value && destOption && destOption.value) {
            const availableQty = parseFloat(selectedOption.dataset.quantity || 0);
            const sourceText = selectedOption.textContent.split('·').map(p => p.trim());
            const sourceWarehouse = sourceText[0] || 'Unknown';
            const productName = sourceText[1] || 'Unknown';
            const destWarehouse = destOption.textContent.split('·').map(p => p.trim())[0] || 'Unknown';
            
            let summaryHtml = `
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Source:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${sourceWarehouse}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Destination:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${destWarehouse}</span>
                    </div>
            `;

            if (destLocationOption && destLocationOption.value) {
                summaryHtml += `
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Destination Location:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${destLocationOption.textContent.trim()}</span>
                    </div>
                `;
            }

            summaryHtml += `
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Product:</span>
                        <span class="font-medium text-gray-900 dark:text-white">${productName}</span>
                    </div>
            `;
            
            if (quantity > 0) {
                const percentage = ((quantity / availableQty) * 100).toFixed(1);
                summaryHtml += `
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Transfer Quantity:</span>
                            <span class="font-bold text-brand-600 dark:text-brand-400">${quantity.toFixed(2)}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400">of available:</span>
                            <span class="text-gray-700 dark:text-gray-300">${availableQty.toFixed(2)} (${percentage}%)</span>
                        </div>
                    </div>
                `;
            }
            
            summaryHtml += `</div>`;
            transferSummary.innerHTML = summaryHtml;
        } else {
            transferSummary.innerHTML = '<p class="text-gray-500 dark:text-gray-400">Select source stock and destination warehouse to see transfer details.</p>';
        }
    }

    // Event listeners
    entrySelect.addEventListener('change', updateStockInfo);
    destWarehouseSelect.addEventListener('change', function () {
        updateDestinationLocations();
        updateTransferSummary();
    });
    destLocationSelect.addEventListener('change', updateTransferSummary);
    quantityInput.addEventListener('input', updateTransferSummary);
    
    // Initial update if values are pre-selected
    updateDestinationLocations();
    if (entrySelect.value) {
        updateStockInfo();
    } else {
        updateTransferSummary();
    }
});
</script>
@endpush
@endsection
