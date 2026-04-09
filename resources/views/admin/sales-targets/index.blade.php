@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Sales Targets</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track monthly targets for agents and sales employees.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sales-targets.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Prev</a>
            <span class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $month->format('F Y') }}</span>
            <a href="{{ route('admin.sales-targets.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Next</a>
            <a href="{{ route('admin.sales-targets.create') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-sm hover:bg-brand-600">New Target</a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Owner</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Period</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Target</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Achieved</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Remaining</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Progress</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($targets as $row)
                        @php($target = $row['target'])
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $target->agent?->name ?? $target->employee?->name ?? 'Unassigned' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $target->agent_id ? 'Agent target' : 'Employee target' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $target->period_start->format('d M Y') }} to {{ $target->period_end->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">BDT {{ number_format($target->target_value, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">BDT {{ number_format($row['achieved'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">BDT {{ number_format($row['remaining'], 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-32 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                                        <div class="h-2 rounded-full bg-brand-500" style="width: {{ $row['progress'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ number_format($row['progress'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.sales-targets.edit', $target) }}" class="inline-flex rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No sales targets found for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
            {{ $targets->links() }}
        </div>
    </div>
</div>
@endsection
