@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Cashflow
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <!-- Date Range Display -->
            <div class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $from->format('M d, Y') }} - {{ $to->format('M d, Y') }}
                </span>
            </div>
            @include('admin.finance.partials.print_button', ['label' => 'Print Report'])
        </div>
    </div>

    <!-- Cashflow Card -->
    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
        <!-- Decorative background -->
        <div class="absolute right-0 top-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-64 w-64 rounded-full bg-gradient-to-br from-success-100 to-success-50 opacity-20 dark:from-success-900 dark:to-success-800 blur-3xl"></div>
        
        <div class="relative p-8">
            <!-- Cashflow Visualization -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cash Inflows -->
                <div class="flex flex-col items-center p-6 rounded-2xl bg-gradient-to-br from-success-50 to-white dark:from-success-950/30 dark:to-gray-900 border border-success-100 dark:border-success-900/30">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-success-100 dark:bg-success-900/30 mb-4">
                        <svg class="h-8 w-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Cash Inflows</p>
                    <p class="text-3xl font-bold text-success-600 dark:text-success-400">
                        +{{ number_format($cashIn, 2) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Bank debits / Receipts
                    </p>
                </div>

                <!-- Cash Outflows -->
                <div class="flex flex-col items-center p-6 rounded-2xl bg-gradient-to-br from-orange-50 to-white dark:from-orange-950/30 dark:to-gray-900 border border-orange-100 dark:border-orange-900/30">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30 mb-4">
                        <svg class="h-8 w-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Cash Outflows</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">
                        -{{ number_format($cashOut, 2) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Bank credits / Payments
                    </p>
                </div>

                <!-- Net Cashflow -->
                <div class="flex flex-col items-center p-6 rounded-2xl bg-gradient-to-br from-brand-50 to-white dark:from-brand-950/30 dark:to-gray-900 border border-brand-100 dark:border-brand-900/30">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/30 mb-4">
                        <svg class="h-8 w-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Net Cashflow</p>
                    <p class="text-3xl font-bold {{ $net >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-500' }}">
                        {{ $net >= 0 ? '+' : '-' }}{{ number_format(abs($net), 2) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ $net >= 0 ? 'Positive cashflow' : 'Negative cashflow' }}
                    </p>
                </div>
            </div>

            <!-- Summary Bar -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                            <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Reporting Period</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-success-500"></span>
                            <span class="text-gray-600 dark:text-gray-400">Inflows: +{{ number_format($cashIn, 2) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                            <span class="text-gray-600 dark:text-gray-400">Outflows: -{{ number_format($cashOut, 2) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $net >= 0 ? 'bg-brand-500' : 'bg-error-500' }}"></span>
                            <span class="text-gray-600 dark:text-gray-400">Net: {{ $net >= 0 ? '+' : '-' }}{{ number_format(abs($net), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cashflow Ratio Card -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Cashflow Ratio -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/30">
                    <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Cashflow Ratio</h3>
            </div>
            @php
                $ratio = $cashOut > 0 ? $cashIn / $cashOut : ($cashIn > 0 ? 999 : 0);
                $ratioColor = $ratio >= 1.5 ? 'text-success-600 dark:text-success-400' : ($ratio >= 1 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-500');
            @endphp
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-3xl font-bold {{ $ratioColor }}">{{ number_format($ratio, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $ratio >= 1.5 ? 'Excellent' : ($ratio >= 1 ? 'Healthy' : 'Needs attention') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Inflows : Outflows</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ number_format($cashIn, 0) }} : {{ number_format($cashOut, 0) }}
                    </p>
                </div>
            </div>
            <div class="mt-4 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                <div class="h-2 rounded-full {{ $ratio >= 1.5 ? 'bg-success-500' : ($ratio >= 1 ? 'bg-brand-500' : 'bg-error-500') }}" 
                     style="width: {{ min($ratio * 50, 100) }}%"></div>
            </div>
        </div>

        <!-- Period Summary -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                    <svg class="h-5 w-5 text-gray-700 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Period Summary</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Average daily inflow</span>
                    @php
                        $days = max(1, $from->diffInDays($to) + 1);
                        $avgIn = $cashIn / $days;
                        $avgOut = $cashOut / $days;
                    @endphp
                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">+{{ number_format($avgIn, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Average daily outflow</span>
                    <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">-{{ number_format($avgOut, 2) }}</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Net daily average</span>
                    <span class="text-sm font-bold {{ $net >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-500' }}">
                        {{ $net >= 0 ? '+' : '-' }}{{ number_format(abs($net) / $days, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/30">
                <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">About Cashflow</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Cash inflows represent money received from customers (receipts), while cash outflows represent 
                    payments made to suppliers, employees, and other expenses. A positive net cashflow indicates 
                    more money came in than went out during this period.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
