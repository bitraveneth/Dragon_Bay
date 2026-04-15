@extends('layouts.app')

@section('title', 'Client Statement')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Statement</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Invoices, receipts, and credit notes for the selected period.</p>
        </div>
        <form method="GET" class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-end">
            <div>
                <label class="mb-1 block text-xs text-gray-500">From</label>
                <input type="date" name="from" value="{{ $fromDate->format('Y-m-d') }}" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500">To</label>
                <input type="date" name="to" value="{{ $toDate->format('Y-m-d') }}" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Apply</button>
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="text-xs uppercase text-gray-500">Closing Balance</div>
        <div class="mt-2 text-2xl font-semibold">BDT {{ number_format($balance, 2) }}</div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4 py-3">{{ $row['date'] ?: '-' }}</td>
                            <td class="px-4 py-3">{{ ucwords(str_replace('_', ' ', $row['type'])) }}</td>
                            <td class="px-4 py-3">{{ $row['reference'] ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $row['description'] }}</td>
                            <td class="px-4 py-3 text-right {{ $row['amount'] < 0 ? 'text-green-600 dark:text-green-400' : '' }}">BDT {{ number_format($row['amount'], 2) }}</td>
                            <td class="px-4 py-3 text-right">BDT {{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No statement activity found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
