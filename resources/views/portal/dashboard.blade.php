@extends('layouts.portal')

@section('title', 'Client Portal Dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-semibold">Overview</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track shipments, invoices, and account activity.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">Open Orders</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($openOrders) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">Active Shipments</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($activeShipments) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">Delivered This Month</div>
            <div class="mt-2 text-2xl font-semibold">{{ number_format($deliveredThisMonth) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">Outstanding Balance</div>
            <div class="mt-2 text-2xl font-semibold">BDT {{ number_format($outstanding, 2) }}</div>
            @if($lastReceipt)
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Last receipt: {{ $lastReceipt->received_at?->format('d M Y') }}</div>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Recent Shipments</h2>
                <a href="{{ route('portal.shipments.index') }}" class="text-sm text-brand-600 dark:text-brand-300">View all</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($recentShipments as $shipment)
                    <a href="{{ route('portal.shipments.show', $shipment) }}" class="block rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $shipment->shipment_no }}</div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ strtoupper(str_replace('_', ' ', $shipment->mode)) }}</div>
                            </div>
                            <div class="text-right text-sm">
                                <div>{{ ucwords(str_replace('_', ' ', $shipment->status)) }}</div>
                                <div class="mt-1 text-gray-500 dark:text-gray-400">{{ $shipment->created_at?->format('d M Y') }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No shipments yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Recent Invoices</h2>
                <a href="{{ route('portal.invoices.index') }}" class="text-sm text-brand-600 dark:text-brand-300">View all</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($recentInvoices as $invoice)
                    <a href="{{ route('portal.invoices.show', $invoice) }}" class="block rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $invoice->number }}</div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ strtoupper($invoice->invoice_type ?? 'STANDARD') }}</div>
                            </div>
                            <div class="text-right text-sm">
                                <div>BDT {{ number_format($invoice->cash_total, 2) }}</div>
                                <div class="mt-1 text-gray-500 dark:text-gray-400">{{ ucfirst($invoice->status) }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No invoices yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
