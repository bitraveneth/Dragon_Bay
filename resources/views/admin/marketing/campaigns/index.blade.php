@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.871m7.5 0v.871m-7.5-.871v.871M12 2.25c-1.5 0-3 .75-3 2.25v6.75c0 1.5.75 2.25 2.25 2.25h1.5c1.5 0 2.25-.75 2.25-2.25V4.5c0-1.5-1.5-2.25-3-2.25z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Marketing Campaigns
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Track social and offline campaigns, reach, and spend
                    </p>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.campaigns.create') }}" 
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Campaign
        </a>
    </div>

    <!-- Status Message -->

    @if($campaigns->isNotEmpty())
        @php
            $totalCampaigns = $campaigns instanceof \Illuminate\Pagination\LengthAwarePaginator ? $campaigns->total() : $campaigns->count();
            $totalReach = $campaigns->sum('reach');
            $totalImpressions = $campaigns->sum('impressions');
            $totalCost = $campaigns->sum('cost');
            $activeCampaigns = $campaigns->where('status', 'running')->count();
            $avgCPM = $totalImpressions > 0 ? ($totalCost / $totalImpressions) * 1000 : 0;
            $avgCPR = $totalReach > 0 ? $totalCost / $totalReach : 0;
            
            $platformBreakdown = $campaigns->groupBy('platform')->map->count();
        @endphp

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Campaigns -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-10">
                    <svg class="h-full w-full text-brand-600 dark:text-brand-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.871m7.5 0v.871m-7.5-.871v.871M12 2.25c-1.5 0-3 .75-3 2.25v6.75c0 1.5.75 2.25 2.25 2.25h1.5c1.5 0 2.25-.75 2.25-2.25V4.5c0-1.5-1.5-2.25-3-2.25z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Campaigns</span>
                        <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalCampaigns }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-brand-600 dark:text-brand-400">{{ $activeCampaigns }} running</span> · {{ $totalCampaigns - $activeCampaigns }} not running
                    </p>
                </div>
            </div>

            <!-- Total Reach -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-10">
                    <svg class="h-full w-full text-blue-light-600 dark:text-blue-light-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12.75c1.148 0 2.278.08 3.383.237 1.037.146 1.866.966 1.866 2.013 0 1.66-1.391 3.018-3.106 2.934A15.66 15.66 0 0012 17.25c-1.148 0-2.278.08-3.383.237-1.037.146-1.866.966-1.866 2.013 0 1.66 1.391 3.018 3.106 2.934A15.66 15.66 0 0112 20.25a39.34 39.34 0 01-4.143.212c-1.622-.013-2.857-1.292-2.857-2.855V17.25" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Reach</span>
                        <div class="rounded-lg bg-blue-light-100 p-2 dark:bg-blue-light-900/30">
                            <svg class="h-4 w-4 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalReach) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-blue-light-600 dark:text-blue-light-400">CPR: BDT {{ number_format($avgCPR, 2) }}</span> · Cost per reach
                    </p>
                </div>
            </div>

            <!-- Total Impressions -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-10">
                    <svg class="h-full w-full text-purple-600 dark:text-purple-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Impressions</span>
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                            <svg class="h-4 w-4 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalImpressions) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-purple-600 dark:text-purple-400">CPM: BDT {{ number_format($avgCPM, 2) }}</span> · Cost per 1k
                    </p>
                </div>
            </div>

            <!-- Total Spend -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-10">
                    <svg class="h-full w-full text-success-600 dark:text-success-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Spend</span>
                        <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                            <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($totalCost, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Across {{ $platformBreakdown->count() }} {{ Str::plural('platform', $platformBreakdown->count()) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Platform Distribution & Campaign Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Platform Distribution Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Platform Distribution</h3>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $platformBreakdown->count() }} platforms</span>
                </div>
                <div class="space-y-4">
                    @foreach($platformBreakdown as $platform => $count)
                        @php
                            $percentage = $totalCampaigns > 0 ? round(($count / $totalCampaigns) * 100) : 0;
                            $platformColors = [
                                'facebook' => 'bg-brand-500',
                                'instagram' => 'bg-purple-500',
                                'twitter' => 'bg-blue-light-500',
                                'linkedin' => 'bg-blue-500',
                                'youtube' => 'bg-error-500',
                                'google' => 'bg-orange-500',
                                'offline' => 'bg-gray-500',
                            ];
                            $platformColor = $platformColors[strtolower($platform)] ?? 'bg-brand-500';
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300 capitalize">{{ $platform }}</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ $count }} {{ Str::plural('campaign', $count) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="{{ $platformColor }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Campaign Performance Summary -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Campaign Performance</h3>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-900/30 dark:text-success-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                            Running: {{ $activeCampaigns }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>
                            Not running: {{ $totalCampaigns - $activeCampaigns }}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Avg. Reach/Campaign</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($totalCampaigns > 0 ? $totalReach / $totalCampaigns : 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Avg. Impressions/Campaign</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($totalCampaigns > 0 ? $totalImpressions / $totalCampaigns : 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Avg. Spend/Campaign</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">BDT {{ number_format($totalCampaigns > 0 ? $totalCost / $totalCampaigns : 0, 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaigns Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">All Campaigns</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $campaigns->count() }} of {{ $totalCampaigns }} campaigns
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Campaign</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Platform</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Period</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Reach</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Impressions</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($campaigns as $campaign)
                            @php
                                $statusColors = [
                                    'planned' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'running' => 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
                                    'completed' => 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
                                ];
                                $statusColor = $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                
                                $platformColors = [
                                    'facebook' => 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
                                    'instagram' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'twitter' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-900/30 dark:text-blue-light-400',
                                    'linkedin' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'youtube' => 'bg-error-100 text-error-700 dark:bg-error-900/30 dark:text-error-400',
                                    'google' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                    'offline' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                ];
                                $platformColor = $platformColors[strtolower($campaign->platform)] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                
                                $ctr = $campaign->impressions > 0 ? ($campaign->reach / $campaign->impressions) * 100 : 0;
                                $cpc = $campaign->reach > 0 ? $campaign->cost / $campaign->reach : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $campaign->name }}</p>
                                        @if($campaign->ctr > 0 || $cpc > 0)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                CTR: {{ number_format($ctr, 1) }}% · CPC: BDT {{ number_format($cpc, 2) }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $platformColor }} capitalize">
                                        {{ $campaign->platform }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $campaign->start_date?->format('d M Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            → {{ $campaign->end_date?->format('d M Y') ?? 'Ongoing' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($campaign->reach ?? 0) }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($campaign->impressions ?? 0) }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($campaign->cost !== null)
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">BDT {{ number_format($campaign->cost, 0) }}</p>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400">—</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $campaign->status === 'running' ? 'bg-success-500' : ($campaign->status === 'completed' ? 'bg-brand-500' : 'bg-gray-500') }}"></span>
                                        {{ ucfirst($campaign->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.campaigns.edit', $campaign) }}" 
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.campaigns.destroy', $campaign) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete campaign &quot;{{ $campaign->name }}&quot;? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-error-600 shadow-sm hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($campaigns, 'links'))
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Modern Empty State -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm p-16 text-center shadow-xl dark:border-gray-800 dark:bg-gray-900/50">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-purple-100 to-purple-50 opacity-20 dark:from-purple-900 dark:to-purple-800 blur-3xl"></div>
            
            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.871m7.5 0v.871m-7.5-.871v.871M12 2.25c-1.5 0-3 .75-3 2.25v6.75c0 1.5.75 2.25 2.25 2.25h1.5c1.5 0 2.25-.75 2.25-2.25V4.5c0-1.5-1.5-2.25-3-2.25z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">No campaigns yet</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Start tracking your marketing efforts by creating your first campaign. Monitor reach, impressions, and ROI across all platforms.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <a href="{{ route('admin.campaigns.create') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 transition-all">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Your First Campaign
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
