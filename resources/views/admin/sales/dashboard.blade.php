@extends('layouts.app')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l4-4 3 3 4-7" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Orders dashboard
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Current month order performance across invoices, clients, and catalog items.
                    </p>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Period: {{ $periodLabel }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 4h14v2H5zM5 9h10v2H5zM5 14h7v2H5z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total invoices</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalInvoices) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Issued this month</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 7h16v2H4zM4 11h16v2H4zM4 15h16v2H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Net billed value (BDT)</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($netSales, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    VAT: {{ number_format($vatTotal, 0) }} · Withholding: {{ number_format($withholdingTotal, 0) }}
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 6h16v12H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Collected (BDT)</p>
                <p class="mt-3 text-3xl font-bold text-success-600 dark:text-success-400">
                    {{ number_format($collected, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Client receipts received this month
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding (BDT)</p>
                <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">
                    {{ number_format($outstanding, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Net of VAT, withholding, receipts &amp; credit notes
                </p>
            </div>
        </div>
    </div>

    {{-- Grids --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Top clients --}}
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top clients by billed value</h2>
            @if($topAgents->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No client billing data for this period.</p>
            @else
                <ul class="space-y-3">
                    @foreach($topAgents as $agent)
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $agent['agent_name'] }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($agent['net_sales'], 0) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Top catalog items --}}
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top catalog items</h2>
            @if($topProducts->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No catalog activity for this period.</p>
            @else
                <ul class="space-y-3">
                    @foreach($topProducts as $product)
                        <li class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $product['product_name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Qty: {{ number_format($product['qty'], 0) }}
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($product['net'], 0) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Recent client orders --}}
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Recent client orders</h2>
            @if($recentOrders->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No recent orders found.</p>
            @else
                <ul class="space-y-3">
                    @foreach($recentOrders as $order)
                        <li class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    #{{ $order->id }} · {{ $order->agent?->name ?? 'Unknown client' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ optional($order->delivery_date)->format('d M Y') ?? $order->created_at->format('d M Y') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300 capitalize">
                                {{ $order->status }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
