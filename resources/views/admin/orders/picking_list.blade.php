@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Picking List
                        </h1>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                            Order #{{ $order->id }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                        <div class="flex items-center gap-1">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $order->agent->name }}</span>
                        </div>
                        <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                        <div class="flex items-center gap-1">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Delivery: {{ optional($order->delivery_date)->format('d M Y') ?? 'TBD' }}
                            </span>
                        </div>
                        @if($order->agent_reference)
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <div class="flex items-center gap-1">
                                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16 4 4 4-4 4 16H7z" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-400">PO: {{ $order->agent_reference }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                @php
                    $statusColors = [
                        'confirmed' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                        'picked' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                        'packed' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                    ];
                    $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                @endphp
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium {{ $statusColor }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $order->status === 'confirmed' ? 'bg-success-500' : ($order->status === 'picked' ? 'bg-brand-500' : 'bg-blue-light-500') }}"></span>
                    Current Status: {{ ucfirst($order->status) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Generated {{ now()->format('d M Y, H:i') }}
                </span>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            @php($canConfirmPicking = auth()->user()?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer']))
            @if($canConfirmPicking && $order->status === 'confirmed')
                <form action="{{ route('admin.orders.status.update', $order) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="picked">
                    <button type="submit" 
                            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Confirm Picking
                    </button>
                </form>
            @endif
            <button type="button" 
                    onclick="window.print()"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
            <a href="{{ route('admin.orders.show', $order) }}" 
               class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Order
            </a>
        </div>
    </div>

    <!-- Picking Instructions Banner -->
    <div class="rounded-2xl border border-brand-200 bg-gradient-to-r from-brand-50 to-white p-5 dark:border-brand-900 dark:from-brand-950/30 dark:to-gray-900">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Picking Instructions</h3>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    Verify each item against the list below. Scan batch codes if applicable. 
                    After completing picking, click "Confirm Picking" to update order status.
                </p>
            </div>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center rounded-full bg-brand-100 px-3 py-1.5 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                    {{ count($lines) }} items to pick
                </span>
            </div>
        </div>
    </div>

    <!-- Picking List Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                        <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Items to Pick</h3>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Warehouse</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Batch</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Pick Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($lines as $index => $line)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $line['sku'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $line['name'] }}</p>
                                    @if(!empty($line['size']))
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $line['size'] }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center justify-center rounded-full bg-brand-50 px-3 py-1.5 text-sm font-bold text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                                    {{ $line['quantity'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($line['warehouse']))
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $line['warehouse'] }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($line['batch']))
                                    <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $line['batch'] }}
                                    </span>
                                    @if(!empty($line['expiry_date']))
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Exp: {{ \Carbon\Carbon::parse($line['expiry_date'])->format('d M Y') }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Pending
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Total Items
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-lg font-bold text-brand-600 dark:text-brand-400">
                                {{ collect($lines)->sum('quantity') }}
                            </span>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Signature Section (Print-only) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-8 print:block">
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="mb-2 h-px w-full bg-gray-300 dark:bg-gray-700"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Picker signature</p>
            <p class="mt-4 text-xs text-gray-400 dark:text-gray-600">Name: _________________</p>
            <p class="mt-2 text-xs text-gray-400 dark:text-gray-600">Date: __________________</p>
        </div>
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="mb-2 h-px w-full bg-gray-300 dark:bg-gray-700"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Checker signature</p>
            <p class="mt-4 text-xs text-gray-400 dark:text-gray-600">Name: _________________</p>
            <p class="mt-2 text-xs text-gray-400 dark:text-gray-600">Date: __________________</p>
        </div>
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="mb-2 h-px w-full bg-gray-300 dark:bg-gray-700"></div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Supervisor signature</p>
            <p class="mt-4 text-xs text-gray-400 dark:text-gray-600">Name: _________________</p>
            <p class="mt-2 text-xs text-gray-400 dark:text-gray-600">Date: __________________</p>
        </div>
    </div>
</div>

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
    .no-print, .sidebar, .header-alert, .header-user, footer,
    button, .flex.items-center.gap-3 a:not(.print\\:block) {
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
    .bg-gradient-to-r, .bg-gradient-to-br {
        background: white !important;
        color: black !important;
    }
    .shadow-lg, .shadow-md, .shadow-sm, .shadow-xs, .shadow-xl {
        box-shadow: none !important;
    }
    .inline-flex.items-center.gap-2.rounded-xl.bg-gradient-to-r {
        background: white !important;
        color: black !important;
        border: 1px solid #e5e7eb !important;
    }
</style>
@endpush
@endsection
