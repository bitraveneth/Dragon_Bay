@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $agent->name }}
                </h1>
                @if($agent->code)
                    <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                        Code: {{ $agent->code }}
                    </span>
                @endif
                @if($agent->is_active)
                    <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                            <circle cx="3" cy="3" r="3" />
                        </svg>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full bg-error-100 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                            <circle cx="3" cy="3" r="3" />
                        </svg>
                        Suspended
                    </span>
                @endif
            </div>
            <div class="mt-2 flex items-center gap-2">
                <div class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>
                        @if($agent->area) {{ $agent->area }} @endif
                        @if($agent->zone) · Zone {{ $agent->zone }} @endif
                        @if($agent->location_code) · {{ $agent->location_code }} @endif
                    </span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Created {{ \Carbon\Carbon::parse($agent->created_at)->format('d M Y') }}</span>
                <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                <span>Updated {{ \Carbon\Carbon::parse($agent->updated_at)->diffForHumans() }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agents.edit', $agent) }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Client
            </a>
            <a href="{{ route('admin.agents.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Clients
            </a>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Content - Left Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Section -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contact Information</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</p>
                            @if($agent->email)
                                <a href="mailto:{{ $agent->email }}" class="mt-1 text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                    {{ $agent->email }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone</p>
                            @if($agent->phone)
                                <a href="tel:{{ $agent->phone }}" class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $agent->phone }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Location & Territory</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Area</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $agent->area ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Zone</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $agent->zone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Location Code</p>
                            @if($agent->location_code)
                                <span class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    {{ $agent->location_code }}
                                </span>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Special Code</p>
                            @if($agent->special_code)
                                <span class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    {{ $agent->special_code }}
                                </span>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial & KYC Section -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Financial & KYC</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Credit Limit</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                BDT {{ number_format($agent->credit_limit ?? 0, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Withholding Tax Rate</p>
                            @if($agent->withholding_rate)
                                <span class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    {{ number_format($agent->withholding_rate, 2) }}%
                                </span>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Parent Account</p>
                            @if($agent->parent)
                                <div class="mt-1 flex items-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20">
                                        <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                            {{ substr($agent->parent->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $agent->parent->name }}</span>
                                </div>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</p>
                            @if($agent->is_active)
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                    <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                        <circle cx="3" cy="3" r="3" />
                                    </svg>
                                    Active
                                </span>
                            @else
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-error-100 px-2.5 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                    <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                        <circle cx="3" cy="3" r="3" />
                                    </svg>
                                    Suspended
                                </span>
                            @endif
                        </div>
                        
                        <!-- Bank Details - Full Width -->
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Bank Details</p>
                            @if($agent->bank_details)
                                <div class="mt-2 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-300">
                                    {{ $agent->bank_details }}
                                </div>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                        
                        <!-- KYC Documents - Full Width -->
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">KYC Documents</p>
                            @php
                                $kycDocs = is_array($agent->kyc_documents) ? $agent->kyc_documents : [];
                            @endphp
                            @if(empty($kycDocs))
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @else
                                <div class="mt-2 space-y-2">
                                    @foreach($kycDocs as $doc)
                                        <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-800/50">
                                            @if(Str::startsWith($doc, 'agents/kyc/'))
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <a href="{{ asset('storage/'.$doc) }}" target="_blank" class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                                    {{ basename($doc) }}
                                                </a>
                                            @else
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                                </svg>
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $doc }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar - Right Column (1/3 width) -->
        <div class="space-y-6">
            <!-- Account Snapshot Card -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Account Snapshot</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Credit Limit</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">BDT {{ number_format($agent->credit_limit ?? 0, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                            @if($agent->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-error-100 px-2.5 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                    Suspended
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Parent Account</span>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $agent->parent->name ?? '—' }}</span>
                        </div>
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ route('admin.agents.ledger.show', $agent) }}" 
                               class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                View Ledger
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links Card -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Quick Links</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Jump into detailed views for this client account.
                    </p>
                    <div class="space-y-2">
                        @if(Route::has('admin.agents.pricing.edit'))
                        <a href="{{ route('admin.agents.pricing.edit', $agent) }}" 
                           class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-300 dark:hover:bg-gray-800">
                            <span>Pricing & Commission</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @endif
                        
                        @if(Route::has('admin.agents.ledger.show'))
                        <a href="{{ route('admin.agents.ledger.show', $agent) }}" 
                           class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-300 dark:hover:bg-gray-800">
                            <span>Ledger</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @endif
                        
                        @if(Route::has('admin.orders.index'))
                        <a href="{{ route('admin.orders.index', ['agent_id' => $agent->id]) }}" 
                           class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-300 dark:hover:bg-gray-800">
                            <span>Orders for Client</span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Client Stats Card (Optional) -->
            @if(isset($agent->orders_count) || isset($agent->total_sales))
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                            <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Performance</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @if(isset($agent->orders_count))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Orders</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $agent->orders_count }}</span>
                        </div>
                        @endif
                        @if(isset($agent->total_sales))
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total billed value</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">BDT {{ number_format($agent->total_sales, 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
