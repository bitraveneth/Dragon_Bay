@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Packing Slip
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Delivery #{{ $delivery->id }}
                </span>
            </div>
            <div class="mt-2 space-y-1">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Order #{{ $delivery->order->id }} · {{ $delivery->order->agent->name }}
                </p>
                @php
                    $order = $delivery->order;
                    $agent = $order?->agent;
                    $deliveryDate = $order?->delivery_date ?? $delivery->created_at;
                @endphp
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ optional($deliveryDate)->format('d M Y') }}
                    </span>
                    <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Status: 
                        @php
                            $statusColors = [
                                'scheduled' => 'text-gray-600 dark:text-gray-400',
                                'in_transit' => 'text-blue-light-600 dark:text-blue-light-400',
                                'delivered' => 'text-success-600 dark:text-success-400',
                                'exception' => 'text-error-600 dark:text-error-400',
                            ];
                            $statusColor = $statusColors[$delivery->status] ?? 'text-gray-600 dark:text-gray-400';
                        @endphp
                        <span class="font-medium {{ $statusColor }}">
                            {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                        </span>
                    </span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            <a href="{{ route('admin.deliveries.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Deliveries
            </a>
            @if(Route::has('admin.deliveries.destroy'))
            <form action="{{ route('admin.deliveries.destroy', $delivery) }}" 
                  method="POST" 
                  onsubmit="return confirm('Delete delivery #{{ $delivery->id }}? This action cannot be undone.');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Delivery
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Customer & Delivery Info Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <!-- Ship To Card -->
        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-2 mb-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Ship To</h3>
            </div>
            <div class="space-y-1.5 text-sm">
                <p class="font-medium text-gray-900 dark:text-white">{{ $agent->name ?? '—' }}</p>
                @if($agent?->area)
                    <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        Area: {{ $agent->area }}
                    </p>
                @endif
                @if($agent?->zone)
                    <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2 2 2 2-2 2 2 2-2 2 2 2-2 2 2"/>
                        </svg>
                        Zone: {{ $agent->zone }}
                    </p>
                @endif
                @if($agent?->phone)
                    <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Phone: {{ $agent->phone }}
                    </p>
                @endif
                @if($agent?->location_code)
                    <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Code: {{ $agent->location_code }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Route & Vehicle Card -->
        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-2 mb-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Route & Vehicle</h3>
            </div>
            <div class="space-y-1.5 text-sm">
                <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Route:</span>
                    {{ $delivery->route->name ?? '—' }}
                </p>
                <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Vehicle:</span>
                    {{ $delivery->vehicle->name ?? '—' }}
                    @if($delivery->vehicle?->license_plate)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            ({{ $delivery->vehicle->license_plate }})
                        </span>
                    @endif
                </p>
                <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Driver:</span>
                    {{ $delivery->vehicle->driver ?? $delivery->route->driver ?? '—' }}
                </p>
                @if($delivery->sequence)
                    <p class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Stop #:</span>
                        <span class="font-mono font-medium text-gray-900 dark:text-white">{{ $delivery->sequence }}</span>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Items to Pack Table -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Items to Pack</h2>
            @php
                $totalQty = 0;
            @endphp
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Pack</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($delivery->order->items as $item)
                            @php
                                $totalQty += $item->quantity;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $item->product->sku ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $item->product->name ?? '—' }}
                                        </p>
                                        @if($item->product?->size)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Size: {{ $item->product->size }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->product?->packagingType?->name)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            {{ $item->product->packagingType->name }}
                                        </span>
                                    @elseif($item->product?->size)
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $item->product->size }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total Items
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                                {{ $totalQty }}
                            </td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mt-8">
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="mb-2 h-px w-full bg-gray-300 dark:bg-gray-700"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Picker signature</p>
        </div>
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="mb-2 h-px w-full bg-gray-300 dark:bg-gray-700"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Checker signature</p>
        </div>
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="mb-2 h-px w-full bg-gray-300 dark:bg-gray-700"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Driver signature</p>
        </div>
    </div>

    <!-- Print Styles -->
    @push('styles')
    <style media="print">
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            background: white;
            color: black;
        }
        .no-print, .sidebar, .header-alert, .header-user, footer {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        .rounded-2xl, .rounded-xl, .rounded-lg {
            border: 1px solid #e5e7eb !important;
            box-shadow: none !important;
        }
        .bg-white, .bg-gray-50, .bg-gray-100 {
            background: white !important;
        }
        .dark\:bg-gray-900, .dark\:bg-gray-800 {
            background: white !important;
        }
        .text-gray-900, .text-gray-700, .text-gray-600 {
            color: black !important;
        }
        .border-gray-200, .border-gray-300 {
            border-color: #e5e7eb !important;
        }
    </style>
    @endpush
</div>
@endsection