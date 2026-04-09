@extends('layouts.app')

@section('content')
@php
    $context = $context ?? (request()->routeIs('admin.materials.*') ? 'materials' : 'products');
    $isMaterials = $context === 'materials';
@endphp

<div class="mx-auto max-w-(--breakpoint-2xl) space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ $isMaterials ? 'Add Material' : 'Add Product' }}
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                @if($isMaterials)
                    Define raw materials, services, or in‑house steps used in BOMs.
                @else
                    Capture SKU details, packaging linkage, and pricing.
                @endif
            </p>
        </div>
        <a href="{{ $isMaterials ? route('admin.materials.index') : route('admin.products.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $isMaterials ? 'Back to Materials' : 'Back to Catalog' }}
        </a>
    </div>

    <!-- Form card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ $isMaterials ? route('admin.materials.store') : route('admin.products.store') }}"
              method="POST" class="space-y-8 p-6" enctype="multipart/form-data">
            @csrf

            <!-- Section 1: Basic Details -->
            <div class="space-y-4" data-tour="product-form-basics">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $isMaterials ? 'Material Details' : 'Product Details' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($isMaterials)
                            Define the material name and an optional description.
                        @else
                            Define the product name, description, and hero image.
                        @endif
                    </p>
                </div>
                
                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $isMaterials ? 'Material Name' : 'Product Name' }}
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        @error('name')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type (for materials only) -->
                    @if($isMaterials)
                        <div>
                            <label for="product_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Material Type
                            </label>
                            <select id="product_type" name="product_type" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @php($type = old('product_type', 'raw'))
                                <option value="raw"{{ $type === 'raw' ? ' selected' : '' }}>Raw material (stocked)</option>
                                <option value="service"{{ $type === 'service' ? ' selected' : '' }}>Service / external cost</option>
                                <option value="inhouse"{{ $type === 'inhouse' ? ' selected' : '' }}>In‑house / self‑made step</option>
                            </select>
                        </div>
                    @else
                        <!-- Hidden field for finished products -->
                        <input type="hidden" id="product_type" name="product_type" value="{{ old('product_type', 'finished') }}">
                    @endif

                    <!-- Product Image (for products only) -->
                    @unless($isMaterials)
                        <div>
                            <label for="product_image" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Product Image
                                <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">(PNG/JPG, 1080×1080 px preferred)</span>
                            </label>
                            <input type="file" id="product_image" name="product_image" accept="image/*"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:file:bg-gray-700 dark:file:text-gray-300">
                        </div>
                    @endunless

                    <!-- Description -->
                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Measurements & Units -->
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Measurements &amp; Units
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($isMaterials)
                            Define the unit of measure for this material.
                        @else
                            Define size labels, units of measure, and volumes.
                        @endif
                    </p>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Unit of Measure -->
                    <div>
                        <label for="uom" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Unit of Measure (UOM)
                        </label>
                        <select id="uom" name="uom"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select unit</option>
                            @foreach(['piece','carton','bottle','jar','liter','ml','day','shift','trip'] as $uom)
                                <option value="{{ $uom }}"{{ old('uom') === $uom ? ' selected' : '' }}>{{ ucfirst($uom) }}</option>
                            @endforeach
                        </select>
                    </div>

                    @unless($isMaterials)
                        <!-- Size -->
                        <div>
                            <label for="size" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Size
                            </label>
                            <select id="size_select" 
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select size</option>
                                @foreach(['500ml','1L','20L'] as $size)
                                    <option value="{{ $size }}"{{ old('size') === $size ? ' selected' : '' }}>{{ $size }}</option>
                                @endforeach
                                <option value="__custom"{{ old('size') && ! in_array(old('size'), ['500ml','1L','20L'], true) ? ' selected' : '' }}>Custom…</option>
                            </select>
                            <input type="text" id="size" name="size" value="{{ old('size') }}" 
                                   class="mt-2 hidden w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>

                        <!-- Volume -->
                        <div>
                            <label for="volume_ml" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Volume (ml)
                            </label>
                            <select id="volume_ml_select"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select volume</option>
                                @foreach([500, 1000, 20000] as $volume)
                                    <option value="{{ $volume }}"{{ (string) old('volume_ml') === (string) $volume ? ' selected' : '' }}>
                                        {{ $volume }}
                                    </option>
                                @endforeach
                                <option value="__custom"{{ old('volume_ml') && ! in_array((int) old('volume_ml'), [500,1000,20000], true) ? ' selected' : '' }}>Custom…</option>
                            </select>
                            <input type="number" id="volume_ml" name="volume_ml" value="{{ old('volume_ml') }}"
                                   class="mt-2 hidden w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                    @endunless
                </div>
            </div>

            @unless($isMaterials)
                <!-- Section 3: Packaging & Taxation -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Packaging &amp; Taxation
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Link SKUs to packaging master data and VAT-ready tax classes.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Packaging Type -->
                        <div>
                            <label for="packaging_type_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Packaging Type
                            </label>
                            <select id="packaging_type_id" name="packaging_type_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Unassigned</option>
                                @foreach($packagingTypes as $type)
                                    <option value="{{ $type->id }}"{{ old('packaging_type_id') == $type->id ? ' selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tax Class -->
                        <div>
                            <label for="tax_class_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tax Class
                            </label>
                            <select id="tax_class_id" name="tax_class_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Unassigned</option>
                                @foreach($taxClasses as $class)
                                    <option value="{{ $class->id }}"{{ old('tax_class_id') == $class->id ? ' selected' : '' }}>
                                        {{ $class->name }} &middot; {{ $class->rate }}%
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Composition & Certifications -->
                <div class="space-y-4">
                    <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Composition &amp; Certifications
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Track mineral source, pH, TDS, and Bangladesh-ready certifications (e.g., BSTI, ISO).
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Mineral Source -->
                        <div>
                            <label for="mineral_source" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Mineral Source
                            </label>
                            <input type="text" id="mineral_source" name="mineral_source" value="{{ old('mineral_source') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>

                        <!-- pH -->
                        <div>
                            <label for="ph" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                pH
                            </label>
                            <input type="number" step="0.01" id="ph" name="ph" value="{{ old('ph') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>

                        <!-- TDS -->
                        <div>
                            <label for="tds" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                TDS (ppm)
                            </label>
                            <input type="number" id="tds" name="tds" value="{{ old('tds') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>

                        <!-- Certifications -->
                        <div>
                            <label for="certifications" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Certifications (e.g., BSTI, ISO)
                            </label>
                            <input type="text" id="certifications" name="certifications" value="{{ old('certifications') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                    </div>
                </div>
            @endunless

            <!-- Section 5: Codes, Pricing & Costing -->
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Codes, Pricing &amp; Costing
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($isMaterials)
                            Track standard cost and optional supplier for this material.
                        @else
                            Define the base selling price for this SKU.
                        @endif
                    </p>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- SKU -->
                    <div>
                        <label for="sku" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $isMaterials ? 'Material Code (SKU)' : 'SKU' }}
                        </label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku') }}" required
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        @error('sku')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Base Price (for products only) -->
                    @unless($isMaterials)
                        <div>
                            <label for="base_price" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Base Price (Selling Price)
                            </label>
                            <input type="number" step="0.01" id="base_price" name="base_price" value="{{ old('base_price') }}" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                            @error('base_price')
                                <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endunless

                    @if($isMaterials)
                        <!-- Standard Cost (for materials only) -->
                        <div>
                            <label for="standard_cost" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Standard Cost per Unit
                            </label>
                            <input type="number" step="0.01" id="standard_cost" name="standard_cost" value="{{ old('standard_cost') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>

                        <!-- Supplier Name (for materials only) -->
                        <div>
                            <label for="supplier_name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Supplier Name (optional)
                            </label>
                            <input type="text" id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                    @endif
                </div>

                <!-- Active Status -->
                <div class="mt-4">
                    <label for="is_active" class="inline-flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', 1) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Inactive {{ $isMaterials ? 'materials' : 'SKUs' }} are hidden from new lists and BOMs.
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <button type="submit"
                        data-tour="product-form-submit"
                        class="inline-flex items-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $isMaterials ? 'Save Material' : 'Save Product' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom size selector
    const sizeSelect = document.getElementById('size_select');
    const sizeInput = document.getElementById('size');
    
    if (sizeSelect && sizeInput) {
        sizeSelect.addEventListener('change', function() {
            if (this.value === '__custom') {
                sizeInput.style.display = 'block';
                sizeInput.value = '';
                sizeInput.focus();
            } else {
                sizeInput.style.display = 'none';
                sizeInput.value = this.value;
            }
        });
        
        // Initialize on page load
        if (sizeSelect.value === '__custom') {
            sizeInput.style.display = 'block';
        }
    }
    
    // Custom volume selector
    const volumeSelect = document.getElementById('volume_ml_select');
    const volumeInput = document.getElementById('volume_ml');
    
    if (volumeSelect && volumeInput) {
        volumeSelect.addEventListener('change', function() {
            if (this.value === '__custom') {
                volumeInput.style.display = 'block';
                volumeInput.value = '';
                volumeInput.focus();
            } else {
                volumeInput.style.display = 'none';
                volumeInput.value = this.value;
            }
        });
        
        // Initialize on page load
        if (volumeSelect.value === '__custom') {
            volumeInput.style.display = 'block';
        }
    }
});
</script>
@endpush
@endsection
