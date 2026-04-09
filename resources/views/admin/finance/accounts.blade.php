@extends('layouts.app')

@section('content')
@php
    $summaryAccounts = $allAccounts ?? $accounts;
    $totalAccounts = $summaryAccounts->count();
    $activeAccounts = $summaryAccounts->where('is_active', true)->count();
    $inactiveAccounts = $totalAccounts - $activeAccounts;
    $visibleAccountsCount = $accounts->count();
    $accountTypes = $summaryAccounts->groupBy('type')->map->count();
    $typeLabels = [
        'asset' => 'Assets',
        'liability' => 'Liabilities',
        'equity' => 'Equity',
        'income' => 'Income',
        'expense' => 'Expenses',
    ];
    $typeBadgeClasses = [
        'asset' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        'liability' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300',
        'equity' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
        'income' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
        'expense' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
    ];
    $typeCardStyles = [
        'asset' => 'from-emerald-500/15 to-emerald-500/5 border-emerald-200/80 hover:border-emerald-300 dark:border-emerald-500/20 dark:hover:border-emerald-500/40',
        'liability' => 'from-orange-500/15 to-orange-500/5 border-orange-200/80 hover:border-orange-300 dark:border-orange-500/20 dark:hover:border-orange-500/40',
        'equity' => 'from-blue-500/15 to-blue-500/5 border-blue-200/80 hover:border-blue-300 dark:border-blue-500/20 dark:hover:border-blue-500/40',
        'income' => 'from-sky-500/15 to-sky-500/5 border-sky-200/80 hover:border-sky-300 dark:border-sky-500/20 dark:hover:border-sky-500/40',
        'expense' => 'from-rose-500/15 to-rose-500/5 border-rose-200/80 hover:border-rose-300 dark:border-rose-500/20 dark:hover:border-rose-500/40',
    ];
    $selectedTypeLabel = $selectedType ? ($typeLabels[$selectedType] ?? ucfirst($selectedType)) : null;
@endphp

<div class="space-y-6"
     x-data="{
        showAccount: false,
        selectedAccount: null,
     }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Chart of Accounts</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Review account codes, types, and status in one simple list.
            </p>
        </div>

        <a href="{{ route('admin.accounts.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add account
        </a>
    </div>


    @if($totalAccounts > 0)
        <div class="grid gap-4 xl:grid-cols-[1.2fr_3fr]">
            <a href="{{ route('admin.accounts.index') }}"
               class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition-all hover:-translate-y-0.5 hover:shadow-sm dark:border-gray-800 dark:bg-gray-900 {{ $selectedType === null ? 'ring-2 ring-brand-500/40 dark:ring-brand-500/30' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">All accounts</p>
                        <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $totalAccounts }}</div>
                    </div>
                    <div class="rounded-xl bg-brand-50 p-3 text-brand-600 transition group-hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300 dark:group-hover:bg-brand-500/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M7 12h10m-7 5h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <div><span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $activeAccounts }}</span> active</div>
                    <div><span class="font-semibold text-gray-700 dark:text-gray-300">{{ $inactiveAccounts }}</span> inactive</div>
                </div>
            </a>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Browse by type</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Click a card to focus the table on one account family.</p>
                    </div>
                    @if($selectedTypeLabel)
                        <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                            Filtering: {{ $selectedTypeLabel }}
                        </span>
                    @endif
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach($typeLabels as $typeKey => $typeLabel)
                        <a href="{{ route('admin.accounts.index', ['type' => $typeKey]) }}"
                           class="group rounded-2xl border bg-gradient-to-br p-4 transition-all hover:-translate-y-0.5 hover:shadow-sm dark:bg-gray-800/60 {{ $typeCardStyles[$typeKey] ?? 'from-gray-100 to-gray-50 border-gray-200 dark:border-gray-700' }} {{ $selectedType === $typeKey ? 'ring-2 ring-brand-500/35 dark:ring-brand-500/25' : '' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $typeLabel }}</div>
                                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ $accountTypes[$typeKey] ?? 0 }}</div>
                                </div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium {{ $typeBadgeClasses[$typeKey] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                    View
                                </span>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $selectedType === $typeKey ? 'Showing rows below' : 'Open filtered list' }}</span>
                                <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Accounts</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if($selectedTypeLabel)
                            Showing {{ strtolower($selectedTypeLabel) }} accounts only.
                        @else
                            Code-first list for faster scanning and maintenance.
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($selectedTypeLabel)
                        <a href="{{ route('admin.accounts.index') }}"
                           class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Clear filter
                        </a>
                    @endif
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $visibleAccountsCount }} shown
                    </span>
                </div>
            </div>

            @if($accounts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/60">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Code</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Account</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Updated</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($accounts as $account)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                    <td class="px-5 py-4 align-top">
                                        <button type="button"
                                                class="font-mono text-sm font-semibold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-300"
                                                @click="selectedAccount = @js([
                                                    'id' => $account->id,
                                                    'code' => $account->code,
                                                    'name' => $account->name,
                                                    'type' => $account->type,
                                                    'type_label' => $typeLabels[$account->type] ?? ucfirst($account->type),
                                                    'type_badge' => $typeBadgeClasses[$account->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                                    'is_active' => (bool) $account->is_active,
                                                    'status_label' => $account->is_active ? 'Active' : 'Inactive',
                                                    'status_badge' => $account->is_active
                                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                                    'created' => optional($account->created_at)->format('d M Y'),
                                                    'updated' => optional($account->updated_at)->diffForHumans(),
                                                    'edit_url' => route('admin.accounts.edit', $account),
                                                    'delete_url' => route('admin.accounts.destroy', $account),
                                                ]); showAccount = true">
                                            {{ $account->code }}
                                        </button>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $account->name }}</div>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $typeBadgeClasses[$account->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                            {{ $typeLabels[$account->type] ?? ucfirst($account->type) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $account->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 align-top text-sm text-gray-500 dark:text-gray-400">
                                        {{ optional($account->updated_at)->diffForHumans() }}
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                                    @click="selectedAccount = @js([
                                                        'id' => $account->id,
                                                        'code' => $account->code,
                                                        'name' => $account->name,
                                                        'type' => $account->type,
                                                        'type_label' => $typeLabels[$account->type] ?? ucfirst($account->type),
                                                        'type_badge' => $typeBadgeClasses[$account->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                                        'is_active' => (bool) $account->is_active,
                                                        'status_label' => $account->is_active ? 'Active' : 'Inactive',
                                                        'status_badge' => $account->is_active
                                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300'
                                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                                        'created' => optional($account->created_at)->format('d M Y'),
                                                        'updated' => optional($account->updated_at)->diffForHumans(),
                                                        'edit_url' => route('admin.accounts.edit', $account),
                                                        'delete_url' => route('admin.accounts.destroy', $account),
                                                    ]); showAccount = true">
                                                View
                                            </button>
                                            <a href="{{ route('admin.accounts.edit', $account) }}"
                                               class="rounded-lg bg-brand-500 px-3 py-2 text-xs font-medium text-white hover:bg-brand-600">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">No {{ strtolower($selectedTypeLabel ?? 'account') }} records found</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Try another account type or clear the current filter to see the full chart.
                    </p>
                </div>
            @endif
        </div>

        <div x-show="showAccount && selectedAccount"
             x-cloak
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/60 px-4 py-6"
             @keydown.escape.window="showAccount = false">
            <div x-show="showAccount && selectedAccount"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 class="w-full max-w-2xl rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
                 @click.outside="showAccount = false">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Account details</div>
                        <h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white" x-text="selectedAccount.name"></h2>
                        <p class="mt-1 font-mono text-sm text-gray-500 dark:text-gray-400" x-text="selectedAccount.code"></p>
                    </div>
                    <button type="button"
                            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            @click="showAccount = false">
                        Close
                    </button>
                </div>

                <div class="space-y-5 px-6 py-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/60">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</div>
                            <div class="mt-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="selectedAccount.type_badge" x-text="selectedAccount.type_label"></span>
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/60">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</div>
                            <div class="mt-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="selectedAccount.status_badge" x-text="selectedAccount.status_label"></span>
                            </div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/60">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Created</div>
                            <div class="mt-2 text-sm font-medium text-gray-900 dark:text-white" x-text="selectedAccount.created"></div>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/60">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Updated</div>
                            <div class="mt-2 text-sm font-medium text-gray-900 dark:text-white" x-text="selectedAccount.updated"></div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        This screen keeps account maintenance simple. Use the edit page for changes, and use finance reports for transaction analysis.
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                    <form :action="selectedAccount.delete_url" method="POST" onsubmit="return confirm('Delete this account?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="rounded-lg border border-error-200 bg-error-50 px-4 py-2.5 text-sm font-medium text-error-700 hover:bg-error-100 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
                            Delete
                        </button>
                    </form>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                @click="showAccount = false">
                            Close
                        </button>
                        <a :href="selectedAccount.edit_url"
                           class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                            Edit account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-3xl border border-gray-200 bg-white px-6 py-16 text-center shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">No accounts yet</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create your first chart-of-accounts entry to start organizing ledger categories.</p>
            <div class="mt-6">
                <a href="{{ route('admin.accounts.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add first account
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush
