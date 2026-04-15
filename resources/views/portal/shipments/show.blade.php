@extends('layouts.app')

@section('title', $shipment->shipment_no)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $shipment->shipment_no }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ strtoupper(str_replace('_', ' ', $shipment->mode)) }} • {{ ucwords(str_replace('_', ' ', $shipment->status)) }}</p>
        </div>
        <a href="{{ route('portal.shipments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</a>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Actual KG</div><div class="mt-2 text-xl font-semibold">{{ number_format($shipment->total_actual_weight_kg, 3) }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">CBM</div><div class="mt-2 text-xl font-semibold">{{ number_format($shipment->total_cbm, 4) }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Chargeable KG</div><div class="mt-2 text-xl font-semibold">{{ number_format($shipment->total_chargeable_weight_kg, 3) }}</div></div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-xs uppercase text-gray-500">Final Price</div><div class="mt-2 text-xl font-semibold">{{ $shipment->final_price === null ? '-' : 'BDT ' . number_format((float) $shipment->final_price, 2) }}</div></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold">Shipment Legs</h2>
            <div class="mt-4 space-y-3">
                @foreach($shipment->legs as $leg)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div class="font-medium">{{ $leg->sequence }}. {{ ucwords(str_replace('_', ' ', $leg->leg_type)) }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $leg->status)) }}</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $leg->from_location ?: '-' }} → {{ $leg->to_location ?: '-' }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold">Proof of Delivery</h2>
            @if($shipment->pod)
                <div class="mt-4 space-y-3 text-sm">
                    <div><span class="text-gray-500 dark:text-gray-400">Received by:</span> {{ $shipment->pod->received_by ?: '-' }}</div>
                    <div><span class="text-gray-500 dark:text-gray-400">Receiver phone:</span> {{ $shipment->pod->receiver_phone ?: '-' }}</div>
                    <div><span class="text-gray-500 dark:text-gray-400">Delivered at:</span> {{ $shipment->pod->delivered_at?->format('d M Y H:i') ?: '-' }}</div>
                    @if($shipment->pod->notes)
                        <div><span class="text-gray-500 dark:text-gray-400">Notes:</span> {{ $shipment->pod->notes }}</div>
                    @endif
                    @if($shipment->pod->document_path)
                        <a href="{{ asset('storage/' . $shipment->pod->document_path) }}" target="_blank" class="inline-flex text-brand-600 dark:text-brand-300">Open POD document</a>
                    @endif
                </div>
            @else
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">POD has not been uploaded yet.</p>
            @endif
        </section>
    </div>
</div>
@endsection
