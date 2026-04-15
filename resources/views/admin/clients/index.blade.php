@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Clients</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage cargo clients, credit limits, and currency preferences.</p>
        </div>
        <a href="{{ route('admin.clients.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            New Client
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
        <div class="relative flex-1 min-w-[220px]">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/>
            </svg>
            <input name="search" value="{{ request('search') }}"
                   placeholder="Search name, company, email, phone…"
                   class="w-full rounded-lg border-gray-300 pl-9 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        </div>
        <select name="status" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">All statuses</option>
            <option value="active"   @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
            Filter
        </button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.clients.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400">
                Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Client</th>
                        <th class="px-5 py-3.5 text-left">Contact</th>
                        <th class="px-5 py-3.5 text-left">Agent</th>
                        <th class="px-5 py-3.5 text-right">Credit Limit</th>
                        <th class="px-5 py-3.5 text-left">Currency</th>
                        <th class="px-5 py-3.5 text-left">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($clients as $client)
                    <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/40">

                        {{-- Client name + company --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                                    {{ strtoupper(substr($client->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.clients.show', $client) }}"
                                       class="font-semibold text-brand-600 hover:underline dark:text-brand-400">
                                        {{ $client->name }}
                                    </a>
                                    @if($client->company_name)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $client->company_name }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Contact --}}
                        <td class="px-5 py-4">
                            <div class="text-gray-700 dark:text-gray-300">{{ $client->email ?? '—' }}</div>
                            @if($client->phone)
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $client->phone }}</div>
                            @endif
                        </td>

                        {{-- Agent --}}
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-400">
                            {{ $client->agent?->name ?? '—' }}
                        </td>

                        {{-- Credit limit --}}
                        <td class="px-5 py-4 text-right font-mono font-medium text-gray-900 dark:text-white">
                            {{ $client->currency }} {{ number_format($client->credit_limit, 2) }}
                        </td>

                        {{-- Currency --}}
                        <td class="px-5 py-4">
                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $client->currency }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @if($client->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.clients.show', $client) }}"
                                   class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-400 dark:hover:text-brand-400">
                                    View
                                </a>
                                <a href="{{ route('admin.clients.edit', $client) }}"
                                   class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-400 dark:hover:text-brand-400">
                                    Edit
                                </a>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-600">
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                </svg>
                                <p class="text-sm font-medium">No clients found</p>
                                <p class="text-xs">Try adjusting your search or filter, or add a new client.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $clients->links() }}

</div>
@endsection
