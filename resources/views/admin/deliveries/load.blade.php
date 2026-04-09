@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Vehicle Load
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Fleet Capacity
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Estimated crate load per vehicle for {{ $date->format('d M Y') }}.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Date Navigation -->
            <div class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                <a href="{{ route('admin.vehicle-load.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" 
                   class="inline-flex h-9 w-9 items-center justify-center rounded-l-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="h-4 w-px bg-gray-200 dark:bg-gray-700"></span>
                <span class="px-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $date->format('M d, Y') }}
                </span>
                <span class="h-4 w-px bg-gray-200 dark:bg-gray-700"></span>
                <a href="{{ route('admin.vehicle-load.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" 
                   class="inline-flex h-9 w-9 items-center justify-center rounded-r-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            
            <!-- Today Button -->
            <a href="{{ route('admin.vehicle-load.index') }}" 
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Today
            </a>
        </div>
    </div>

    @if(empty($rows))
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4-4m-4 4l4 4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M12 13v8m-4-4h8"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No deliveries scheduled</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                No deliveries are scheduled for {{ $date->format('l, F d, Y') }}.
                Select a different date or schedule new deliveries.
            </p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('admin.deliveries.create') }}" 
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Schedule Delivery
                </a>
            </div>
        </div>
    @else
        <!-- Summary Cards -->
        @php
            $totalVehicles = count($rows);
            $totalCapacity = collect($rows)->sum(fn($row) => $row['vehicle']->capacity_crates ?? 0);
            $totalLoad = collect($rows)->sum('crateLoad');
            $overallUtilization = $totalCapacity > 0 ? ($totalLoad / $totalCapacity) * 100 : 0;
            $overloadedCount = collect($rows)->filter(fn($row) => 
                ($row['vehicle']->capacity_crates ?? 0) > 0 && 
                $row['crateLoad'] > $row['vehicle']->capacity_crates
            )->count();
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Active Vehicles</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalVehicles }}</p>
                    </div>
                    <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                        <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4-4m-4 4l4 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Capacity</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalCapacity) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">crates</p>
                    </div>
                    <div class="rounded-lg bg-blue-light-50 p-2.5 dark:bg-blue-light-500/10">
                        <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Load</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalLoad) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">crates</p>
                    </div>
                    <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                        <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Utilization</p>
                        <p class="mt-2 text-2xl font-semibold {{ $overallUtilization > 90 ? 'text-error-600 dark:text-error-500' : 'text-gray-900 dark:text-white' }}">
                            {{ number_format($overallUtilization, 1) }}%
                        </p>
                        @if($overloadedCount > 0)
                            <p class="mt-1 text-xs text-error-600 dark:text-error-500">
                                ⚠️ {{ $overloadedCount }} overloaded
                            </p>
                        @endif
                    </div>
                    <div class="rounded-lg {{ $overallUtilization > 90 ? 'bg-error-50 dark:bg-error-500/10' : 'bg-orange-50 dark:bg-orange-500/10' }}">
                        <svg class="h-5 w-5 {{ $overallUtilization > 90 ? 'text-error-500' : 'text-orange-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Load Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Vehicle Load Details</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $totalVehicles }} {{ Str::plural('vehicle', $totalVehicles) }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Vehicle</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Capacity (crates)</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Planned Load (crates)</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Utilization</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($rows as $row)
                            @php
                                $vehicle = $row['vehicle'];
                                $capacity = $vehicle->capacity_crates ?? 0;
                                $load = $row['crateLoad'];
                                $util = $capacity > 0 ? ($load / $capacity) * 100 : null;
                                $isOverloaded = $capacity > 0 && $load > $capacity;
                                $isNearCapacity = $capacity > 0 && $load >= $capacity * 0.9 && $load <= $capacity;
                                
                                $utilColor = 'bg-gray-200 dark:bg-gray-700';
                                if ($util !== null) {
                                    if ($util >= 100) $utilColor = 'bg-error-500';
                                    elseif ($util >= 90) $utilColor = 'bg-orange-500';
                                    elseif ($util >= 70) $utilColor = 'bg-success-500';
                                    else $utilColor = 'bg-brand-500';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4-4m-4 4l4 4"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $vehicle->name }}
                                            </p>
                                            @if($vehicle->license_plate)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $vehicle->license_plate }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($capacity > 0)
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($capacity) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold {{ $isOverloaded ? 'text-error-600 dark:text-error-500' : 'text-gray-900 dark:text-white' }}">
                                        {{ number_format($load) }}
                                    </span>
                                    @if($isOverloaded)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                            ⚠️ Overload
                                        </span>
                                    @elseif($isNearCapacity)
                                        <span class="ml-2 inline-flex items-center rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-500/20 dark:text-orange-400">
                                            Near capacity
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($util !== null)
                                        <div class="flex items-center gap-3">
                                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700">
                                                <div class="h-full rounded-full {{ $utilColor }}" 
                                                     style="width: {{ min($util, 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium {{ $util >= 100 ? 'text-error-600 dark:text-error-500' : 'text-gray-700 dark:text-gray-300' }}">
                                                {{ number_format($util, 1) }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No capacity set</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300">Totals</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($totalCapacity) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold {{ $totalLoad > $totalCapacity ? 'text-error-600 dark:text-error-500' : 'text-gray-900 dark:text-white' }}">
                                {{ number_format($totalLoad) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700">
                                        <div class="h-full rounded-full {{ $overallUtilization > 90 ? 'bg-error-500' : 'bg-brand-500' }}" 
                                             style="width: {{ min($overallUtilization, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $overallUtilization > 90 ? 'text-error-600 dark:text-error-500' : 'text-gray-700 dark:text-gray-300' }}">
                                        {{ number_format($overallUtilization, 1) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>


    @endif
</div>
@endsection