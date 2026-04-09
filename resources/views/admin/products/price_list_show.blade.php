@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Price list · {{ $product->sku }}
            </h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $product->name }}</span>
                <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Base price and agent-specific overrides for this SKU.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.prices.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Price Lists
            </a>
            <a href="{{ route('admin.products.edit', $product) }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Product
            </a>
        </div>
    </div>

    <!-- Base Price Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Base Price</p>
                <h3 class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                    BDT {{ number_format($product->base_price ?? 0, 2) }}
                </h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Configured on the product master
                </p>
            </div>
            <div class="rounded-full bg-brand-50 p-3 dark:bg-brand-500/10">
                <svg class="h-6 w-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Agent Overrides Section -->
    @if($agentPrices->isEmpty())
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No agent overrides</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                No agent-specific price overrides found for this SKU. Use the agent pricing screen to define negotiated prices.
            </p>
            <a href="{{ route('admin.agents.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Manage Agents
            </a>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Agent Overrides</h2>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {{ $agentPrices->count() }} {{ Str::plural('override', $agentPrices->count()) }}
                </span>
            </div>

            <!-- Table -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Agent
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Area / Zone
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Override Price
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Notes
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($agentPrices as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                                <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                    {{ substr(optional($row->agent)->name ?? '?', 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ optional($row->agent)->name ?? '—' }}
                                                </p>
                                                @if($row->agent && $row->agent->code)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $row->agent->code }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        @if($row->agent)
                                            <span class="font-medium">{{ $row->agent->area }}</span>
                                            @if($row->agent->zone)
                                                <span class="text-gray-400 dark:text-gray-600">·</span>
                                                <span>{{ $row->agent->zone }}</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            BDT {{ number_format($row->price ?? 0, 2) }}
                                        </span>
                                        @if($row->price && $product->base_price)
                                            @php
                                                $diff = $row->price - $product->base_price;
                                                $percent = $product->base_price > 0 ? round(($diff / $product->base_price) * 100, 1) : 0;
                                            @endphp
                                            <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium 
                                                {{ $diff > 0 ? 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400' : 
                                                   ($diff < 0 ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : 
                                                   'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400') }}">
                                                {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }} ({{ $percent }}%)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $row->notes ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($row->agent)
                                            <a href="{{ route('admin.agents.pricing.edit', $row->agent) }}" 
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                Agent Pricing
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Additional Info Card -->
            <div class="rounded-lg bg-blue-light-50 p-4 border border-blue-light-100 dark:bg-blue-light-500/10 dark:border-blue-light-500/20">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-blue-light-600 dark:text-blue-light-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-light-800 dark:text-blue-light-300">
                        <p class="font-medium">Price Priority Information</p>
                        <p class="mt-1">Agent-specific prices override the base price during order processing. 
                           If no override exists, the system uses the base price from the product master.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection