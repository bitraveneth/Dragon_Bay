@extends('layouts.portal')

@section('title', $invoice->number)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $invoice->number }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($invoice->status) }} • {{ strtoupper($invoice->invoice_type ?? 'STANDARD') }}</p>
        </div>
        <a href="{{ route('portal.invoices.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</a>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Issued</div><div class="mt-2 text-lg font-semibold">{{ $invoice->issued_at?->format('d M Y') ?: '-' }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Due</div><div class="mt-2 text-lg font-semibold">{{ $invoice->due_at?->format('d M Y') ?: '-' }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Cash Total</div><div class="mt-2 text-lg font-semibold">BDT {{ number_format($invoice->cash_total, 2) }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Outstanding</div><div class="mt-2 text-lg font-semibold">BDT {{ number_format($invoice->outstanding, 2) }}</div></div>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold">Invoice Items</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-gray-500">
                    <tr>
                        <th class="py-3 text-left">Description</th>
                        <th class="py-3 text-right">Qty</th>
                        <th class="py-3 text-right">Unit Price</th>
                        <th class="py-3 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="py-3">{{ $item->description }}</td>
                            <td class="py-3 text-right">{{ number_format((float) $item->quantity) }}</td>
                            <td class="py-3 text-right">BDT {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="py-3 text-right">BDT {{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold">Receipts</h2>
            <div class="mt-4 space-y-3">
                @forelse($invoice->receipts as $receipt)
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm dark:border-gray-800">
                        <div>{{ $receipt->received_at?->format('d M Y') ?: '-' }}</div>
                        <div>BDT {{ number_format((float) $receipt->amount, 2) }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No receipts recorded.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold">Credit Notes</h2>
            <div class="mt-4 space-y-3">
                @forelse($invoice->creditNotes as $credit)
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-sm dark:border-gray-800">
                        <div>{{ $credit->number ?: 'Credit note' }}</div>
                        <div>BDT {{ number_format((float) $credit->amount, 2) }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No credit notes recorded.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
