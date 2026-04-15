@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Agent Directory
            </h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                List, search, and manage your agent accounts.
            </p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.agents.index') }}" class="flex gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search clients..."
                        aria-label="Search clients"
                        class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                </div>
                <button type="submit" 
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Search
                </button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.agents.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Clear
                    </a>
                @endif
            </form>
            <a href="{{ route('admin.agents.create') }}" 
               data-tour="agents-primary-action"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Client
            </a>
        </div>
    </div>

    <!-- Status Message -->

    <!-- Clients Table -->
    @if($agents->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Zone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Parent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Credit Limit</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Commission</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Account</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($agents as $agent)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                            <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                {{ substr($agent->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $agent->name }}
                                            </p>
                                            @if($agent->code)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $agent->code }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm">
                                        @if($agent->area || $agent->zone)
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $agent->area ?? '—' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $agent->zone ?? '—' }}</p>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    @if($agent->parent)
                                        <div class="flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $agent->parent->name }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        BDT {{ number_format($agent->credit_limit, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($agent->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                            <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-error-100 px-2.5 py-0.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                            <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Suspended
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $rules = $agent->commissions ?? collect();
                                    @endphp
                                    @if($rules->isEmpty())
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">—</span>
                                    @else
                                        @php $primaryRule = $rules->first(); @endphp
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-[11px] font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                                            @if($primaryRule->type === 'percentage')
                                                {{ rtrim(rtrim(number_format($primaryRule->value, 2), '0'), '.') }}%
                                            @else
                                                BDT {{ number_format($primaryRule->value, 2) }}
                                            @endif
                                            @if($primaryRule->order_type)
                                                <span class="text-[10px] text-brand-600/80 dark:text-brand-200/80">
                                                    {{ $primaryRule->order_type }}
                                                </span>
                                            @endif
                                        </span>
                                        @if($rules->count() > 1)
                                            <span class="ml-1 text-[10px] text-gray-500 dark:text-gray-400">
                                                +{{ $rules->count() - 1 }} more
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3" x-data="{ open:false }" @keydown.escape.window="open=false">
                                    <div class="relative inline-block text-left">
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                        >
                                            Account
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div
                                            x-show="open"
                                            x-transition
                                            @click.outside="open = false"
                                            class="absolute z-30 mt-1 w-40 origin-top-left rounded-lg border border-gray-200 bg-white py-1 text-xs shadow-lg dark:border-gray-700 dark:bg-gray-900"
                                        >
                                            <a href="{{ route('admin.agents.pricing.edit', $agent) }}"
                                               class="flex items-center gap-1 px-3 py-1.5 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Pricing
                                            </a>
                                            <a href="{{ route('admin.agents.ledger.show', $agent) }}"
                                               class="flex items-center gap-1 px-3 py-1.5 text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                Ledger
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.agents.show', $agent) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('admin.agents.edit', $agent) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.agents.destroy', $agent) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete this client? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $agents->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No clients found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                @if(request()->filled('q'))
                    No clients match your search criteria. Try a different search term.
                @else
                    No clients registered yet. Add your first client to start building your account base.
                @endif
            </p>
            <a href="{{ route('admin.agents.create') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Client
            </a>
        </div>
    @endif
</div>
@endsection
