@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">New Shipment</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create the logistics record for China to Bangladesh movement.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.shipment-rate-cards.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Rate Cards</a>
            <a href="{{ route('admin.shipments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.shipments.store') }}" class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Shipment No</label>
                <input name="shipment_no" value="{{ old('shipment_no', $shipment->shipment_no) }}" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mode</label>
                <select name="mode" required class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @foreach($modes as $mode)
                        <option value="{{ $mode }}" @selected(old('mode') === $mode)>{{ strtoupper(str_replace('_', ' ', $mode)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Client <span class="text-red-500">*</span></label>
                <select name="client_id" required class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Select client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}{{ $client->company_name ? ' — ' . $client->company_name : '' }}</option>
                    @endforeach
                </select>
                @error('client_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Internal Agent (Salesperson)</label>
                <select name="agent_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Use client's assigned agent</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>{{ $agent->name }}</option>
                    @endforeach
                </select>
                @error('agent_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Related Order</label>
                <select name="order_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">No order link</option>
                    @foreach($orders as $order)
                        <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>#{{ $order->id }} - {{ $order->client?->name ?? 'Unknown' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Origin Country</label>
                <input name="origin_country" value="{{ old('origin_country', $shipment->origin_country) }}" required class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Destination Country</label>
                <input name="destination_country" value="{{ old('destination_country', $shipment->destination_country) }}" required class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Origin Warehouse</label>
                <select name="origin_warehouse_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Not assigned</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('origin_warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Destination Warehouse</label>
                <select name="destination_warehouse_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Not assigned</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('destination_warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Departure</label>
                <input type="date" name="estimated_departure" value="{{ old('estimated_departure') }}" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Arrival</label>
                <input type="date" name="estimated_arrival" value="{{ old('estimated_arrival') }}" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Rate</label>
                <input type="number" step="0.01" min="0" name="estimated_unit_rate" value="{{ old('estimated_unit_rate') }}" placeholder="Auto from rate card" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <p class="mt-1 text-xs text-gray-500">Leave blank or 0 to use the best matching active rate card.</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Manual Pricing Basis</label>
                <select name="pricing_basis" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @foreach(['chargeable_kg' => 'Chargeable KG', 'cbm' => 'CBM', 'shipment' => 'Shipment'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('pricing_basis', 'chargeable_kg') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Manual Volumetric Divisor</label>
                <input type="number" min="1" name="volumetric_divisor" value="{{ old('volumetric_divisor', 5000) }}" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="mt-6">
            <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Create Shipment</button>
        </div>
    </form>
</div>
@endsection
