@extends('layouts.portal')

@section('title', 'Client Orders')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Orders</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View staff-created orders and fulfillment progress.</p>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Order</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Delivery Date</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($orders as $order)
                        <tr>
                            <td class="px-4 py-3"><a href="{{ route('portal.orders.show', $order) }}" class="font-medium text-brand-600 dark:text-brand-300">Order #{{ $order->id }}</a></td>
                            <td class="px-4 py-3">{{ ucwords($order->status) }}</td>
                            <td class="px-4 py-3">{{ $order->delivery_date?->format('d M Y') ?: '-' }}</td>
                            <td class="px-4 py-3 text-right">BDT {{ number_format((float) $order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $orders->links() }}
</div>
@endsection
