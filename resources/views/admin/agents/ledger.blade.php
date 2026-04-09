@extends('layouts.app')

@section('content')
@php
    $totalDebit = collect($rows)->sum('debit');
    $totalCredit = collect($rows)->sum('credit');
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Agent Ledger
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    {{ $agent->code ?? 'AGENT' }}
                </span>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-lg font-medium text-gray-900 dark:text-white">{{ $agent->name }}</span>
                @if($agent->area || $agent->zone)
                    <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $agent->area ?? '—' }}@if($agent->zone) · Zone {{ $agent->zone }} @endif
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agents.show', $agent) }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Agent Profile
            </a>
            <a href="{{ route('admin.agents.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                All Agents
            </a>
        </div>
    </div>

    <!-- Period Filter Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Date Range</h3>
            </div>
        </div>
        <form method="GET" class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="from" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        From Date
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input id="from" name="from" type="date" value="{{ $from->toDateString() }}"
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>
                <div>
                    <label for="to" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        To Date
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input id="to" name="to" type="date" value="{{ $to->toDateString() }}"
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Period Card -->
        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Period</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}
                    </p>
                </div>
                <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Invoiced (Debit) Card -->
        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoiced</p>
                    <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                        BDT {{ number_format($totalDebit, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total debits</p>
                </div>
                <div class="rounded-lg bg-blue-light-50 p-2.5 dark:bg-blue-light-500/10">
                    <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Credits & Receipts Card -->
        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Receipts</p>
                    <p class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">
                        BDT {{ number_format($totalCredit, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total credits</p>
                </div>
                <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                    <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Closing Balance Card -->
        <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Closing Balance</p>
                    <p class="mt-2 text-xl font-semibold {{ $balance >= 0 ? 'text-gray-900 dark:text-white' : 'text-error-600 dark:text-error-500' }}">
                        BDT {{ number_format($balance, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $balance >= 0 ? 'Outstanding receivable' : 'Credit balance' }}
                    </p>
                </div>
                <div class="rounded-lg {{ $balance >= 0 ? 'bg-orange-50 dark:bg-orange-500/10' : 'bg-error-50 dark:bg-error-500/10' }}">
                    <svg class="h-5 w-5 {{ $balance >= 0 ? 'text-orange-500' : 'text-error-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($balance >= 0)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        @endif
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    @if(empty($rows))
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No ledger activity</h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                No transactions found for the selected period.
                Try adjusting the date range.
            </p>
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Transaction History</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ count($rows) }} {{ Str::plural('transaction', count($rows)) }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Reference</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Credit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @php
                            $runningBalance = 0;
                        @endphp
                        @foreach($rows as $row)
                            @php
                                $runningBalance = $row['balance'];
                                $typeClass = match($row['type']) {
                                    'invoice' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'payment' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'credit_note' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                    'debit_note' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $row['date']->format('Y-m-d') }}
                                    </span>
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $row['date']->format('D') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $row['type'])) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm font-mono text-gray-600 dark:text-gray-400">
                                        {{ $row['ref'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @if($row['debit'] > 0)
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            BDT {{ number_format($row['debit'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    @if($row['credit'] > 0)
                                        <span class="text-sm font-semibold text-success-600 dark:text-success-400">
                                            BDT {{ number_format($row['credit'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <span class="text-sm font-semibold {{ $row['balance'] >= 0 ? 'text-gray-900 dark:text-white' : 'text-error-600 dark:text-error-500' }}">
                                        BDT {{ number_format($row['balance'], 2) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Period Totals
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                BDT {{ number_format($totalDebit, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-success-600 dark:text-success-400">
                                BDT {{ number_format($totalCredit, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold {{ $balance >= 0 ? 'text-gray-900 dark:text-white' : 'text-error-600 dark:text-error-500' }}">
                                BDT {{ number_format($balance, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection