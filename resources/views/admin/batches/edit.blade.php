@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Edit Batch
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    {{ $batch->batch_code }}
                </span>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Adjust production, expiry, or QC status.
                </p>
                @if($batch->product)
                    <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $batch->product->sku }} — {{ $batch->product->name }}
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.batches.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Batches
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
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Batch Details</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Created {{ \Carbon\Carbon::parse($batch->created_at)->diffForHumans() }} · 
                        Last updated {{ \Carbon\Carbon::parse($batch->updated_at)->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.batches.update', $batch) }}" method="POST" class="p-6">
            @csrf
            @method('PATCH')
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Product Selection -->
                <div class="sm:col-span-2 lg:col-span-1">
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
                                <option value="{{ $product->id }}" {{ old('product_id', $batch->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->sku }} — {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('product_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Batch Code -->
                <div>
                    <label for="batch_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Batch Code <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <input type="text" 
                               id="batch_code" 
                               name="batch_code" 
                               value="{{ old('batch_code', $batch->batch_code) }}" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                    </div>
                    @error('batch_code')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Production Date -->
                <div>
                    <label for="production_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Production Date <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="date" 
                               id="production_date" 
                               name="production_date" 
                               value="{{ old('production_date', $batch->production_date ? \Carbon\Carbon::parse($batch->production_date)->format('Y-m-d') : '') }}" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    @error('production_date')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiry Date -->
                <div>
                    <label for="expiry_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Expiry Date
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <input type="date" 
                               id="expiry_date" 
                               name="expiry_date" 
                               value="{{ old('expiry_date', $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('Y-m-d') : '') }}"
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank if no expiry date</p>
                    @error('expiry_date')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- QC Status -->
                <div>
                    <label for="qc_status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        QC Status
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <select id="qc_status" 
                                name="qc_status"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @foreach(['pending', 'approved', 'rejected'] as $status)
                                <option value="{{ $status }}" {{ old('qc_status', $batch->qc_status) == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('qc_status')
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
                                  rows="3"
                                  class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                  placeholder="Additional information about this batch...">{{ old('notes', $batch->notes) }}</textarea>
                    </div>
                    @error('notes')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.batches.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Batch
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone - Delete Section -->
    <div class="rounded-2xl border border-error-200 bg-white shadow-theme-sm dark:border-error-800/30 dark:bg-gray-900">
        <div class="border-b border-error-100 px-6 py-4 dark:border-error-800/20">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-medium text-error-700 dark:text-error-400">Danger Zone</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this batch</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Once deleted, this batch record cannot be recovered. This action cannot be undone.
                    </p>
                </div>
                <form action="{{ route('admin.batches.destroy', $batch) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete batch {{ $batch->batch_code }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Batch
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection