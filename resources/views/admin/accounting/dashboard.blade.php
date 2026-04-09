@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $collectionRate = $netSales > 0 ? ($collected / $netSales) * 100 : 0;
    $costBase = $totalExpenses + $totalPayroll;
    $cashGap = $collected - $costBase;
    $profitTone = $netProfitEstimate >= 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-error-600 dark:text-error-400';
    $gapTone = $cashGap >= 0
        ? 'text-emerald-600 dark:text-emerald-400'
        : 'text-orange-600 dark:text-orange-400';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="xl:max-w-3xl">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 opacity-20 blur"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L12 3l9 4.5M4.5 9.75V18h15V9.75M9 13.5h6M7.5 18V12h9v6" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Finance & Billing</p>
                    <h1 class="mt-1 bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-3xl font-bold text-transparent dark:from-white dark:to-gray-300">
                        Dashboard
                    </h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Period-based finance snapshot showing invoice-basis billing, cash-basis collections, operating costs, payroll, and management profit estimate.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
            <form method="GET" action="{{ route('admin.accounting.dashboard') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Dashboard period</div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Choose a reporting window for the finance snapshot.</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900 dark:text-gray-200">
                        {{ $rangeOptions[$range] ?? 'Custom range' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Range</span>
                        <select name="range"
                                class="h-12 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($rangeOptions as $value => $label)
                                <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">From</span>
                        <input type="date"
                               name="from"
                               value="{{ request('from', $from->toDateString()) }}"
                               class="h-12 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </label>

                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">To</span>
                        <input type="date"
                               name="to"
                               value="{{ request('to', $to->toDateString()) }}"
                               class="h-12 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </label>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-300">
                        Showing data for <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $periodLabel }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(request()->filled('range') || request()->filled('from') || request()->filled('to'))
                            <a href="{{ route('admin.accounting.dashboard') }}"
                               class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                                Reset
                            </a>
                        @endif
                        <button type="submit"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Apply filters
                        </button>
                    </div>
                </div>
            </form>

            <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-900 to-gray-800 p-6 text-white shadow-theme-xs dark:border-gray-700">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Management estimate</div>
                <div class="mt-3 text-4xl font-semibold">{{ $currencyCode }} {{ number_format($netProfitEstimate, 0) }}</div>
                <p class="mt-2 text-sm text-white/70">
                    Invoiced sales minus period expenses and payroll. This is a management estimate, not a formal statement result.
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-white/60">Operating expenses</div>
                        <div class="mt-2 text-xl font-semibold">{{ $currencyCode }} {{ number_format($totalExpenses, 0) }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-white/60">Payroll</div>
                        <div class="mt-2 text-xl font-semibold">{{ $currencyCode }} {{ number_format($totalPayroll, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Net billed value</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($netSales, 0) }}</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Invoice basis. Net invoice value before VAT for the selected period.</p>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Collected receipts</div>
            <div class="mt-3 text-3xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $currencyCode }} {{ number_format($collected, 0) }}</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Cash basis. Actual receipts received in the selected period.</p>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Open receivables</div>
            <div class="mt-3 text-3xl font-semibold text-orange-600 dark:text-orange-400">{{ $currencyCode }} {{ number_format($outstanding, 0) }}</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Invoice basis. Outstanding after receipts, advances, withholding, and credit notes.</p>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Cash after costs</div>
            <div class="mt-3 text-3xl font-semibold {{ $gapTone }}">{{ $currencyCode }} {{ number_format($cashGap, 0) }}</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Cash basis. Receipts collected minus period expenses and payroll.</p>
        </article>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Invoices issued</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalInvoices) }}</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Count of invoices issued in the selected period.</p>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Collection rate</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($collectionRate, 1) }}%</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Receipts collected compared with net sales on invoice basis.</p>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">VAT on invoices</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($vatTotal, 0) }}</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Invoice basis. VAT attached to invoices issued in this period.</p>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Withholding on invoices</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($withholdingTotal, 0) }}</div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Invoice basis. Expected withholding recorded on issued invoices.</p>
        </article>
    </section>

    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Invoice-basis view</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use this section when you want to understand client billing and receivables rather than cash in bank.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Net billed value</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Invoice value before VAT</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($netSales, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">VAT tracked</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Invoice VAT for the selected period</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($vatTotal, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Withholding tracked</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Expected customer withholding on invoices</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($withholdingTotal, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Outstanding balance</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Receivables still open after receipts and credits</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-orange-600 dark:text-orange-400">{{ $currencyCode }} {{ number_format($outstanding, 0) }}</div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Cash-and-cost view</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use this section when you want to compare actual receipts with money going out in the same period.</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Collected receipts</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cash received in the selected period</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $currencyCode }} {{ number_format($collected, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Operating expenses</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Expenses, gifts, and campaigns recorded in the period</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-error-600 dark:text-error-400">{{ $currencyCode }} {{ number_format($totalExpenses, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Payroll and allowances</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Salary distributions whose payroll period starts in the selected range</div>
                    </div>
                    <div class="text-right text-lg font-semibold text-error-600 dark:text-error-400">{{ $currencyCode }} {{ number_format($totalPayroll, 0) }}</div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 dark:bg-gray-800/60">
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Cash after costs</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Collected cash minus expenses and payroll in the same range</div>
                    </div>
                    <div class="text-right text-lg font-semibold {{ $gapTone }}">{{ $currencyCode }} {{ number_format($cashGap, 0) }}</div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
