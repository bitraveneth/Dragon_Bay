@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Payroll Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                </div>
                
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Payroll Summary
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Salary and allowances per employee for the selected period
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Date Filter Form -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm w-full lg:w-auto">
            <form method="GET" action="{{ route('admin.reports.payroll') }}" class="flex flex-col sm:flex-row items-end gap-3">
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- Total Salary Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Salary</span>
                    <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                        <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($totals['salary'], 0) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Base salary</p>
            </div>
        </div>

        <!-- Total TA Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.22-1.113-.615-1.53a15.54 15.54 0 00-1.95-1.77" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total TA</span>
                    <div class="rounded-lg bg-blue-light-100 p-2 dark:bg-blue-light-900/30">
                        <svg class="h-4 w-4 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.22-1.113-.615-1.53a15.54 15.54 0 00-1.95-1.77" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($totals['ta'], 0) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Travel allowance</p>
            </div>
        </div>

        <!-- Total DA Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 17.25v1.5a1.5 1.5 0 001.5 1.5h3a1.5 1.5 0 001.5-1.5v-1.5M9 11.25v4.5M15 11.25v4.5" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total DA</span>
                    <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                        <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.5a1.5 1.5 0 001.5 1.5h3a1.5 1.5 0 001.5-1.5v-1.5M9 11.25v4.5M15 11.25v4.5" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($totals['da'], 0) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dearness allowance</p>
            </div>
        </div>

        <!-- Total Bonus Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875zm0 0V3m0 9.75v-1.5M3.375 7.5h17.25" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Bonus</span>
                    <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                        <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875zm0 0V3m0 9.75v-1.5M3.375 7.5h17.25" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-success-600 dark:text-success-400">BDT {{ number_format($totals['bonus'], 0) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bonus payments</p>
            </div>
        </div>

        <!-- Grand Total Card -->
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Grand Total</span>
                    <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                        <svg class="h-4 w-4 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($totals['grand'], 0) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total payroll</p>
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
                    <span class="text-gray-600 dark:text-gray-400">{{ $rows->count() }} employees</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-success-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Active contracts</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-employee Breakdown Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                    <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Per-employee Breakdown</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Base contract amounts plus TA/DA/bonus allowances in this period
                    </p>
                </div>
            </div>
        </div>

        @if($rows->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Employee</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Department</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Base Salary</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Base TA</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Base DA</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Base Bonus</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">TA Claims</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">DA Claims</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Bonus Claims</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @php
                                $totalSalary = 0;
                                $totalBaseTA = 0;
                                $totalBaseDA = 0;
                                $totalBaseBonus = 0;
                                $totalTA = 0;
                                $totalDA = 0;
                                $totalBonus = 0;
                                $totalGrand = 0;
                            @endphp
                            @foreach($rows as $row)
                                @php
                                    $employee = $row['employee'];
                                    $totalSalary += $row['total_salary'];
                                    $totalBaseTA += $row['base_ta'];
                                    $totalBaseDA += $row['base_da'];
                                    $totalBaseBonus += $row['base_bonus'];
                                    $totalTA += $row['ta_allowances'];
                                    $totalDA += $row['da_allowances'];
                                    $totalBonus += $row['bonus_allowances'];
                                    $totalGrand += $row['grand_total'];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/30">
                                                <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                    {{ substr($employee->name, 0, 1) }}{{ substr($employee->name, strpos($employee->name, ' ') + 1, 1) ?? '' }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $employee->name }}
                                                </p>
                                                @if($employee->employee_id)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        ID: {{ $employee->employee_id }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($employee->department)
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                                {{ $employee->department }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($row['total_salary'], 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">
                                        {{ number_format($row['base_ta'], 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">
                                        {{ number_format($row['base_da'], 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">
                                        {{ number_format($row['base_bonus'], 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-blue-light-600 dark:text-blue-light-400">
                                        +{{ number_format($row['ta_allowances'], 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-orange-600 dark:text-orange-400">
                                        +{{ number_format($row['da_allowances'], 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium text-success-600 dark:text-success-400">
                                        +{{ number_format($row['bonus_allowances'], 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-base font-bold text-gray-900 dark:text-white">
                                        {{ number_format($row['grand_total'], 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300" colspan="2">
                                    Totals
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                                    {{ number_format($totalSalary, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ number_format($totalBaseTA, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ number_format($totalBaseDA, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ number_format($totalBaseBonus, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-blue-light-600 dark:text-blue-light-400">
                                    +{{ number_format($totalTA, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-orange-600 dark:text-orange-400">
                                    +{{ number_format($totalDA, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-success-600 dark:text-success-400">
                                    +{{ number_format($totalBonus, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right text-base font-bold text-brand-600 dark:text-brand-400">
                                    {{ number_format($totalGrand, 0) }}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No payroll data</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    No payroll records were found for the selected period.
                    Try adjusting the date range or add employee contracts and allowances.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
