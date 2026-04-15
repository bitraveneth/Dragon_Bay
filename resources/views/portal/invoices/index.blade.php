@extends('layouts.app')

@section('title', 'Client Invoices')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Invoices</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Review issued invoices, payments, and outstanding balances.</p>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Issued</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Outstanding</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="px-4 py-3"><a href="{{ route('portal.invoices.show', $invoice) }}" class="font-medium text-brand-600 dark:text-brand-300">{{ $invoice->number }}</a></td>
                            <td class="px-4 py-3">{{ $invoice->issued_at?->format('d M Y') ?: '-' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($invoice->status) }}</td>
                            <td class="px-4 py-3 text-right">BDT {{ number_format($invoice->cash_total, 2) }}</td>
                            <td class="px-4 py-3 text-right">BDT {{ number_format($invoice->outstanding, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $invoices->links() }}
</div>
@endsection
