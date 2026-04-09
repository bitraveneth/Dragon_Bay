@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Commission Summary
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    {{ $month->format('F Y') }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Per-agent realized sales and earned commissions for {{ $month->format('F Y') }}, including monthly tiers.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settlements.index', ['month' => $month->format('Y-m')]) }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                View Settlements
            </a>
            <div class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                <a href="{{ route('admin.commissions.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" 
                   class="inline-flex h-9 w-9 items-center justify-center rounded-l-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="h-4 w-px bg-gray-200 dark:bg-gray-700"></span>
                <a href="{{ route('admin.commissions.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" 
                   class="inline-flex h-9 w-9 items-center justify-center rounded-r-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @if(empty($rows))
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No commissionable sales</h3>
            <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                No commissionable sales were recorded for {{ $month->format('F Y') }}.
                This could be because no delivered invoices were issued or no commission rules are configured.
            </p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a href="{{ route('admin.agents.index') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Manage Agents
                </a>
                <a href="{{ route('admin.orders.index') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    View Orders
                </a>
            </div>
        </div>
    @else
        @php
            $totalSales = collect($rows)->sum('sales');
            $totalCommission = collect($rows)->sum('commission');
            $overallRate = $totalSales > 0 ? ($totalCommission / $totalSales) * 100 : 0;
            $topAgent = collect($rows)->sortByDesc('commission')->first();
            $avgCommission = $totalCommission / count($rows);
        @endphp

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Sales Card -->
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Sales</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            BDT {{ number_format($totalSales, 2) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                        <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                        {{ count($rows) }} {{ Str::plural('agent', count($rows)) }}
                    </span>
                </div>
            </div>

            <!-- Total Commission Card -->
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Commission</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            BDT {{ number_format($totalCommission, 2) }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Avg. {{ number_format($avgCommission, 2) }} per agent
                        </p>
                    </div>
                    <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                        <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Effective Rate Card -->
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Overall Rate</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($overallRate, 2) }}%
                        </p>
                    </div>
                    <div class="rounded-lg bg-blue-light-50 p-2.5 dark:bg-blue-light-500/10">
                        <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Top Performer Card -->
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Top Performer</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $topAgent['agent']->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            BDT {{ number_format($topAgent['commission'], 2) }} commission
                        </p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-500/20">
                        <span class="text-sm font-semibold text-orange-700 dark:text-orange-400">
                            #1
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Breakdown Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Agent Performance</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Detailed breakdown of sales and commissions by agent
                        </p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ count($rows) }} {{ Str::plural('agent', count($rows)) }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Agent</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Sales</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Commission</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Effective Rate</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($rows as $index => $row)
                            @php
                                $rate = $row['sales'] > 0 ? ($row['commission'] / $row['sales']) * 100 : 0;
                                $salesShare = $totalSales > 0 ? ($row['sales'] / $totalSales) * 100 : 0;
                                $commissionShare = $totalCommission > 0 ? ($row['commission'] / $totalCommission) * 100 : 0;
                                $isTopPerformer = $index === 0;
                                $rankColors = ['bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400', 
                                             'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400', 
                                             'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400'];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        @if($isTopPerformer)
                                            <div class="flex-shrink-0 mr-2">
                                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-yellow-100 text-xs font-medium text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        @endif
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                                <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                    {{ substr($row['agent']->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $row['agent']->name }}
                                                </p>
                                                @if($row['agent']->code)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $row['agent']->code }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        BDT {{ number_format($row['sales'], 2) }}
                                    </span>
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                        ({{ number_format($salesShare, 1) }}%)
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">
                                        BDT {{ number_format($row['commission'], 2) }}
                                    </span>
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                        ({{ number_format($commissionShare, 1) }}%)
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        {{ number_format($rate, 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700">
                                            <div class="h-full rounded-full bg-brand-500" 
                                                 style="width: {{ $commissionShare }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ number_format($commissionShare, 1) }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                BDT {{ number_format($totalSales, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-success-600 dark:text-success-400">
                                BDT {{ number_format($totalCommission, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ number_format($overallRate, 2) }}%
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                100%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Export Options -->
        <div class="flex items-center justify-end gap-3">
            <button onclick="window.print()" 
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Summary
            </button>
            <a href="{{ route('admin.commissions.export', ['month' => $month->format('Y-m')]) }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </div>
    @endif
</div>
@endsection
