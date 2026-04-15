@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">

    {{-- Welcome banner --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Welcome back, {{ auth()->user()->name }} 👋
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Here's what's happening with your account today.
            </p>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ now()->format('l, d F Y') }}
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Open Orders --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Open Orders</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($openOrders) }}</div>
            <a href="{{ route('portal.orders.index') }}" class="mt-2 inline-flex items-center gap-1 text-xs text-brand-600 hover:underline dark:text-brand-400">
                View orders
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>

        {{-- Active Shipments --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Active Shipments</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/20">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($activeShipments) }}</div>
            <a href="{{ route('portal.shipments.index') }}" class="mt-2 inline-flex items-center gap-1 text-xs text-brand-600 hover:underline dark:text-brand-400">
                Track shipments
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>

        {{-- Delivered This Month --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Delivered This Month</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 dark:bg-green-900/20">
                    <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($deliveredThisMonth) }}</div>
            <a href="{{ route('portal.deliveries.index') }}" class="mt-2 inline-flex items-center gap-1 text-xs text-brand-600 hover:underline dark:text-brand-400">
                View deliveries
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>

        {{-- Outstanding Balance --}}
        <div class="rounded-2xl border {{ $outstanding > 0 ? 'border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/20' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }} p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wide {{ $outstanding > 0 ? 'text-red-500' : 'text-gray-500 dark:text-gray-400' }}">Outstanding Balance</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $outstanding > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-800' }}">
                    <svg class="h-5 w-5 {{ $outstanding > 0 ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold {{ $outstanding > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                {{ $client->currency }} {{ number_format($outstanding, 2) }}
            </div>
            @if($lastReceipt)
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Last payment: {{ $lastReceipt->received_at?->format('d M Y') }}
                </p>
            @else
                <a href="{{ route('portal.invoices.index') }}" class="mt-2 inline-flex items-center gap-1 text-xs text-brand-600 hover:underline dark:text-brand-400">
                    View invoices
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            @endif
        </div>

    </div>

    {{-- Recent Shipments + Recent Invoices --}}
    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Recent Shipments --}}
        <section class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">Recent Shipments</h2>
                <a href="{{ route('portal.shipments.index') }}" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">View all →</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentShipments as $shipment)
                    <a href="{{ route('portal.shipments.show', $shipment) }}"
                       class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/20">
                                <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="truncate font-semibold text-gray-900 dark:text-white">{{ $shipment->shipment_no }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ strtoupper(str_replace('_', ' ', $shipment->mode)) }}</div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                            </span>
                            <div class="mt-1 text-xs text-gray-400">{{ $shipment->created_at?->format('d M Y') }}</div>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center gap-2 px-5 py-10 text-center text-gray-400 dark:text-gray-600">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-sm">No shipments yet</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Recent Invoices --}}
        <section class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">Recent Invoices</h2>
                <a href="{{ route('portal.invoices.index') }}" class="text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">View all →</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentInvoices as $invoice)
                    @php($invoiceCurrencyCode = $invoice->currency_code ?: strtoupper((string) ($client->currency ?: \App\Support\Currency::baseCode())))
                    <a href="{{ route('portal.invoices.show', $invoice) }}"
                       class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/20">
                                <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="truncate font-semibold text-gray-900 dark:text-white">{{ $invoice->number }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ strtoupper($invoice->invoice_type ?? 'STANDARD') }}</div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $invoiceCurrencyCode }} {{ number_format($invoice->cash_total, 2) }}</div>
                            @php
                                $statusColor = match($invoice->status) {
                                    'paid'   => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                    'issued' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                    'overdue'=> 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                    default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                };
                            @endphp
                            <span class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center gap-2 px-5 py-10 text-center text-gray-400 dark:text-gray-600">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        <p class="text-sm">No invoices yet</p>
                    </div>
                @endforelse
            </div>
        </section>

    </div>

</div>
@endsection
