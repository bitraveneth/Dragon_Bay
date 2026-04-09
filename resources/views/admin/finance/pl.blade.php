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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Profit & Loss Statement
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <!-- Date Range Navigation -->
            <div class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $from->format('M d, Y') }} - {{ $to->format('M d, Y') }}
                </span>
            </div>
            @include('admin.finance.partials.print_button', ['label' => 'Print Statement'])
        </div>
    </div>

    <!-- P&L Statement Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-8 py-6 dark:border-gray-800 dark:from-gray-900/50 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Income Statement</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        For the period ending {{ $to->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- P&L Table -->
        <div class="p-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        <!-- Revenue Section -->
                        <tr class="bg-gray-50/50 dark:bg-gray-800/30">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white" colspan="2">
                                Revenue
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 pl-12">
                                Sales revenue
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-success-600 dark:text-success-400">
                                +{{ number_format($sales, 2) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 pl-12">
                                Sales returns
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-error-600 dark:text-error-500">
                                -{{ number_format($returns, 2) }}
                            </td>
                        </tr>
                        <tr class="border-t-2 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white pl-12">
                                Net sales
                            </td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-gray-900 dark:text-white">
                                {{ number_format($netSales, 2) }}
                            </td>
                        </tr>

                        <!-- Cost of Goods Sold -->
                        <tr class="bg-gray-50/50 dark:bg-gray-800/30">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white" colspan="2">
                                Cost of Goods Sold
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 pl-12">
                                Material costs
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-error-600 dark:text-error-500">
                                -{{ number_format($cogs ?? 0, 2) }}
                            </td>
                        </tr>

                        <!-- Gross Profit -->
                        <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-brand-50/30 dark:bg-brand-500/5">
                            <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white pl-12">
                                Gross profit
                            </td>
                            <td class="px-6 py-4 text-right text-lg font-bold {{ ($grossProfit ?? 0) >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                                {{ ($grossProfit ?? 0) >= 0 ? '+' : '-' }}{{ number_format(abs($grossProfit ?? 0), 2) }}
                            </td>
                        </tr>

                        <!-- Operating Expenses -->
                        <tr class="bg-gray-50/50 dark:bg-gray-800/30">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white" colspan="2">
                                Operating Expenses
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 pl-12">
                                Commission expense
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-error-600 dark:text-error-500">
                                -{{ number_format($commissions, 2) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 pl-12">
                                Other operating expenses
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-error-600 dark:text-error-500">
                                -{{ number_format($otherExpenses ?? 0, 2) }}
                            </td>
                        </tr>

                        <!-- Net Profit -->
                        <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-success-50/30 dark:bg-success-500/5">
                            <td class="px-6 py-4 text-base font-bold text-gray-900 dark:text-white pl-12">
                                Net Profit {{ $profit < 0 ? '(Loss)' : '' }}
                            </td>
                            <td class="px-6 py-4 text-right text-xl font-bold {{ $profit >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                                {{ $profit >= 0 ? '+' : '-' }}{{ number_format(abs($profit), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 pt-6 border-t border-gray-200 dark:border-gray-800">
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/30">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Gross Margin</p>
                    <p class="mt-1 text-2xl font-bold {{ ($grossProfit ?? 0) >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                        {{ $netSales > 0 ? number_format(($grossProfit / $netSales) * 100, 1) : 0 }}%
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">of net sales</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/30">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Net Margin</p>
                    <p class="mt-1 text-2xl font-bold {{ $profit >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                        {{ $netSales > 0 ? number_format(($profit / $netSales) * 100, 1) : 0 }}%
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">of net sales</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/30">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Operating Ratio</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $netSales > 0 ? number_format((($cogs ?? 0) + $commissions + ($otherExpenses ?? 0)) / $netSales * 100, 1) : 0 }}%
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">expenses to sales</p>
                </div>
            </div>

            <!-- Key Performance Indicators -->
            <div class="mt-6 flex flex-wrap items-center gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-success-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Revenue: BDT {{ number_format($sales, 2) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-error-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Returns: BDT {{ number_format($returns, 2) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">COGS: BDT {{ number_format($cogs ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-brand-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Commission: BDT {{ number_format($commissions, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed ledger breakdown -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
            Detailed ledger by account
        </h3>
        @if(isset($accountRows) && $accountRows->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left">Account</th>
                            <th class="px-3 py-2 text-right">Debits</th>
                            <th class="px-3 py-2 text-right">Credits</th>
                            <th class="px-3 py-2 text-right">Net (credit - debit)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($accountRows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-3 py-2 text-gray-800 dark:text-gray-100">
                                    {{ $row['account'] }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">
                                    BDT {{ number_format($row['debit'], 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">
                                    BDT {{ number_format($row['credit'], 2) }}
                                </td>
                                <td class="px-3 py-2 text-right {{ $row['net'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500' }}">
                                    {{ $row['net'] >= 0 ? '+' : '-' }}BDT {{ number_format(abs($row['net']), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No ledger entries found for this period.
            </p>
        @endif
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
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">About this report</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    This Profit & Loss statement summarizes revenues, costs, and expenses incurred during the specified period.
                    All figures are in Bangladeshi Taka (BDT) and are based on confirmed invoices and production costs.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
