@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @php
        $canManageSettlementAccounting = \App\Helpers\Permission::can(auth()->user(), 'accounting.manage');
    @endphp
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Commission Settlements
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    {{ $month->format('F Y') }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Monthly totals by agent for {{ $month->format('F Y') }}.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.settlements.generate') }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Recalculate Month
                </button>
            </form>
            <a href="{{ route('admin.agents.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Manage Agents
            </a>
        </div>
    </div>

    <!-- Status Message -->

    <!-- Monthly Summary Cards -->
    @php
        $totalSales = $settlements->sum('sales_total');
        $totalCommission = $settlements->sum('commission_total');
        $avgRate = $totalSales > 0 ? ($totalCommission / $totalSales) * 100 : 0;
        $paidCount = $settlements->where('status', 'paid')->count();
        $approvedCount = $settlements->where('status', 'approved')->count();
        $openCount = $settlements->where('status', 'open')->count();
    @endphp

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
                            Avg. rate: {{ number_format($avgRate, 2) }}%
                        </p>
                    </div>
                    <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                        <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Agents Count Card -->
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Agents</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $settlements->count() }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-blue-light-50 p-2.5 dark:bg-blue-light-500/10">
                        <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-success-500"></span>
                        Paid: {{ $paidCount }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                        Approved: {{ $approvedCount }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                        Open: {{ $openCount }}
                    </span>
                </div>
            </div>

            <!-- Month Navigation Card -->
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Navigate</p>
                        <div class="mt-2 flex items-center gap-2">
                            <a href="{{ route('admin.settlements.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" 
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $month->format('M Y') }}</span>
                            <a href="{{ route('admin.settlements.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" 
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-2.5 dark:bg-gray-800">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
    </div>

    <!-- Settlements Table -->
    @if($settlements->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No settlements found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                No commission settlements have been generated for {{ $month->format('F Y') }} yet.
                Click "Recalculate Month" to generate settlements for this period.
            </p>
            <form action="{{ route('admin.settlements.generate') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Generate Settlements
                </button>
            </form>
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Settlement Details</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $settlements->count() }} {{ Str::plural('agent', $settlements->count()) }}
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
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Rate</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($settlements as $settlement)
                            @php
                                $rate = $settlement->sales_total > 0 ? ($settlement->commission_total / $settlement->sales_total) * 100 : 0;
                                $statusColors = [
                                    'open' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'approved' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'paid' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                ];
                                $statusColor = $statusColors[$settlement->status] ?? $statusColors['open'];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                            <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                {{ substr($settlement->agent->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $settlement->agent->name }}
                                            </p>
                                            @if($settlement->agent->code)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $settlement->agent->code }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        BDT {{ number_format($settlement->sales_total, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        BDT {{ number_format($settlement->commission_total, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        {{ number_format($rate, 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        {{ ucfirst($settlement->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($canManageSettlementAccounting)
                                        <form action="{{ route('admin.settlements.update-status', $settlement) }}" 
                                              method="POST" 
                                              class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="relative">
                                                <select name="status"
                                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                    @foreach(['open','approved','paid'] as $status)
                                                        <option value="{{ $status }}"{{ $settlement->status === $status ? ' selected' : '' }}>
                                                            {{ ucfirst($status) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Update
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Accounting approval required</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300" colspan="1">
                                Period Totals
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                BDT {{ number_format($totalSales, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                BDT {{ number_format($totalCommission, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ number_format($avgRate, 2) }}%
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>


    @endif
</div>
@endsection
