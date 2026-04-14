@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Shipments</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Courier, air, sea LCL/FCL, and DDP shipment tracking.</p>
        </div>
        <a href="{{ route('admin.shipments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
            New Shipment
        </a>
    </div>

    <form method="GET" class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:grid-cols-3">
        <select name="mode" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">All modes</option>
            @foreach($modes as $mode)
                <option value="{{ $mode }}" @selected(request('mode') === $mode)>{{ strtoupper(str_replace('_', ' ', $mode)) }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Filter</button>
            <a href="{{ route('admin.shipments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Clear</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/70 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Shipment</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Mode</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Chargeable KG</th>
                        <th class="px-4 py-3 text-right">Estimated</th>
                        <th class="px-4 py-3 text-right">Final</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($shipments as $shipment)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.shipments.show', $shipment) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-300">
                                    {{ $shipment->shipment_no }}
                                </a>
                                <div class="text-xs text-gray-500">Order #{{ $shipment->order_id ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $shipment->client?->name ?? $shipment->agent?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ strtoupper(str_replace('_', ' ', $shipment->mode)) }}</td>
                            <td class="px-4 py-3">{{ ucwords(str_replace('_', ' ', $shipment->status)) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($shipment->packages->sum('chargeable_weight_kg'), 3) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((float) $shipment->estimated_price, 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ $shipment->final_price === null ? '-' : number_format((float) $shipment->final_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">No shipments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $shipments->links() }}
</div>
@endsection
