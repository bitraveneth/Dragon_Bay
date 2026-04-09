@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
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
                        Balance Sheet
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        As of {{ $asOf->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $asOf->format('M d, Y') }}
                </span>
            </div>
            @include('admin.finance.partials.print_button', ['label' => 'Print Statement'])
        </div>
    </div>

    <!-- Balance Sheet Card -->
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
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Statement of Financial Position</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        As at {{ $asOf->format('d F Y') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Assets Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-100 dark:bg-success-900/30">
                            <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Assets</h3>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-800/30 rounded-xl overflow-hidden">
                        <table class="w-full">
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($assets as $account => $amount)
                                    <tr class="hover:bg-white dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $account }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-success-50/50 dark:bg-success-500/5 border-t-2 border-gray-300 dark:border-gray-700">
                                    <td class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                        Total Assets
                                    </td>
                                    <td class="px-4 py-4 text-right text-lg font-bold text-success-600 dark:text-success-400">
                                        {{ number_format($totalAssets, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Liabilities & Equity Section -->
                <div class="space-y-6">
                    <!-- Liabilities -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30">
                                <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Liabilities</h3>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-800/30 rounded-xl overflow-hidden">
                            <table class="w-full">
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($liabilities as $account => $amount)
                                        <tr class="hover:bg-white dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                                {{ $account }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ number_format($amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-orange-50/50 dark:bg-orange-500/5">
                                        <td class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                            Total Liabilities
                                        </td>
                                        <td class="px-4 py-4 text-right text-lg font-bold text-orange-600 dark:text-orange-400">
                                            {{ number_format($totalLiabilities, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Equity -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Equity</h3>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-800/30 rounded-xl overflow-hidden">
                            <table class="w-full">
                                <tbody>
                                    <tr class="hover:bg-white dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            Retained Earnings
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($equity, 2) }}
                                        </td>
                                    </tr>
                                    <tr class="bg-brand-50/50 dark:bg-brand-500/5 border-t-2 border-gray-300 dark:border-gray-700">
                                        <td class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                            Total Equity
                                        </td>
                                        <td class="px-4 py-4 text-right text-lg font-bold text-brand-600 dark:text-brand-400">
                                            {{ number_format($equity, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounting Equation -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800">
                <div class="bg-gradient-to-r from-brand-50 to-white dark:from-brand-950/30 dark:to-gray-900 rounded-xl p-5">
                    <div class="flex flex-wrap items-center justify-center gap-6 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Assets</span>
                            <span class="text-lg font-bold text-success-600 dark:text-success-400">{{ number_format($totalAssets, 2) }}</span>
                        </div>
                        <div class="text-gray-400 dark:text-gray-600">=</div>
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Liabilities</span>
                            <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ number_format($totalLiabilities, 2) }}</span>
                        </div>
                        <div class="text-gray-400 dark:text-gray-600">+</div>
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Equity</span>
                            <span class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ number_format($equity, 2) }}</span>
                        </div>
                    </div>
                    <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                        The balance sheet is in balance ✓
                    </p>
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
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">About the Balance Sheet</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    The balance sheet provides a snapshot of the company's financial position at a specific point in time. 
                    It shows what the company owns (assets), what it owes (liabilities), and the shareholders' equity.
                    The accounting equation <span class="font-mono font-medium">Assets = Liabilities + Equity</span> always holds true.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
