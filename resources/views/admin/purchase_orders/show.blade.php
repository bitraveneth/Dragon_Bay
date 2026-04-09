@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $order->number }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->supplier->name ?? '—' }} · {{ optional($order->order_date)->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($order->status === 'draft')
                <form method="POST" action="{{ route('admin.purchase-orders.approve', $order) }}">
                    @csrf
                    <button class="rounded-lg bg-success-500 px-4 py-2 text-sm font-semibold text-white">Approve PO</button>
                </form>
            @endif
            <a href="{{ route('admin.goods-receipts.create', ['purchase_order_id' => $order->id]) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Create GRN</a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Qty</th>
                    <th class="px-4 py-3">Received</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($order->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $item->description }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->product->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->quantity, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->received_quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Linked GRNs</h3>
        <div class="mt-2 space-y-2">
            @forelse($order->goodsReceipts as $grn)
                <a class="block text-sm text-brand-500 hover:text-brand-600" href="{{ route('admin.goods-receipts.show', $grn) }}">{{ $grn->grn_number }} · {{ optional($grn->received_at)->format('d M Y H:i') }}</a>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No GRN linked yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
