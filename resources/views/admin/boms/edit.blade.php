@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Edit Bill of Materials
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    BOM
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Update the bill of materials for {{ $bom->product?->name }}.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.boms.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to BOMs
            </a>
            <button type="button" 
                    onclick="document.getElementById('bom-form').submit()"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $bom->product?->sku ?? '' }} – {{ $bom->product?->name ?? 'Unnamed Product' }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Last updated {{ $bom->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        <form id="bom-form" action="{{ route('admin.boms.update', $bom) }}" method="post" class="p-6">
            @csrf
            @method('PATCH')

            <!-- Finished Product Section -->
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white">Finished Product</h4>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Product (Read-only) -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Product
                        </label>
                        <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800/50">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $bom->product?->sku ?? '' }} – {{ $bom->product?->name ?? '—' }}
                            </span>
                        </div>
                    </div>

                    <!-- BOM Name -->
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            BOM Name
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                </svg>
                            </div>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $bom->name) }}"
                                   placeholder="e.g. Standard Recipe, Export Version"
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank for default BOM</p>
                        @error('name')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active" 
                                   value="1" 
                                   @checked(old('is_active', $bom->is_active))
                                   class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                    </div>

                    <!-- Notes -->
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
                                      placeholder="Additional information about this BOM...">{{ old('notes', $bom->notes) }}</textarea>
                        </div>
                        @error('notes')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Components Section -->
            <div class="mt-8 space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-md font-medium text-gray-900 dark:text-white">Components</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Materials and sub-assemblies required for production
                            </p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300" id="component-count">
                            {{ $bom->items->count() }} {{ Str::plural('component', $bom->items->count()) }}
                        </span>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="bom-items-table">
                            <thead class="bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Component</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Qty/Unit</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Unit Cost</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Unit</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($bom->items as $index => $item)
                                    <tr class="bg-white dark:bg-gray-900">
                                        <td class="px-4 py-3">
                                            <select name="items[{{ $index }}][component_product_id]" required
                                                    class="w-full min-w-[200px] rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="">Select component</option>
                                                @foreach($materials as $material)
                                                    <option value="{{ $material->id }}"
                                                        @selected(old("items.$index.component_product_id", $item->component_product_id) == $material->id)>
                                                        [{{ strtoupper($material->product_type ?? 'raw') }}] {{ $material->sku }} – {{ $material->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" 
                                                   step="0.0001" 
                                                   min="0" 
                                                   name="items[{{ $index }}][quantity]"
                                                   value="{{ old("items.$index.quantity", $item->quantity) }}" 
                                                   required
                                                   placeholder="0.00"
                                                   class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="relative w-32">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">BDT</span>
                                                <input type="number" 
                                                       step="0.0001" 
                                                       min="0" 
                                                       name="items[{{ $index }}][unit_cost]"
                                                       value="{{ old("items.$index.unit_cost", $item->unit_cost) }}"
                                                       placeholder="0.00"
                                                       class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-3 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" 
                                                   name="items[{{ $index }}][unit]"
                                                   value="{{ old("items.$index.unit", $item->unit) }}"
                                                   placeholder="pcs, bottle, cap"
                                                   class="w-28 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" 
                                                    onclick="removeBomRow(this)"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    {{ $bom->items->count() <= 1 ? 'disabled' : '' }}>
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                
                                @if($bom->items->isEmpty())
                                    <tr class="bg-white dark:bg-gray-900">
                                        <td class="px-4 py-3">
                                            <select name="items[0][component_product_id]" required
                                                    class="w-full min-w-[200px] rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="">Select component</option>
                                                @foreach($materials as $material)
                                                    <option value="{{ $material->id }}">[{{ strtoupper($material->product_type) }}] {{ $material->sku }} – {{ $material->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.0001" min="0" name="items[0][quantity]" required
                                                   placeholder="0.00"
                                                   class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="relative w-32">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">BDT</span>
                                                <input type="number" step="0.0001" min="0" name="items[0][unit_cost]" 
                                                       placeholder="0.00"
                                                       class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-3 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[0][unit]" placeholder="pcs, bottle, cap"
                                                   class="w-28 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" 
                                                    onclick="removeBomRow(this)"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10"
                                                    disabled>
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add Component Button -->
                <div class="flex items-center gap-3">
                    <button type="button" 
                            onclick="addBomRow()"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Component
                    </button>
                </div>

                <!-- Cost Summary -->
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @php
                        $estimatedCost = 0;
                        foreach($bom->items as $item) {
                            if (!is_null($item->unit_cost) && !is_null($item->quantity)) {
                                $estimatedCost += (float) $item->unit_cost * (float) $item->quantity;
                            }
                        }
                    @endphp
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                        <p id="bom-unit-total" class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Estimated material cost per finished unit:</span> 
                            <span class="ml-2 text-lg font-semibold text-brand-600 dark:text-brand-400">BDT {{ number_format($estimatedCost, 2) }}</span>
                        </p>
                    </div>
                    
                    <div>
                        <label for="material_unit_cost" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Override Material Cost
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
                            <input type="number" 
                                   id="material_unit_cost" 
                                   name="material_unit_cost" 
                                   step="0.01" 
                                   min="0" 
                                   value="{{ old('material_unit_cost', $bom->material_unit_cost) }}"
                                   placeholder="Leave blank to use estimate"
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Optional: Override the calculated material cost
                        </p>
                    </div>
                </div>
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
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this BOM</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Once deleted, this bill of materials and all its components cannot be recovered.
                        This action cannot be undone.
                    </p>
                </div>
                <form action="{{ route('admin.boms.destroy', $bom) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this BOM for {{ $bom->product?->name ?? 'this product' }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete BOM
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let bomRowIndex = {{ max($bom->items->count(), 1) }};
    
    function recalcBomTotal() {
        const tbody = document.querySelector('#bom-items-table tbody');
        const rows = tbody ? tbody.querySelectorAll('tr') : [];
        let total = 0;
        let componentCount = 0;

        rows.forEach((row) => {
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            const costInput = row.querySelector('input[name*="[unit_cost]"]');
            if (!qtyInput || !costInput) return;

            const qty = parseFloat(qtyInput.value) || 0;
            const unitCost = parseFloat(costInput.value) || 0;
            total += qty * unitCost;
            componentCount++;
        });

        const label = document.getElementById('bom-unit-total');
        if (label) {
            label.innerHTML = `<span class="font-medium">Estimated material cost per finished unit:</span> 
                               <span class="ml-2 text-lg font-semibold text-brand-600 dark:text-brand-400">BDT ${total.toFixed(2)}</span>`;
        }

        const countLabel = document.getElementById('component-count');
        if (countLabel) {
            countLabel.textContent = `${componentCount} ${componentCount === 1 ? 'component' : 'components'}`;
        }
    }

    function bindBomInputs() {
        const tbody = document.querySelector('#bom-items-table tbody');
        if (!tbody) return;
        tbody.querySelectorAll('input[name*="[quantity]"], input[name*="[unit_cost]"]').forEach((input) => {
            input.removeEventListener('input', recalcBomTotal);
            input.addEventListener('input', recalcBomTotal);
        });
    }

    function addBomRow() {
        const table = document.getElementById('bom-items-table').querySelector('tbody');
        const template = table.rows[0].cloneNode(true);
        
        // Update remove button state
        const removeBtn = template.querySelector('button[onclick*="removeBomRow"]');
        if (removeBtn) {
            removeBtn.disabled = false;
            removeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        
        // Update input names and clear values
        [...template.querySelectorAll('select, input')].forEach((el) => {
            if (el.tagName === 'SELECT') {
                if (el.name.includes('component_product_id')) {
                    el.name = `items[${bomRowIndex}][component_product_id]`;
                    el.value = '';
                }
            } else {
                if (el.name.includes('quantity')) {
                    el.name = `items[${bomRowIndex}][quantity]`;
                    el.value = '';
                } else if (el.name.includes('unit_cost')) {
                    el.name = `items[${bomRowIndex}][unit_cost]`;
                    el.value = '';
                } else if (el.name.includes('unit')) {
                    el.name = `items[${bomRowIndex}][unit]`;
                    el.value = '';
                }
            }
        });
        
        table.appendChild(template);
        bomRowIndex++;
        bindBomInputs();
        recalcBomTotal();
    }

    function removeBomRow(button) {
        const tableBody = document.getElementById('bom-items-table').querySelector('tbody');
        if (tableBody.rows.length <= 1) {
            alert('At least one component is required.');
            return;
        }
        const row = button.closest('tr');
        if (row) {
            row.remove();
            bindBomInputs();
            recalcBomTotal();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        bindBomInputs();
        recalcBomTotal();
    });
</script>
@endpush
@endsection