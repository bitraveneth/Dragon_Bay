@extends('layouts.app')

@section('content')
@php
    $collectionRate = $grossRevenue > 0 ? ($totalCollections / $grossRevenue) * 100 : 0;
    $cogsShare = $grossRevenue > 0 ? ($cogsEstimate / $grossRevenue) * 100 : 0;
    $commissionShare = $grossRevenue > 0 ? ($commissionsTotal / $grossRevenue) * 100 : 0;
    $expenseShare = $grossRevenue > 0 ? ($totalExpenses / $grossRevenue) * 100 : 0;
    $payrollShare = $grossRevenue > 0 ? ($totalPayroll / $grossRevenue) * 100 : 0;
    $profitShare = $grossRevenue > 0 ? ($netProfitEstimate / $grossRevenue) * 100 : 0;
    $currencyCode = config('app.currency', 'BDT');
@endphp

<div class="space-y-8">
    <section class="relative overflow-hidden rounded-[2rem] border border-gray-200 bg-white px-6 py-7 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:px-8">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.15),_transparent_28%),radial-gradient(circle_at_bottom_left,_rgba(16,185,129,0.12),_transparent_30%)]"></div>

        <div class="relative flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300">
                    Reports Command Center
                </div>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white md:text-4xl">
                    Reports Dashboard
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400 md:text-base">
                    Track year-to-date revenue, collections, liabilities, production output, and commercial reach from one place.
                </p>
            </div>

            <div class="flex w-full max-w-xl flex-col gap-3 xl:w-auto xl:min-w-[30rem]">
                <div class="flex justify-end">
                    @include('admin.finance.partials.print_button', ['label' => 'Print Dashboard'])
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-950/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Period</div>
                        <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $yearLabel }}</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-950/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Collection rate</div>
                        <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($collectionRate, 1) }}%</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-950/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Active agents</div>
                        <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($activeAgents) }}</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-950/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Approved output</div>
                        <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($productionQty) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[1.3fr_0.9fr]">
        <div class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:p-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Commercial snapshot</p>
                    <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Revenue and receivables</h2>
                </div>
                <div class="rounded-2xl bg-brand-50 px-3 py-2 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    Year-to-date
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-4">
                <article class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-950/60">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Revenue</div>
                    <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($grossRevenue, 0) }}</div>
                    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">Net invoiced revenue recorded this year.</p>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-950/60">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Collections</div>
                    <div class="mt-3 text-3xl font-semibold text-success-600 dark:text-success-400">{{ $currencyCode }} {{ number_format($totalCollections, 0) }}</div>
                    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">Cash already collected against invoices.</p>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-950/60">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Outstanding</div>
                    <div class="mt-3 text-3xl font-semibold text-orange-600 dark:text-orange-400">{{ $currencyCode }} {{ number_format($outstanding, 0) }}</div>
                    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">Open receivables after receipts, credit notes, and withholding.</p>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-950/60">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Withholding</div>
                    <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($withholdingTotal, 0) }}</div>
                    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">Retention deducted from invoice settlement value.</p>
                </article>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Collection efficiency</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($collectionRate, 1) }}%</span>
                    </div>
                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full rounded-full bg-success-500" style="width: {{ min(max($collectionRate, 0), 100) }}%"></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Receivable pressure</div>
                    <div class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $grossRevenue > 0 ? number_format(($outstanding / $grossRevenue) * 100, 1) : '0.0' }}%
                    </div>
                    <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Share of revenue still open as receivable.</p>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Commercial footprint</div>
                    <div class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($activeAgents) }} active partners</div>
                    <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Current selling network feeding orders into the system.</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:p-7">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Cost structure</p>
            <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Profit pulse</h2>

            <div class="mt-6 rounded-3xl bg-gray-950 px-6 py-7 text-white dark:bg-black">
                <div class="text-xs font-semibold uppercase tracking-[0.16em] text-white/60">Estimated net result</div>
                <div class="mt-3 text-4xl font-semibold {{ $netProfitEstimate >= 0 ? 'text-success-400' : 'text-error-400' }}">
                    {{ $currencyCode }} {{ number_format($netProfitEstimate, 0) }}
                </div>
                <p class="mt-3 text-sm leading-6 text-white/65">
                    Approximate profit after COGS, commissions, operating expenses, and payroll, based on year-to-date revenue.
                </p>
            </div>

            <div class="mt-5 space-y-4">
                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">COGS estimate</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Production material cost snapshot applied to invoiced quantities</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-error-600 dark:text-error-400">{{ $currencyCode }} {{ number_format($cogsEstimate, 0) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($cogsShare, 1) }}% of revenue</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Commission expense</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ledger-posted commission accruals and settlements</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-error-600 dark:text-error-400">{{ $currencyCode }} {{ number_format($commissionsTotal, 0) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($commissionShare, 1) }}% of revenue</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Operating expenses</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Expense module spend this year</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-error-600 dark:text-error-400">{{ $currencyCode }} {{ number_format($totalExpenses, 0) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($expenseShare, 1) }}% of revenue</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Payroll and allowances</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Salary distributions and related payouts</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-error-600 dark:text-error-400">{{ $currencyCode }} {{ number_format($totalPayroll, 0) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($payrollShare, 1) }}% of revenue</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Profit margin indicator</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Revenue less COGS, commissions, operating expenses, and payroll</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold {{ $profitShare >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                                {{ number_format($profitShare, 1) }}%
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Revenue less tracked costs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:p-7">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Operations</p>
                    <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Factory and field activity</h2>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <article class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-950/60">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Approved output</div>
                    <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($productionQty, 0) }}</div>
                    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">Total quantity from approved production runs in the current year.</p>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 dark:border-gray-800 dark:bg-gray-950/60">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">Agent network</div>
                    <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($activeAgents, 0) }}</div>
                    <p class="mt-2 text-xs leading-5 text-gray-500 dark:text-gray-400">Partner base currently feeding market-side demand into the ERP.</p>
                </article>
            </div>
        </div>

        <div class="rounded-[2rem] border border-dashed border-gray-300 bg-white/70 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900/60 md:p-7" data-tour="reports-drilldown">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Drill down</p>
            <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Open detailed statements</h2>
            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                Use the dedicated reports for finance-grade detail when you need formal statements, reconciliation support, or period-specific analysis.
            </p>

            <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.reports.pl') }}" class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                    Profit &amp; Loss
                </a>
                <a href="{{ route('admin.reports.bs') }}" class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                    Balance sheet
                </a>
                <a href="{{ route('admin.reports.cashflow') }}" class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                    Cashflow
                </a>
                <a href="{{ route('admin.reports.vat') }}" class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                    Tax report
                </a>
                <a href="{{ route('admin.reports.agents') }}" class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                    Agent performance
                </a>
                <a href="{{ route('admin.reports.production') }}" class="rounded-2xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-800 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300">
                    Production reports
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
