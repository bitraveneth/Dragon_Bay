@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Purchase Orders</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Procurement planning and approval before GRN posting.</p>
        </div>
        <a href="{{ route('admin.purchase-orders.create') }}" data-tour="purchase-orders-primary-action" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">New PO</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">PO</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Order Date</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Lines</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($orders as $order)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $order->number }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $order->supplier->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ optional($order->order_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $order->items->count() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.purchase-orders.show', $order) }}" class="text-sm font-medium text-brand-500 hover:text-brand-600">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No purchase orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</div>
@endsection
