@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-5 md:space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ $product->name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                SKU {{ $product->sku }} · {{ $product->size ?? 'Unspecified size' }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $product->description ?? 'No description provided yet.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.edit', $product) }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Edit
            </a>
            <a href="{{ route('admin.products.create') }}"
               class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/50">
                New product
            </a>
        </div>
    </div>


    <!-- Summary cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Packaging</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $product->packagingType->name ?? 'Unassigned' }}
            </p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                {{ $product->packagingType->unit ?? '' }}
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Tax class</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $product->taxClass->name ?? 'Unassigned' }} ({{ $product->taxClass->rate ?? 0 }}%)
            </p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                {{ $product->taxClass->hsn_code ?? $product->taxClass->local_tax_code ?? '' }}
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Volume</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $product->volume_ml ? number_format($product->volume_ml, 2) . ' ml' : '—' }}
            </p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Size: {{ $product->size ?? 'unknown' }}
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Price</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                BDT {{ number_format($product->base_price ?? 0, 2) }}
            </p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Base price
            </p>
        </div>
    </div>

    @if($product->image_path)
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="mb-2 text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Product image</p>
            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-auto w-full max-w-sm rounded-xl border border-gray-200 object-cover dark:border-gray-700">
        </div>
    @endif

    <!-- Composition -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Composition</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Mineral source</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                    {{ $product->mineral_source ?? 'Not specified' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">pH</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                    {{ $product->ph ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">TDS</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                    {{ $product->tds ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Certifications</p>
                <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                    {{ $product->certifications ?? '—' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Codes & costing + batches -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] space-y-5">
        <div>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Codes &amp; costing</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">SKU</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->sku }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit of measure</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->uom ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Standard cost per unit</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">
                        {{ $product->standard_cost !== null ? number_format($product->standard_cost, 2) : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Supplier (optional)</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $product->supplier_name ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Batches</h2>
            @if($product->batches->isEmpty())
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No batches recorded yet.</p>
            @else
                <div class="mt-3 max-w-full overflow-x-auto custom-scrollbar">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 text-theme-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900/40 dark:text-gray-400">
                            <th class="px-3 py-2">Batch code</th>
                            <th class="px-3 py-2">Production</th>
                            <th class="px-3 py-2">Expiry</th>
                            <th class="px-3 py-2">QC status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($product->batches as $batch)
                            <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                                <td class="px-3 py-2">{{ $batch->batch_code }}</td>
                                <td class="px-3 py-2">{{ $batch->production_date ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $batch->expiry_date ?? '—' }}</td>
                                <td class="px-3 py-2">{{ ucfirst($batch->qc_status) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
