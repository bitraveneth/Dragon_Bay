@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $receipt->grn_number }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $receipt->supplier->name ?? '—' }} · {{ $receipt->warehouse->name ?? '—' }} · {{ optional($receipt->received_at)->format('d M Y H:i') }} · {{ ucfirst($receipt->status) }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($receipt->status === 'posted')
                <form action="{{ route('admin.goods-receipts.reverse', $receipt) }}" method="POST" onsubmit="return confirm('Reverse {{ $receipt->grn_number }}? This will remove its stock only if none of it has been used, transferred, reserved, or adjusted.');">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg border border-error-300 px-4 py-2 text-sm font-medium text-error-700 hover:bg-error-50 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10">Reverse GRN</button>
                </form>
            @endif
            <a href="{{ route('admin.goods-receipts.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Back</a>
        </div>
    </div>

    @if(!empty($reversalBlockers))
        <div class="rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">
            <div class="font-semibold">This GRN cannot be reversed right now.</div>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($reversalBlockers as $blocker)
                    <li>{{ $blocker }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Qty</th>
                    <th class="px-4 py-3">QC</th>
                    <th class="px-4 py-3">Batch</th>
                    <th class="px-4 py-3">Location</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($receipt->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $item->product->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->quantity, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($item->qc_status) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->batch->batch_code ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->location->code ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
