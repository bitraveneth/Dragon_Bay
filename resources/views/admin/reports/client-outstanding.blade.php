@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Client Outstanding Balance</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All invoiced balances yet to be collected from clients.</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <select name="agent_id" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">All agents</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" @selected($agentId == $agent->id)>{{ $agent->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="show_all" value="1" @checked(request()->boolean('show_all'))
                   class="rounded border-gray-300 text-brand-600 dark:border-gray-700">
            Include zero-balance clients
        </label>
        <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Filter</button>
    </form>

    {{-- Summary Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total Outstanding</p>
            <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($grandOutstanding, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Clients with Balance</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $rows->filter(fn($r) => $r['outstanding'] > 0)->count() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total Clients Shown</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $rows->count() }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Client</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Agent</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Invoiced</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Paid</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Outstanding</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Credit Limit</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Credit Used</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($rows->sortByDesc('outstanding') as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.clients.show', $row['client']) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-400">
                            {{ $row['client']->name }}
                        </a>
                        @if($row['client']->company_name)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $row['client']->company_name }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['client']->agent?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">{{ number_format($row['total_invoiced'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-green-600 dark:text-green-400">{{ number_format($row['total_paid'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono font-semibold {{ $row['outstanding'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ number_format($row['outstanding'], 2) }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-gray-600 dark:text-gray-400">
                        {{ $row['credit_limit'] > 0 ? number_format($row['credit_limit'], 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($row['credit_used_pct'] !== null)
                            @php $pct = $row['credit_used_pct']; @endphp
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 60 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                         style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-xs {{ $pct >= 90 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $pct }}%</span>
                            </div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No data found. Try enabling "Include zero-balance clients".</td>
                </tr>
                @endforelse
            </tbody>
            @if($rows->count())
            <tfoot class="border-t-2 border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Total</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-red-600 dark:text-red-400">{{ number_format($grandOutstanding, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

</div>
@endsection
