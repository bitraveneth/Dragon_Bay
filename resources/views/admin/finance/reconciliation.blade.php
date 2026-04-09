@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    @php
        $currencyCode = config('app.currency', 'BDT');
        $totalReceipts = $receipts->count();
        $reconciledCount = $receipts->where('reconciled', true)->count();
        $pendingCount = $receipts->where('reconciled', false)->count();
        $totalAmount = $receipts->sum('amount');
        $reconciledAmount = $receipts->where('reconciled', true)->sum('amount');
        $pendingAmount = $receipts->where('reconciled', false)->sum('amount');
    @endphp

    <!-- Header with gradient -->
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between" data-tour="reconciliation-overview-header">
        <div class="xl:max-w-xl">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Bank Reconciliation
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Mark customer receipts as matched to bank statements for 
                        <span class="font-semibold">{{ $from->format('d M Y') }}</span> to 
                        <span class="font-semibold">{{ $to->format('d M Y') }}</span>
                    </p>
                </div>
            </div>
        </div>
        
        <form method="GET" action="{{ route('admin.finance.reconciliation') }}" class="w-full xl:max-w-4xl">
            <div class="rounded-3xl border border-gray-200 bg-white/95 p-4 shadow-theme-xs backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800/85">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Reconciliation period</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                        {{ $rangeOptions[$range] ?? 'Custom range' }}
                    </span>
                </div>

                <div class="grid gap-4 xl:grid-cols-[0.95fr_1.05fr]">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/60">
                        <div class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Quick ranges</div>
                        <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                            <div>
                                <label for="reconciliation-range" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    Range
                                </label>
                                <select
                                    id="reconciliation-range"
                                    name="range"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-950/60 dark:text-white"
                                >
                                    @foreach($rangeOptions as $rangeValue => $rangeLabel)
                                        <option value="{{ $rangeValue }}" @selected($range === $rangeValue)>{{ $rangeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="rounded-xl bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                {{ $rangeOptions[$range] ?? 'Custom range' }}
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/60">
                        <div class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Custom dates</div>
                        <div class="grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                            <div>
                                <label for="reconciliation-from" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    From
                                </label>
                                <input
                                    id="reconciliation-from"
                                    type="date"
                                    name="from"
                                    value="{{ request('from', $from->toDateString()) }}"
                                    class="bank-reconciliation-date-input w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-950/60 dark:text-white"
                                >
                            </div>
                            <div>
                                <label for="reconciliation-to" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    To
                                </label>
                                <input
                                    id="reconciliation-to"
                                    type="date"
                                    name="to"
                                    value="{{ request('to', $to->toDateString()) }}"
                                    class="bank-reconciliation-date-input w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-950/60 dark:text-white"
                                >
                            </div>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center self-end rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600"
                            >
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Status Message -->

    @if($receipts->isEmpty())
        <!-- Empty State -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm p-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 opacity-20 dark:from-gray-900 dark:to-gray-800 blur-3xl"></div>
            
            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-500 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">No Receipts Found</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    No customer receipts were recorded during this period.
                </p>
            </div>
        </div>
    @else
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Receipts</span>
                        <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalReceipts }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total transactions</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Reconciled</span>
                        <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                            <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-success-600 dark:text-success-400">{{ $reconciledCount }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $currencyCode }} {{ number_format($reconciledAmount, 0) }}
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6v6l4 2" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending</span>
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $pendingCount }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $currencyCode }} {{ number_format($pendingAmount, 0) }}
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Value</span>
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <svg class="h-4 w-4 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($totalAmount, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $totalReceipts > 0 ? round(($reconciledAmount / $totalAmount) * 100) : 0 }}% reconciled
                    </p>
                </div>
            </div>
        </div>

        <!-- Reconciliation Form -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Receipt Reconciliation</h3>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $pendingCount }} pending reconciliation
                    </span>
                </div>
            </div>

            <form action="{{ route('admin.finance.reconciliation.update') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="range" value="{{ $range }}">
                <input type="hidden" name="from" value="{{ $from->toDateString() }}">
                <input type="hidden" name="to" value="{{ $to->toDateString() }}">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 w-12">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" 
                                               id="select-all"
                                               class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500">
                                    </label>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Invoice</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Method</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($receipts as $receipt)
                                @php
                                    $statusColors = [
                                        'reconciled' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                        'pending' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                    ];
                                    $statusColor = $receipt->reconciled ? $statusColors['reconciled'] : $statusColors['pending'];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <input type="hidden" name="visible_receipts[]" value="{{ $receipt->id }}">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                   name="reconciled[]" 
                                                   value="{{ $receipt->id }}"
                                                   @checked($receipt->reconciled)
                                                   class="receipt-checkbox h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-brand-500">
                                        </label>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                                <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $receipt->received_at->format('d M Y') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($receipt->invoice)
                                            <div>
                                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $receipt->invoice->number }}
                                                </span>
                                                @if($receipt->invoice->order?->agent)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $receipt->invoice->order->agent->name }}
                                                    </p>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $currencyCode }} {{ number_format($receipt->amount, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($receipt->payment_method)
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium capitalize text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                                {{ str_replace('_', ' ', $receipt->payment_method) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $receipt->reconciled ? 'bg-success-500' : 'bg-orange-500' }}"></span>
                                            {{ $receipt->reconciled ? 'Reconciled' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($totalReceipts > 0)
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span id="selected-count" class="text-sm text-gray-600 dark:text-gray-400">
                                0 of {{ $totalReceipts }} receipts selected
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Reconciliation
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <!-- Reconciliation Summary -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/30">
                    <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">About Bank Reconciliation</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Use the checkboxes to match or unmatch receipts against the current bank statement for this period.
                    </p>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-3 w-3 rounded-full bg-orange-500"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Pending - Awaiting bank confirmation</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-3 w-3 rounded-full bg-success-500"></span>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Reconciled - Matched with bank statement</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .bank-reconciliation-date-input {
        color-scheme: dark;
    }

    .bank-reconciliation-date-input::-webkit-calendar-picker-indicator {
        filter: invert(1);
        opacity: 0.78;
        cursor: pointer;
    }

    .bank-reconciliation-date-input::-webkit-datetime-edit,
    .bank-reconciliation-date-input::-webkit-datetime-edit-fields-wrapper,
    .bank-reconciliation-date-input::-webkit-datetime-edit-text,
    .bank-reconciliation-date-input::-webkit-datetime-edit-month-field,
    .bank-reconciliation-date-input::-webkit-datetime-edit-day-field,
    .bank-reconciliation-date-input::-webkit-datetime-edit-year-field {
        color: inherit;
    }

    html:not(.dark) .bank-reconciliation-date-input {
        color-scheme: light;
    }

    html:not(.dark) .bank-reconciliation-date-input::-webkit-calendar-picker-indicator {
        filter: none;
        opacity: 0.72;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const receiptCheckboxes = document.querySelectorAll('.receipt-checkbox');
        const selectedCountSpan = document.getElementById('selected-count');
        const fromInput = document.getElementById('reconciliation-from');
        const toInput = document.getElementById('reconciliation-to');
        const customRangeInput = document.querySelector('input[name="range"][value="custom"]');

        function updateSelectedCount() {
            if (selectedCountSpan) {
                const checkedCount = document.querySelectorAll('.receipt-checkbox:checked').length;
                const totalReceipts = {{ $totalReceipts ?? 0 }};
                selectedCountSpan.textContent = `${checkedCount} of ${totalReceipts} receipts selected`;
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                receiptCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateSelectedCount();
            });
        }

        receiptCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        if (fromInput && toInput && customRangeInput) {
            const markCustomRange = function() {
                customRangeInput.checked = true;
            };

            fromInput.addEventListener('change', markCustomRange);
            toInput.addEventListener('change', markCustomRange);
        }

        // Initial count update
        updateSelectedCount();
    });
</script>
@endpush
@endsection
