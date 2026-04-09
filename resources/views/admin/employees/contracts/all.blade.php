@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Global Contract Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Employee Contracts
                        </h1>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                            Global View
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        All active and historical contracts across employees
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Back to Employees
            </a>
            <a href="{{ route('admin.contracts.create') }}" 
               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Contract
            </a>
        </div>
    </div>

    @if($contracts->isNotEmpty())
        @php
            $totalContracts = $contracts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $contracts->total() : $contracts->count();
            $uniqueEmployees = $contracts->pluck('employee.id')->filter()->unique()->count();
            $activeContracts = $contracts->where('status', 'active')->count();
            $endedContracts = $contracts->where('status', 'ended')->count();
            $onHoldContracts = $contracts->where('status', 'on_hold')->count();
            
            $totalSalary = $contracts->sum('salary_amount');
            $totalTA = $contracts->sum('travel_allowance');
            $totalDA = $contracts->sum('dearness_allowance');
            $totalBonus = $contracts->sum('bonus');
            
            $statusBreakdown = $contracts->groupBy('status')->map->count();
        @endphp

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Contracts</span>
                        <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalContracts }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-success-600 dark:text-success-400">{{ $activeContracts }}</span> active,
                        <span class="font-medium text-gray-600 dark:text-gray-400">{{ $endedContracts }}</span> ended,
                        <span class="font-medium text-brand-600 dark:text-brand-400">{{ $onHoldContracts }}</span> on hold
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Employees</span>
                        <div class="rounded-lg bg-blue-light-100 p-2 dark:bg-blue-light-900/30">
                            <svg class="h-4 w-4 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $uniqueEmployees }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Employees with contracts</p>
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
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Payroll</span>
                        <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                            <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($totalSalary + $totalTA + $totalDA, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Salary: BDT {{ number_format($totalSalary, 0) }} · TA/DA: BDT {{ number_format($totalTA + $totalDA, 0) }}
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
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Bonus</span>
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875zm0 0V3m0 9.75v-1.5M3.375 7.5h17.25" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">BDT {{ number_format($totalBonus, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total bonus commitments</p>
                </div>
            </div>
        </div>

        <!-- Status Distribution (if multiple statuses) -->
        @if($statusBreakdown->count() > 1)
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
            <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Contract Status</h3>
                <div class="space-y-3">
                    @foreach($statusBreakdown as $status => $count)
                        @php
                            $percentage = $totalContracts > 0 ? round(($count / $totalContracts) * 100) : 0;
                            $statusColors = [
                                'active' => 'bg-success-500',
                                'ended' => 'bg-gray-500',
                                'on_hold' => 'bg-brand-500',
                            ];
                            $statusColor = $statusColors[$status] ?? 'bg-brand-500';
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300 capitalize">{{ $status }}</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                <div class="{{ $statusColor }} h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Contracts Table -->
            <div class="lg:col-span-3 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        @else
            <!-- Contracts Table (Full Width) -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        @endif
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Contract History</h3>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $contracts->count() }} of {{ $totalContracts }} contracts
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Period</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Salary</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">TA</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">DA</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Bonus</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($contracts as $contract)
                            @php
                                $statusColors = [
                                    'active' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'ended' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'on_hold' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                ];
                                $statusColor = $statusColors[$contract->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                
                                $isActive = $contract->status === 'active';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $isActive ? 'bg-success-50/30 dark:bg-success-500/5' : '' }}">
                                <td class="px-6 py-4">
                                    @if($contract->employee)
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-900/30">
                                                <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                    {{ substr($contract->employee->name, 0, 1) }}{{ substr($contract->employee->name, strpos($contract->employee->name, ' ') + 1, 1) ?? '' }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $contract->employee->name }}
                                                </p>
                                                @if($contract->employee->department)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $contract->employee->department }}
                                                    </p>
                                                @endif
                                            </div>
                                            @if($isActive)
                                                <span class="inline-flex items-center rounded-full bg-success-100 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400 ml-2">
                                                    Current
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                            <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                                                #{{ substr($contract->reference, -4) }}
                                            </span>
                                        </div>
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $contract->reference }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                            <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $contract->start_date?->format('d M Y') }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">→</span>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $contract->end_date?->format('d M Y') ?? 'Open-ended' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                                        BDT {{ number_format($contract->salary_amount ?? 0, 0) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        BDT {{ number_format($contract->travel_allowance ?? 0, 0) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        BDT {{ number_format($contract->dearness_allowance ?? 0, 0) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">
                                        BDT {{ number_format($contract->bonus ?? 0, 0) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                                        <span class="h-1.5 w-1.5 rounded-full 
                                            {{ $contract->status === 'active' ? 'bg-success-500' : 
                                               ($contract->status === 'on_hold' ? 'bg-brand-500' : 'bg-gray-500') }}">
                                        </span>
                                        {{ ucfirst(str_replace('_', ' ', $contract->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($contract->employee)
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.employees.contracts.edit', [$contract->employee, $contract]) }}" 
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.employees.contracts.destroy', [$contract->employee, $contract]) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Delete contract {{ $contract->reference }} for {{ $contract->employee->name }}? This action cannot be undone.');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-sm hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($contracts, 'links'))
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm p-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 opacity-20 dark:from-gray-900 dark:to-gray-800 blur-3xl"></div>
            
            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-500 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">No Contracts</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    No employment contracts have been recorded yet. Add your first contract to start tracking employee employment details.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <a href="{{ route('admin.contracts.create') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add First Contract
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
