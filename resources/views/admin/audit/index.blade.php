@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Audit Logs</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Critical user and financial activity trail.</p>
    </div>

    <form method="GET" class="flex gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <input name="action" value="{{ request('action') }}" placeholder="Filter by action" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Filter</button>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/70 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Record</th>
                        <th class="px-4 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No audit logs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $logs->links() }}
</div>
@endsection
