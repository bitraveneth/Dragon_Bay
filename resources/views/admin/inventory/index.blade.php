@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4" data-tour="inventory-overview-header">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375 7.444 2.25 12 2.25s8.25 1.847 8.25 4.125zm0 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-9m16.5 9v-9" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Inventory Dashboard
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        View reserved vs. available stock and expiring batches
                    </p>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.stock.movements') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Movement Log
        </a>
    </div>

    <!-- Stock Summary Cards -->
    @if($summary->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($summary as $bucket)
                @php
                    $statusColors = [
                        'available' => 'from-success-500 to-success-600',
                        'reserved' => 'from-brand-500 to-brand-600',
                        'damaged' => 'from-error-500 to-error-600',
                        'quarantined' => 'from-orange-500 to-orange-600',
                    ];
                    $statusGradient = $statusColors[$bucket->status] ?? 'from-gray-500 to-gray-600';
                    
                    $statusIcons = [
                        'available' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'reserved' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
                        'damaged' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
                        'quarantined' => 'M12 9v3.75m-1.5-2.25h3M12 15.75h.007v.008H12v-.008z',
                    ];
                    $statusIcon = $statusIcons[$bucket->status] ?? 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z';
                @endphp
                <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                        <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375 7.444 2.25 12 2.25s8.25 1.847 8.25 4.125zm0 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-9m16.5 9v-9" />
                        </svg>
                    </div>
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br {{ $statusGradient }} text-white shadow-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ $bucket->warehouse->name }}
                                </span>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium capitalize text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ $bucket->status }}
                            </span>
                        </div>
                        <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($bucket->total, 2) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Total quantity in stock
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white/50 p-8 text-center dark:border-gray-800 dark:bg-gray-900/50">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375 7.444 2.25 12 2.25s8.25 1.847 8.25 4.125zm0 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-9m16.5 9v-9" />
                </svg>
            </div>
            <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">No stock data available</h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No inventory records found.</p>
        </div>
    @endif

    <!-- Expiring Soon Section -->
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-orange-100 to-orange-50 dark:from-orange-900/30 dark:to-orange-800/30">
                <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Expiring Soon</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Batches with expiry within 30 days</p>
            </div>
        </div>

        @if($expiringSoon->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white/50 p-8 text-center dark:border-gray-800 dark:bg-gray-900/50">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-success-100 dark:bg-success-900/30">
                    <svg class="h-6 w-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">No expiring batches in the next 30 days.</p>
            </div>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Batch</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Expiry</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Warehouse</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($expiringSoon as $entry)
                                @php
                                    $daysUntilExpiry = now()->diffInDays($entry->batch->expiry_date, false);
                                    $expiryClass = $daysUntilExpiry <= 7 ? 'bg-error-50 dark:bg-error-500/5' : 'bg-orange-50 dark:bg-orange-500/5';
                                @endphp
                                <tr class="{{ $expiryClass }} hover:bg-gray-100 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $entry->product->name }}</p>
                                            @if($entry->product->sku)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $entry->product->sku }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $entry->batch->batch_code ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm {{ $daysUntilExpiry <= 7 ? 'text-error-600 dark:text-error-500 font-semibold' : 'text-orange-600 dark:text-orange-400' }}">
                                                {{ optional($entry->batch->expiry_date)->format('d M Y') }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full bg-white px-2 py-0.5 text-xs font-medium {{ $daysUntilExpiry <= 7 ? 'text-error-600' : 'text-orange-600' }} shadow-sm dark:bg-gray-800">
                                                {{ $daysUntilExpiry }} days
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $entry->warehouse->name }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form action="{{ route('admin.stock.entries.writeoff', $entry) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Write off this batch as expired? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-error-200 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-sm hover:bg-error-50 hover:text-error-700 dark:border-error-800 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Write Off
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Stock Movements Section -->
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-light-100 to-blue-light-50 dark:from-blue-light-900/30 dark:to-blue-light-800/30">
                <svg class="h-4 w-4 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Stock Movements</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Last 10 inventory transactions – transfers, write‑offs, deliveries</p>
            </div>
        </div>

        @if($recentMovements->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white/50 p-8 text-center dark:border-gray-800 dark:bg-gray-900/50">
                <p class="text-sm text-gray-600 dark:text-gray-400">No recent stock movements found.</p>
            </div>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Warehouse</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($recentMovements as $movement)
                                @php
                                    $movementColors = [
                                        'receipt' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                        'sale' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                        'write-off' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                        'transfer' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    ];
                                    $movementColor = $movementColors[$movement->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $movement->created_at?->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ optional($movement->stockEntry->warehouse)->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $product = optional($movement->stockEntry->product);
                                        @endphp
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name ?? '' }}</p>
                                            @if($product->sku)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-semibold {{ $movement->quantity < 0 ? 'text-error-600 dark:text-error-500' : 'text-success-600 dark:text-success-400' }}">
                                            {{ $movement->quantity < 0 ? '' : '+' }}{{ number_format($movement->quantity, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize {{ $movementColor }}">
                                            {{ $movement->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        @if($movement->order)
                                            <div>
                                                <span class="font-medium">Order #{{ $movement->order->id }}</span>
                                                @if($movement->order->agent)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                                        {{ $movement->order->agent->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            {{ $movement->notes }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Production Runs Section -->
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Production Runs</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Last 10 approved production runs that posted stock to warehouses</p>
            </div>
        </div>

        @if($recentRuns->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white/50 p-8 text-center dark:border-gray-800 dark:bg-gray-900/50">
                <p class="text-sm text-gray-600 dark:text-gray-400">No approved production runs found.</p>
            </div>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Warehouse</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Batch</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Order No.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($recentRuns as $run)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $run->approved_at?->format('d M Y') ?? $run->created_at?->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ optional($run->warehouse)->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $run->product->name ?? '—' }}</p>
                                            @if($run->product?->sku)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $run->product->sku }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $run->batch->batch_code ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-semibold text-success-600 dark:text-success-400">
                                            +{{ number_format($run->quantity, 0) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-gray-600 dark:text-gray-400">
                                            {{ $run->order_number ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
