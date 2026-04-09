@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    New Production Order
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Manufacturing
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Define the production order number, product, batch, line, shift, and planned quantity for this run.
            </p>
        </div>
        <a href="{{ route('admin.production.index') }}" 
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Orders & Runs
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7h8M8 11h6M8 15h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Production Run Details</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Enter the manufacturing order information</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.production.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Production Order Number -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="order_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Production Order No.
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16 4 4 4-4 4 16H7z"/>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="order_number" 
                                   name="order_number" 
                                   value="{{ old('order_number') }}"
                                   placeholder="Auto-generate if blank"
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                        <button type="button" 
                                id="btn-generate-order-number"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                title="Generate order number">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to auto-generate</p>
                    @error('order_number')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product -->
                <div>
                    <label for="product_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Product <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <select id="product_id" 
                                name="product_id" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->sku ?? '' }} {{ $product->sku ? '—' : '' }}{{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('product_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Batch -->
                <div>
                    <label for="batch_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Batch <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <select id="batch_id" 
                                name="batch_id" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select batch</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                                    {{ $batch->batch_code }} — {{ $batch->product->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('batch_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warehouse -->
                <div>
                    <label for="warehouse_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Warehouse
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                        </div>
                        <select id="warehouse_id" 
                                name="warehouse_id"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select warehouse</option>
                            @foreach($warehouses as $warehouse)
                                @php
                                    $selectedWarehouse = old('warehouse_id', $defaultWarehouseId ?? null);
                                @endphp
                                <option value="{{ $warehouse->id }}" {{ $selectedWarehouse == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('warehouse_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Production Line -->
                <div>
                    <label for="line" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Production Line
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <select id="line" 
                                name="line"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @php($line = old('line', 'Line 1'))
                            <option value="Line 1" {{ $line === 'Line 1' ? 'selected' : '' }}>Line 1</option>
                            <option value="Line 2" {{ $line === 'Line 2' ? 'selected' : '' }}>Line 2</option>
                        </select>
                    </div>
                    @error('line')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Shift -->
                <div>
                    <label for="shift" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Shift
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <select id="shift" 
                                name="shift"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @php($shift = old('shift', 'Morning'))
                            <option value="Morning" {{ $shift === 'Morning' ? 'selected' : '' }}>Morning</option>
                            <option value="Evening" {{ $shift === 'Evening' ? 'selected' : '' }}>Evening</option>
                            <option value="Night" {{ $shift === 'Night' ? 'selected' : '' }}>Night</option>
                        </select>
                    </div>
                    @error('shift')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Quantity (cartons) <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <input type="number" 
                               id="quantity" 
                               name="quantity" 
                               type="number" 
                               step="1" 
                               min="1"
                               value="{{ old('quantity', 0) }}" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                    </div>
                    @error('quantity')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cost Information -->
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Material cost per unit</span>
                            <span id="production-unit-cost" class="text-sm font-semibold text-gray-900 dark:text-white">—</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Estimated total cost</span>
                            <span id="production-total-cost" class="text-sm font-semibold text-brand-600 dark:text-brand-400">—</span>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <select id="status" 
                                name="status"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @php($status = old('status', 'confirmed'))
                            <option value="planned" {{ $status === 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Supervisor -->
                <div>
                    <label for="supervisor_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Supervisor
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <select id="supervisor_id" 
                                name="supervisor_id"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select supervisor</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('supervisor_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}{{ $employee->job_position ? ' – '.$employee->job_position : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('supervisor_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Materials Reserved (Full Width) -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="materials_reserved" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Raw Materials Reserved
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute left-3 top-3 flex items-start">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <textarea id="materials_reserved" 
                                  name="materials_reserved" 
                                  rows="2"
                                  class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                  placeholder="e.g., 24,000 bottles, 1,000 cartons, caps, labels.">{{ old('materials_reserved') }}</textarea>
                    </div>
                    @error('materials_reserved')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes (Full Width) -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute left-3 top-3 flex items-start">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="2"
                                  class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                  placeholder="Additional information about this production run...">{{ old('notes') }}</textarea>
                    </div>
                    @error('notes')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.production.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Record Production Run
                </button>
            </div>
        </form>
    </div>

    <!-- Materials Required Section -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                        <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Materials Required for this Run</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Based on the active BOM. Uses the selected warehouse to highlight shortages.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Component</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Required</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Warehouse</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Available</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Shortage</th>
                    </tr>
                </thead>
                <tbody id="materials-required-body" class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Select a product and quantity to see material requirements.
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div id="materials-required-summary" class="border-t border-gray-100 px-6 py-4 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400">
            Select a product and quantity to see summary of required materials and services.
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const productUnitCosts = @json($productUnitCosts ?? []);
        const materialRequirements = @json($materialRequirements ?? []);
        const warehouseStock = @json($warehouseStock ?? []);
        const warehouseNames = @json($warehouses->pluck('name','id'));
        const productSelect = document.getElementById('product_id');
        const quantityInput = document.getElementById('quantity');
        const warehouseSelect = document.getElementById('warehouse_id');
        const unitCostEl = document.getElementById('production-unit-cost');
        const totalCostEl = document.getElementById('production-total-cost');
        const materialsBody = document.getElementById('materials-required-body');
        const materialsSummary = document.getElementById('materials-required-summary');

        function updateProductionCost() {
            const productId = productSelect.value;
            const quantity = parseFloat(quantityInput.value) || 0;
            const unitCost = productUnitCosts[productId] ?? null;

            if (!unitCost || !quantity) {
                unitCostEl.textContent = '—';
                totalCostEl.textContent = '—';
                return;
            }

            unitCostEl.textContent = `BDT ${unitCost.toFixed(2)}`;
            totalCostEl.textContent = `BDT ${(unitCost * quantity).toFixed(2)}`;
        }

        function updateMaterialRequirements() {
            const productId = productSelect.value;
            const quantity = parseFloat(quantityInput.value) || 0;
            const warehouseId = warehouseSelect.value || '{{ $defaultWarehouseId ?? '' }}';

            const components = materialRequirements[productId] ?? null;
            if (!materialsBody) return;

            materialsBody.innerHTML = '';

            const rawDetails = {};
            let labourRequired = 0;
            let utilitiesRequired = 0;
            let labourShortage = 0;
            let utilitiesShortage = 0;
            let hasComponents = false;

            if (!components || components.length === 0 || !quantity) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="5" class="px-4 py-8 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-12 w-12 text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                ${!productId ? 'Select a product' : !quantity ? 'Enter quantity' : 'No BOM defined for this product'}.
                            </p>
                        </div>
                    </td>
                `;
                materialsBody.appendChild(row);
                return;
            }

            components.forEach((comp) => {
                const required = comp.quantity_per_unit * quantity;
                const available = (warehouseStock[warehouseId] && warehouseStock[warehouseId][comp.product_id])
                    ? warehouseStock[warehouseId][comp.product_id]
                    : 0;
                const shortage = Math.max(0, required - available);
                const warehouseName = warehouseNames[warehouseId] || 'Selected warehouse';
                const type = (comp.type || '').toString().toLowerCase();

                // Collect raw material details by SKU/name
                if (type === 'raw') {
                    const key = comp.sku || comp.name || 'Raw material';
                    rawDetails[key] = (rawDetails[key] || 0) + required;
                }

                // Sum services separately
                if (comp.sku && comp.sku.startsWith('SV-LAB')) {
                    labourRequired += required;
                    if (shortage > 0) labourShortage += shortage;
                } else if (comp.sku && comp.sku.startsWith('SV-UTIL')) {
                    utilitiesRequired += required;
                    if (shortage > 0) utilitiesShortage += shortage;
                }

                const row = document.createElement('tr');
                row.className = shortage > 0 ? 'bg-error-50/30 dark:bg-error-500/5' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50';
                
                row.innerHTML = `
                    <td class="px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                ${comp.sku ? comp.sku + ' — ' : ''}${comp.name ?? ''}
                            </p>
                            ${comp.type ? `<span class="text-xs text-gray-500 dark:text-gray-400">${comp.type}</span>` : ''}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            ${required.toFixed(2)} ${comp.uom ?? ''}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                        ${warehouseName}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm ${available >= required ? 'text-gray-700 dark:text-gray-300' : 'text-error-600 dark:text-error-500'}">
                            ${available.toFixed(2)} ${comp.uom ?? ''}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        ${shortage > 0 ? 
                            `<span class="inline-flex items-center rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                ${shortage.toFixed(2)} ${comp.uom ?? ''}
                            </span>` : 
                            `<span class="text-xs text-success-600 dark:text-success-400">OK</span>`
                        }
                    </td>
                `;
                materialsBody.appendChild(row);
                hasComponents = true;
            });

            if (materialsSummary && hasComponents) {
                const lines = [];

                if (Object.keys(rawDetails).length) {
                    const rawLine = Object.entries(rawDetails)
                        .map(([name, qty]) => `${qty.toFixed(2)} × ${name}`)
                        .join(', ');
                    lines.push(`<span class="font-medium">Raw materials:</span> ${rawLine}`);
                }

                lines.push(
                    `<span class="font-medium">Labour:</span> ${labourRequired.toFixed(2)} day (shortage: ${labourShortage.toFixed(2)})`
                );

                lines.push(
                    `<span class="font-medium">Utilities:</span> ${utilitiesRequired.toFixed(2)} shift (shortage: ${utilitiesShortage.toFixed(2)})`
                );

                materialsSummary.innerHTML = lines.join('<br>');
            } else {
                materialsSummary.textContent = 'Select a product and quantity to see summary of required materials and services.';
            }
        }

        if (productSelect) productSelect.addEventListener('change', updateProductionCost);
        if (quantityInput) quantityInput.addEventListener('input', updateProductionCost);
        if (productSelect) productSelect.addEventListener('change', updateMaterialRequirements);
        if (quantityInput) quantityInput.addEventListener('input', updateMaterialRequirements);
        if (warehouseSelect) warehouseSelect.addEventListener('change', updateMaterialRequirements);

        // Initialize
        updateProductionCost();
        updateMaterialRequirements();
        
        // Generate order number button
        const generateBtn = document.getElementById('btn-generate-order-number');
        const orderNumberInput = document.getElementById('order_number');
        
        if (generateBtn && orderNumberInput) {
            generateBtn.addEventListener('click', () => {
                const date = new Date();
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                orderNumberInput.value = `PO-${year}${month}${day}-${random}`;
            });
        }
    });
</script>
@endpush
@endsection