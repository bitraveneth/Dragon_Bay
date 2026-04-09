@extends('layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Warehouses
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Inventory Locations
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Track stock across depots and hubs.
            </p>
        </div>
        <a href="{{ route('admin.warehouses.create') }}"
            data-tour="warehouses-primary-action"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Warehouse
        </a>
    </div>

    <!-- Status Message -->

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Warehouses</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $warehouses->count() }}</p>
                </div>
                <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Stock Items</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ $entries instanceof \Illuminate\Pagination\LengthAwarePaginator ? $entries->total() : $entries->count() }}
                    </p>
                </div>
                <div class="rounded-lg bg-blue-light-50 p-2.5 dark:bg-blue-light-500/10">
                    <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Active Locations</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ $warehouses->where('is_active', true)->count() }}
                    </p>
                </div>
                <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                    <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Low Stock Items</p>
                    <p class="mt-2 text-2xl font-semibold text-orange-600 dark:text-orange-400">0</p>
                </div>
                <div class="rounded-lg bg-orange-50 p-2.5 dark:bg-orange-500/10">
                    <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouses Grid -->
    @if($warehouses->isNotEmpty())
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($warehouses as $warehouse)
        @php
            $type = strtolower($warehouse->type ?? 'depot');
            $typeLabel = $warehouse->type ?? 'Depot';
            $typeStyles = [
                'factory' => ['bg' => 'bg-orange-50 dark:bg-orange-500/15', 'text' => 'text-orange-700 dark:text-orange-300'],
                'returns' => ['bg' => 'bg-red-50 dark:bg-red-500/15', 'text' => 'text-red-700 dark:text-red-300'],
                'consignment' => ['bg' => 'bg-purple-50 dark:bg-purple-500/15', 'text' => 'text-purple-700 dark:text-purple-300'],
                'depot' => ['bg' => 'bg-blue-light-50 dark:bg-blue-light-500/15', 'text' => 'text-blue-light-700 dark:text-blue-light-300'],
            ];
            $style = $typeStyles[$type] ?? $typeStyles['depot'];
        @endphp

        <a href="{{ route('admin.warehouses.show', $warehouse) }}"
            class="group block rounded-xl border border-gray-200 bg-white p-5 shadow-theme-sm transition-all hover:border-brand-200 hover:shadow-theme-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-700/80">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-slate-900 to-slate-700 text-slate-100 shadow-theme-xs dark:from-slate-800 dark:to-slate-900">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 11L12 4L21 11V20H3V11Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                            <path d="M9 20V13H15V20" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                            <path d="M6 11H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">
                            {{ $warehouse->name }}
                        </h3>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $style['bg'] }} {{ $style['text'] }}">
                                {{ $typeLabel }}
                            </span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                Code: {{ $warehouse->code ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
                @if(!($warehouse->is_active ?? true))
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    Inactive
                </span>
                @endif
            </div>

            <div class="mt-4">

                <div class="mt-4 flex items-center gap-4">
                    <div>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $warehouse->entries_count ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Stock Items</p>
                    </div>
                    @if($warehouse->capacity)
                    <div class="h-8 w-px bg-gray-200 dark:bg-gray-700"></div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ number_format($warehouse->capacity) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Capacity</p>
                    </div>
                    @endif
                </div>

                @if($warehouse->address)
                <p class="mt-3 text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                    {{ Str::limit($warehouse->address, 60) }}
                </p>
                @else
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-600">
                    No address yet
                </p>
                @endif
            </div>

                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 text-xs dark:border-gray-800">
                    <span class="inline-flex items-center gap-1 font-medium text-brand-600 group-hover:text-brand-700 dark:text-brand-400 dark:group-hover:text-brand-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                        View details
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                        Updated {{ $warehouse->updated_at->diffForHumans() }}
                    </span>
                </div>
        </a>
        @endforeach
    </div>

    @if(method_exists($warehouses, 'links'))
    <div class="mt-6">
        {{ $warehouses->links() }}
    </div>
    @endif
    @else
    <!-- Empty State -->
    <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7h8M8 11h6M8 15h4" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No warehouses found</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
            No warehouses have been added yet. Add your first warehouse to start tracking inventory.
        </p>
        <a href="{{ route('admin.warehouses.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Warehouse
        </a>
    </div>
    @endif

    <!-- Recent Stock Section -->
    <div class="mt-8 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Recent Stock Movements</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Latest inventory entries across all warehouses</p>
            </div>
            @if($entries->isNotEmpty() && method_exists($entries, 'links'))
            <a href="{{ route('admin.inventory.index') }}"
                class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                View all →
            </a>
            @endif
        </div>

        @if($entries->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Warehouse</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Batch</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($entries as $entry)
                        @php
                        $statusColors = [
                        'available' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                        'reserved' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                        'damaged' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                        'quarantined' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                        ];
                        $statusColor = $statusColors[$entry->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                        <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                            {{ substr($entry->warehouse->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $entry->warehouse->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $entry->warehouse->type ?? 'Depot' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $entry->product->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        SKU: {{ $entry->product->sku }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($entry->batch)
                                <span class="font-mono text-xs font-medium text-gray-900 dark:text-white">
                                    {{ $entry->batch->batch_code }}
                                </span>
                                @if($entry->batch->expiry_date)
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Exp: {{ \Carbon\Carbon::parse($entry->batch->expiry_date)->format('d M Y') }}
                                </p>
                                @endif
                                @else
                                <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($entry->quantity, 2) }}
                                </span>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($entries, 'links'))
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                {{ $entries->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-16 h-16 mb-3 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <h3 class="text-md font-medium text-gray-900 dark:text-white mb-1">No stock entries yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Stock movements will appear here when products are received or transferred.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
