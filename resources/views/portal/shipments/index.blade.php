@extends('layouts.portal')

@section('title', 'Client Shipments')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Shipments</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Live tracking for courier, air, sea, and DDP shipments.</p>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Shipment</th>
                        <th class="px-4 py-3 text-left">Mode</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Chargeable KG</th>
                        <th class="px-4 py-3 text-left">POD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($shipments as $shipment)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('portal.shipments.show', $shipment) }}" class="font-medium text-brand-600 dark:text-brand-300">{{ $shipment->shipment_no }}</a>
                            </td>
                            <td class="px-4 py-3">{{ strtoupper(str_replace('_', ' ', $shipment->mode)) }}</td>
                            <td class="px-4 py-3">{{ ucwords(str_replace('_', ' ', $shipment->status)) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($shipment->total_chargeable_weight_kg, 3) }}</td>
                            <td class="px-4 py-3">{{ $shipment->pod ? 'Available' : 'Pending' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No shipments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $shipments->links() }}
</div>
@endsection
