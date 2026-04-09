@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Edit Packaging
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Update packaging title, unit, or description.
            </p>
        </div>
        <a href="{{ route('admin.packaging.index') }}" 
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to List
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $packagingType->name }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Created {{ $packagingType->created_at->diffForHumans() }} · 
                        Last updated {{ $packagingType->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.packaging.update', $packagingType) }}" method="POST" class="p-6">
            @csrf
            @method('PATCH')
            
            <div class="space-y-4">
                <!-- Packaging Name -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Packaging Name <span class="text-error-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $packagingType->name) }}" 
                               required
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., PET Bottle, Cardboard Carton">
                        @error('name')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Unit Label -->
                    <div class="sm:col-span-1">
                        <label for="unit" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Unit Label
                        </label>
                        <input type="text" 
                               id="unit" 
                               name="unit" 
                               value="{{ old('unit', $packagingType->unit) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., bottle, carton, crate">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Used in UOM dropdowns and conversions
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                              placeholder="Describe this packaging type, its specifications, or any special handling instructions...">{{ old('description', $packagingType->description) }}</textarea>
                </div>

                <!-- Preview Card (if unit exists) -->
                @if($packagingType->unit)
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Unit Preview</p>
                                <p class="mt-1">
                                    This packaging will appear as 
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                        {{ $packagingType->unit }}
                                    </span> 
                                    in UOM dropdowns.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Usage Stats (if used in products) -->
                @if(isset($packagingType->products_count) && $packagingType->products_count > 0)
                    <div class="rounded-lg bg-blue-light-50 p-4 border border-blue-light-100 dark:bg-blue-light-500/10 dark:border-blue-light-500/20">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-blue-light-600 dark:text-blue-light-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <div class="text-sm text-blue-light-800 dark:text-blue-light-300">
                                <p class="font-medium">Currently in Use</p>
                                <p class="mt-1">
                                    This packaging type is assigned to 
                                    <span class="font-semibold">{{ $packagingType->products_count }}</span> 
                                    {{ Str::plural('product', $packagingType->products_count) }}.
                                    Changes will apply to all associated products.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.packaging.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Danger Zone (for delete action) -->
    @if(!isset($packagingType->products_count) || $packagingType->products_count === 0)
        <div class="rounded-2xl border border-error-200 bg-white shadow-theme-sm dark:border-error-800/30 dark:bg-gray-900">
            <div class="border-b border-error-100 px-6 py-4 dark:border-error-800/20">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-error-700 dark:text-error-400">Danger Zone</h3>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this packaging type</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Once deleted, this packaging type cannot be recovered. 
                            This action cannot be undone.
                        </p>
                    </div>
                    <form action="{{ route('admin.packaging.destroy', $packagingType) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete {{ $packagingType->name }}? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Packaging
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection