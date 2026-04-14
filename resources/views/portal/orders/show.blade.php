@extends('layouts.portal')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Order #{{ $order->id }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ ucwords($order->status) }} • {{ strtoupper($order->order_type) }}</p>
        </div>
        <a href="{{ route('portal.orders.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</a>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Delivery Date</div><div class="mt-2 text-lg font-semibold">{{ $order->delivery_date?->format('d M Y') ?: '-' }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Total</div><div class="mt-2 text-lg font-semibold">BDT {{ number_format((float) $order->total, 2) }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Shipment</div><div class="mt-2 text-lg font-semibold">{{ $order->shipment?->shipment_no ?: 'Pending' }}</div></div>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold">Order Items</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-gray-500">
                    <tr>
                        <th class="py-3 text-left">Product</th>
                        <th class="py-3 text-right">Qty</th>
                        <th class="py-3 text-right">Unit Price</th>
                        <th class="py-3 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="py-3">{{ $item->product?->name ?: 'Product #' . $item->product_id }}</td>
                            <td class="py-3 text-right">{{ number_format((float) $item->quantity) }}</td>
                            <td class="py-3 text-right">BDT {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="py-3 text-right">BDT {{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold">Status History</h2>
        <div class="mt-4 space-y-3">
            @foreach($order->statusHistory as $entry)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm dark:border-gray-800">
                    <div>{{ ucwords($entry->status) }}</div>
                    <div class="text-gray-500 dark:text-gray-400">{{ $entry->changed_at?->format('d M Y H:i') }}</div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
