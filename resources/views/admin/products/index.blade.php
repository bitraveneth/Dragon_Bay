@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-(--breakpoint-2xl) space-y-5 md:space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                Product catalog
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Browse finished SKUs, packaging, and pricing at a glance.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.export') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Export CSV
            </a>
            <a href="{{ route('admin.products.create') }}"
               data-tour="products-primary-action"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/50">
                Add product
            </a>
        </div>
    </div>

    <!-- Metrics + Search -->
    <div
        class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] md:flex-row md:items-center md:justify-between md:p-5">
        <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-3">
            @php $hasSearch = request()->filled('q'); @endphp
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                    Total SKUs
                </p>
                <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ number_format($products->total()) }}
                </p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    {{ $hasSearch ? 'Matching current search' : 'Sorted by latest' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                    Packaging types
                </p>
                <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ number_format($packagingCount) }}
                </p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Linked to packaging master
                </p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">
                    Tax classes
                </p>
                <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ number_format($taxClassCount) }}
                </p>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    HSN/SAC rates defined
                </p>
            </div>
        </div>

        <form class="mt-2 w-full max-w-md md:mt-0" method="GET" action="{{ route('admin.products.index') }}">
            <label class="relative block">
                <span class="sr-only">Search catalog</span>
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.75 3.75C5.98858 3.75 3.75 5.98858 3.75 8.75C3.75 11.5114 5.98858 13.75 8.75 13.75C9.99609 13.75 11.1293 13.2812 11.9844 12.5156L14.4844 15.0156C14.707 15.2383 15.077 15.2383 15.2996 15.0156C15.5223 14.793 15.5223 14.423 15.2996 14.2004L12.7996 11.7004C13.5652 10.8453 14.034 9.71212 14.034 8.46602C14.034 5.7046 11.7954 3.46602 9.034 3.46602C6.27258 3.46602 4.034 5.7046 4.034 8.46602C4.034 11.2274 6.27258 13.466 9.034 13.466" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </span>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    class="w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-sm text-gray-900 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                    placeholder="Search by SKU, name, or barcode…">
            </label>
        </form>
    </div>


    <!-- Products table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        @if($products->isNotEmpty())
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[780px]">
                    <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-900/40">
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">SKU</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Name</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Packaging</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Tax rate</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Price</th>
                        <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                            <td class="px-5 py-3 text-sm text-gray-800 dark:text-gray-100">
                                {{ $product->sku }}
                            </td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.products.show', $product) }}"
                                   class="text-sm font-medium text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400">
                                    {{ $product->name }}
                                </a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $product->size ?? '—' }}
                                </p>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $type = $product->product_type ?? 'finished';
                                    $typeLabel = ucfirst($type);
                                @endphp
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-200">
                                {{ $product->packagingType->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-200">
                                {{ $product->taxClass->rate ?? '0' }}%
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-800 dark:text-gray-100">
                                BDT {{ number_format($product->base_price ?? 0, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                @if($product->is_active)
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.products.show', $product) }}"
                                       class="text-theme-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        View
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="text-theme-xs font-medium text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-theme-xs font-medium text-error-600 hover:text-error-700 dark:text-error-400 dark:hover:text-error-300">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
                {{ $products->links() }}
            </div>
        @else
            <div class="px-5 py-6 text-sm text-gray-500 dark:text-gray-400">
                The catalog is currently empty. Click “Add product” to get started.
            </div>
        @endif
    </div>
</div>
@endsection
