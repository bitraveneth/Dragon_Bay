@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Report Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                </div>
                
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Production & P/L Summary
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Production quantities vs sales and expenses for the selected period
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Date Filter Form -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm w-full lg:w-auto">
            <form method="GET" action="{{ route('admin.reports.production') }}" class="flex flex-col sm:flex-row items-end gap-3">
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <input type="date" 
                               name="from" 
                               value="{{ request('from', $from->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white/50 pl-10 pr-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
                    </div>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <input type="date" 
                               name="to" 
                               value="{{ request('to', $to->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-gray-200 bg-white/50 pl-10 pr-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
                    </div>
                </div>
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200 whitespace-nowrap">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Apply
                </button>
                @include('admin.finance.partials.print_button', ['label' => 'Print Report'])
            </form>
        </div>
    </div>

    @php
        $profitColor = $approxProfit >= 0 ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-500';
        $profitIcon = $approxProfit >= 0 ? 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33' : 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636';
    @endphp

    <!-- P/L Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Total Sales Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Net Sales</span>
                    <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                        <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($salesTotal, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Net invoiced sales after credits, excluding VAT</p>
            </div>
        </div>

        <!-- Total Expenses Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Operating Expenses</span>
                    <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                        <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($expensesTotal, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Campaign, gift, and operating spend</p>
            </div>
        </div>

        <!-- Profit/Loss Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ $approxProfit >= 0 ? 'Profit' : 'Loss' }}
                    </span>
                    <div class="rounded-lg {{ $approxProfit >= 0 ? 'bg-success-100' : 'bg-error-100' }} p-2 dark:{{ $approxProfit >= 0 ? 'bg-success-900/30' : 'bg-error-900/30' }}">
                        <svg class="h-4 w-4 {{ $profitColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $profitIcon }}" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold {{ $profitColor }}">
                    {{ $approxProfit >= 0 ? '+' : '' }}BDT {{ number_format($approxProfit, 2) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $salesTotal > 0 ? round(($approxProfit / $salesTotal) * 100, 1) : 0 }}% margin
                </p>
            </div>
        </div>
    </div>

    <!-- Period Summary Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/30">
                    <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-brand-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Sales</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-orange-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Expenses</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $approxProfit >= 0 ? 'bg-success-500' : 'bg-error-500' }}"></span>
                    <span class="text-gray-600 dark:text-gray-400">{{ $approxProfit >= 0 ? 'Profit' : 'Loss' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Production by Product Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                    <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Production by Product</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Number of runs and total quantity produced per product, including
                        an estimated material cost based on BOM & material standard costs.
                    </p>
                </div>
            </div>
        </div>

        @if($byProduct->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Production Runs</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Total Quantity</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Est. Unit Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Est. Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @php
                                $totalRuns = collect($byProduct)->sum('runs');
                                $totalQuantity = collect($byProduct)->sum('quantity');
                                $totalCost = collect($byProduct)->sum('total_cost');
                            @endphp
                            @foreach($byProduct as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                                <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                    {{ substr($row['product']?->name ?? '?', 0, 1) }}
                                                </span>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $row['product']?->name ?? 'Unknown product' }}
                                            </span>
                                            @if($row['product']?->sku)
                                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                                    SKU: {{ $row['product']->sku }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center justify-center rounded-full bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                                            {{ $row['runs'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($row['quantity']) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(!is_null($row['unit_cost']))
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                BDT {{ number_format($row['unit_cost'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400 italic">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if(!is_null($row['total_cost']))
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                BDT {{ number_format($row['total_cost'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400 italic">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Totals
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $totalRuns }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($totalQuantity) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">
                                    —
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-brand-600 dark:text-brand-400">
                                    BDT {{ number_format($totalCost, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm p-12 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No production runs</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    No production runs were recorded for the selected period.
                    Try adjusting the date range or create new production runs.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
