@extends('layouts.app')

@section('content')
@php
    $input = 'w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white';
    $label = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Shipment Rate Cards</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set automatic pricing by mode, client, agent, route, and billing unit.</p>
        </div>
        <a href="{{ route('admin.shipments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Shipments</a>
    </div>

    @if(session('status'))
        <div class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Add Rate Card</h2>
        <form method="POST" action="{{ route('admin.shipment-rate-cards.store') }}" class="mt-4 grid gap-4 md:grid-cols-4">
            @csrf
            <div>
                <label class="{{ $label }}">Mode</label>
                <select name="mode" class="{{ $input }}" required>
                    @foreach($modes as $mode)
                        <option value="{{ $mode }}" @selected(old('mode') === $mode)>{{ strtoupper(str_replace('_', ' ', $mode)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Billing Unit</label>
                <select name="billing_unit" class="{{ $input }}" required>
                    @foreach($billingUnits as $unit)
                        <option value="{{ $unit }}" @selected(old('billing_unit') === $unit)>{{ ucwords(str_replace('_', ' ', $unit)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Rate</label>
                <input type="number" step="0.01" min="0" name="rate" value="{{ old('rate') }}" class="{{ $input }}" required>
            </div>
            <div>
                <label class="{{ $label }}">Minimum Charge</label>
                <input type="number" step="0.01" min="0" name="minimum_charge" value="{{ old('minimum_charge', 0) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Client</label>
                <select name="client_id" class="{{ $input }}">
                    <option value="">Any client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Agent</label>
                <select name="agent_id" class="{{ $input }}">
                    <option value="">Any agent</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Origin Country</label>
                <input name="origin_country" value="{{ old('origin_country', 'China') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Destination Country</label>
                <input name="destination_country" value="{{ old('destination_country', 'Bangladesh') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Volumetric Divisor</label>
                <input type="number" min="1" name="volumetric_divisor" value="{{ old('volumetric_divisor', 5000) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Effective From</label>
                <input type="date" name="effective_from" value="{{ old('effective_from') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Effective To</label>
                <input type="date" name="effective_to" value="{{ old('effective_to') }}" class="{{ $input }}">
            </div>
            <label class="flex items-end gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-brand-500">
                Active
            </label>
            <div class="md:col-span-4">
                <label class="{{ $label }}">Notes</label>
                <textarea name="notes" rows="2" class="{{ $input }}">{{ old('notes') }}</textarea>
            </div>
            <div class="md:col-span-4">
                <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Save Rate Card</button>
            </div>
        </form>
    </section>

    <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Rate Cards</h2>
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="mode" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All modes</option>
                    @foreach($modes as $mode)
                        <option value="{{ $mode }}" @selected(request('mode') === $mode)>{{ strtoupper(str_replace('_', ' ', $mode)) }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Any status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Filter</button>
            </form>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="py-2">Scope</th>
                        <th class="py-2">Route</th>
                        <th class="py-2">Mode</th>
                        <th class="py-2">Unit</th>
                        <th class="py-2 text-right">Rate</th>
                        <th class="py-2 text-right">Minimum</th>
                        <th class="py-2">Validity</th>
                        <th class="py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($rateCards as $card)
                        <tr>
                            <td class="py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $card->client?->name ?? 'Any client' }}</div>
                                <div class="text-xs text-gray-500">{{ $card->agent?->name ?? 'Any agent' }} · {{ $card->is_active ? 'Active' : 'Inactive' }}</div>
                            </td>
                            <td class="py-3">{{ $card->origin_country ?? 'Any origin' }} -> {{ $card->destination_country ?? 'Any destination' }}</td>
                            <td class="py-3">{{ strtoupper(str_replace('_', ' ', $card->mode)) }}</td>
                            <td class="py-3">{{ ucwords(str_replace('_', ' ', $card->billing_unit)) }}<div class="text-xs text-gray-500">Divisor {{ $card->volumetric_divisor }}</div></td>
                            <td class="py-3 text-right">{{ number_format((float) $card->rate, 2) }}</td>
                            <td class="py-3 text-right">{{ number_format((float) $card->minimum_charge, 2) }}</td>
                            <td class="py-3 text-xs text-gray-500">
                                {{ $card->effective_from?->format('d M Y') ?? 'Any start' }} -
                                {{ $card->effective_to?->format('d M Y') ?? 'No end' }}
                            </td>
                            <td class="py-3 text-right">
                                <form method="POST" action="{{ route('admin.shipment-rate-cards.destroy', $card) }}" onsubmit="return confirm('Delete this rate card?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-semibold text-error-600 dark:text-error-400">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-6 text-center text-gray-500">No shipment rate cards yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $rateCards->links() }}</div>
    </section>
</div>
@endsection
