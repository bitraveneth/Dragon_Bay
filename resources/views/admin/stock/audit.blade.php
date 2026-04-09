@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Stock Audit / Cycle Count
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Record counted quantities and variances per warehouse, product, and batch
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Message -->

    <!-- Record Count Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-8 py-6 dark:border-gray-800 dark:from-gray-900/50 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Record New Count</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Enter the physical count details below</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.stock.audit.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Warehouse -->
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
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
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

                    <!-- Product -->
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
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->sku }} — {{ $product->name }}
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

                    <!-- Batch (Optional) -->
                    <div class="space-y-2">
                        <label for="batch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Batch
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </div>
                            <select id="batch_id" 
                                    name="batch_id"
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Any batch</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                                        {{ $batch->batch_code }} @if($batch->product)({{ $batch->product->sku }})@endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Leave blank for non-batched stock</p>
                        @error('batch_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Counted Quantity -->
                    <div class="space-y-2">
                        <label for="counted_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Counted Quantity <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <input type="number" 
                                   id="counted_quantity" 
                                   name="counted_quantity" 
                                   step="0.01" 
                                   min="0"
                                   value="{{ old('counted_quantity') }}"
                                   placeholder="0.00"
                                   required
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <div id="system-quantity-info" class="text-xs text-gray-500 dark:text-gray-400">
                            <!-- Will be populated via AJAX -->
                        </div>
                        @error('counted_quantity')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes (Full Width) -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <div class="relative group">
                            <div class="absolute left-3 top-3.5 flex items-start pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3"
                                      placeholder="Add any observations, discrepancy reasons, or notes about this count..."
                                      class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('notes') }}</textarea>
                        </div>
                        @error('notes')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <button type="button" 
                            onclick="this.form.reset()"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Clear
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Record Count
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Audits Section -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                    <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25 0H21M12 18h9" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Audits</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Latest variances across warehouses</p>
                </div>
            </div>
        </div>

        @if($audits->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Audit History</h3>
                        <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $audits->total() }} total audits
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Warehouse</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Batch</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">System</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Counted</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Variance</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($audits as $audit)
                                @php
                                    $variance = $audit->variance;
                                    $varianceClass = $variance > 0 ? 'text-success-600 dark:text-success-400' : ($variance < 0 ? 'text-error-600 dark:text-error-500' : 'text-gray-600 dark:text-gray-400');
                                    $variancePrefix = $variance > 0 ? '+' : '';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $audit->created_at->format('d M Y') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $audit->warehouse->name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $audit->product->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                SKU: {{ $audit->product->sku }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($audit->batch)
                                            <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $audit->batch->batch_code }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($audit->system_quantity, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($audit->counted_quantity, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $varianceClass }}">
                                            {{ $variancePrefix }}{{ number_format($variance, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('admin.stock.audit.destroy', $audit) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this audit record? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-sm hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @if($audit->notes)
                                <tr class="bg-gray-50/50 dark:bg-gray-800/30">
                                    <td colspan="8" class="px-6 py-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium">Notes:</span> {{ $audit->notes }}
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if(method_exists($audits, 'links'))
                    <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                        {{ $audits->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white/50 p-12 text-center dark:border-gray-800 dark:bg-gray-900/50">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25 0H21M12 18h9" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No audits recorded</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Start your first cycle count by recording stock counts above.
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSelect = document.getElementById('warehouse_id');
    const productSelect = document.getElementById('product_id');
    const batchSelect = document.getElementById('batch_id');
    const systemQuantityInfo = document.getElementById('system-quantity-info');
    
    function fetchSystemQuantity() {
        const warehouseId = warehouseSelect.value;
        const productId = productSelect.value;
        const batchId = batchSelect.value;
        
        if (warehouseId && productId) {
            // Show loading state
            systemQuantityInfo.innerHTML = '<span class="text-gray-400">Loading system quantity...</span>';
            
            // Fetch system quantity via AJAX
            fetch(`/admin/stock/quantity?warehouse_id=${warehouseId}&product_id=${productId}&batch_id=${batchId || ''}`)
                .then(response => response.json())
                .then(data => {
                    if (data.quantity !== undefined) {
                        systemQuantityInfo.innerHTML = `
                            <span class="font-medium text-gray-700 dark:text-gray-300">System quantity:</span>
                            <span class="font-semibold text-brand-600 dark:text-brand-400">${data.quantity.toFixed(2)}</span>
                            <span class="text-gray-500 dark:text-gray-400"> ${data.uom || 'units'}</span>
                        `;
                    } else {
                        systemQuantityInfo.innerHTML = '<span class="text-gray-500">No stock record found</span>';
                    }
                })
                .catch(() => {
                    systemQuantityInfo.innerHTML = '<span class="text-error-600">Unable to fetch system quantity</span>';
                });
        } else {
            systemQuantityInfo.innerHTML = '';
        }
    }
    
    warehouseSelect.addEventListener('change', fetchSystemQuantity);
    productSelect.addEventListener('change', fetchSystemQuantity);
    batchSelect.addEventListener('change', fetchSystemQuantity);
});
</script>
@endpush
@endsection