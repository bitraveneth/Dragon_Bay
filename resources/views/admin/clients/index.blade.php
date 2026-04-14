@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Clients</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage cargo clients, credit limits, and currency preferences.</p>
        </div>
        <a href="{{ route('admin.clients.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700">
            + New Client
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input name="search" value="{{ request('search') }}" placeholder="Search name, company, email, phone…"
               class="w-72 rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        <select name="status" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.clients.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Client</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Contact</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Agent</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Credit Limit</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Currency</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.clients.show', $client) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                            {{ $client->name }}
                        </a>
                        @if($client->company_name)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $client->company_name }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        <div>{{ $client->email ?? '—' }}</div>
                        <div class="text-xs">{{ $client->phone ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $client->agent?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">
                        {{ $client->currency }} {{ number_format($client->credit_limit, 2) }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $client->currency }}</td>
                    <td class="px-4 py-3">
                        @if($client->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.clients.edit', $client) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No clients found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $clients->links() }}

</div>
@endsection
