@extends('layouts.portal')

@section('title', 'Client Deliveries')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Deliveries</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track dispatch, route, vehicle, and POD status.</p>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Order</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Route</th>
                        <th class="px-4 py-3 text-left">Vehicle</th>
                        <th class="px-4 py-3 text-left">POD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td class="px-4 py-3">Order #{{ $delivery->order_id }}</td>
                            <td class="px-4 py-3">{{ ucwords(str_replace('_', ' ', $delivery->status)) }}</td>
                            <td class="px-4 py-3">{{ $delivery->route?->name ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $delivery->vehicle?->name ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $delivery->pod || $delivery->pod_photo ? 'Available' : 'Pending' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No deliveries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $deliveries->links() }}
</div>
@endsection
