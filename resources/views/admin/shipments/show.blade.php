@extends('layouts.app')

@section('content')
@php
    $label = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
    $input = 'w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $shipment->shipment_no }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $shipment->agent?->name ?? 'Unknown client' }} - {{ strtoupper(str_replace('_', ' ', $shipment->mode)) }} - {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.shipments.invoice', $shipment) }}">
                @csrf
                <input type="hidden" name="invoice_type" value="proforma">
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Create Proforma</button>
            </form>
            <form method="POST" action="{{ route('admin.shipments.invoice', $shipment) }}">
                @csrf
                <input type="hidden" name="invoice_type" value="final">
                <button class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">Create Final Invoice</button>
            </form>
            <a href="{{ route('admin.shipments.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">Actual KG</div>
            <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($shipment->total_actual_weight_kg, 3) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">CBM</div>
            <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($shipment->total_cbm, 4) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">Chargeable KG</div>
            <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($shipment->total_chargeable_weight_kg, 3) }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs uppercase text-gray-500">Final Price</div>
            <div class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $shipment->final_price === null ? '-' : number_format((float) $shipment->final_price, 2) }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Packages</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-2">Description</th>
                            <th class="py-2 text-right">Pieces</th>
                            <th class="py-2 text-right">Actual KG</th>
                            <th class="py-2 text-right">CBM</th>
                            <th class="py-2 text-right">Vol KG</th>
                            <th class="py-2 text-right">Charge KG</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($shipment->packages as $package)
                            <tr>
                                <td class="py-2">{{ $package->description ?? 'Package' }}</td>
                                <td class="py-2 text-right">{{ $package->pieces }}</td>
                                <td class="py-2 text-right">{{ number_format((float) $package->actual_weight_kg, 3) }}</td>
                                <td class="py-2 text-right">{{ number_format((float) $package->cbm, 4) }}</td>
                                <td class="py-2 text-right">{{ number_format((float) $package->volumetric_weight_kg, 3) }}</td>
                                <td class="py-2 text-right">{{ number_format((float) $package->chargeable_weight_kg, 3) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-gray-500">No packages recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('admin.shipments.packages.store', $shipment) }}" class="mt-5 grid gap-3 md:grid-cols-6">
                @csrf
                <input name="description" placeholder="Goods" class="{{ $input }} md:col-span-2">
                <input type="number" name="pieces" min="1" value="1" class="{{ $input }}">
                <input type="number" step="0.001" name="actual_weight_kg" placeholder="KG" class="{{ $input }}">
                <input type="number" step="0.01" name="length_cm" placeholder="L cm" class="{{ $input }}">
                <input type="number" step="0.01" name="width_cm" placeholder="W cm" class="{{ $input }}">
                <input type="number" step="0.01" name="height_cm" placeholder="H cm" class="{{ $input }}">
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900 md:col-span-6">Add Package</button>
            </form>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status & Pricing</h2>
            <form method="POST" action="{{ route('admin.shipments.status.update', $shipment) }}" class="mt-4 space-y-3">
                @csrf
                @method('PATCH')
                <label class="{{ $label }}">Shipment Status</label>
                <select name="status" class="{{ $input }}">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($shipment->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Update Status</button>
            </form>

            <form method="POST" action="{{ route('admin.shipments.pricing.lock', $shipment) }}" class="mt-6 space-y-3">
                @csrf
                <label class="{{ $label }}">Final Rate Per Chargeable KG</label>
                <input type="number" step="0.01" min="0" name="final_unit_rate" value="{{ old('final_unit_rate', $shipment->final_unit_rate ?? $shipment->estimated_unit_rate) }}" class="{{ $input }}">
                <button class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">Lock Pricing</button>
                @if($shipment->pricing_locked)
                    <p class="text-xs text-gray-500">Locked {{ optional($shipment->pricing_locked_at)->diffForHumans() }}.</p>
                @endif
            </form>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Shipment Legs</h2>
            <div class="mt-4 space-y-3">
                @foreach($shipment->legs as $leg)
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $leg->sequence }}. {{ ucwords(str_replace('_', ' ', $leg->leg_type)) }}</div>
                            <div class="text-xs text-gray-500">{{ ucwords(str_replace('_', ' ', $leg->status)) }}</div>
                        </div>
                        <div class="mt-1 text-sm text-gray-500">{{ $leg->from_location ?? '-' }} -> {{ $leg->to_location ?? '-' }}</div>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.shipments.legs.store', $shipment) }}" class="mt-5 grid gap-3 md:grid-cols-2">
                @csrf
                <input type="number" min="1" name="sequence" placeholder="Sequence" class="{{ $input }}">
                <select name="leg_type" class="{{ $input }}">
                    @foreach(['origin','international','customs','last_mile'] as $type)
                        <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
                <select name="status" class="{{ $input }}">
                    @foreach(['pending','received','in_transit','customs','out_for_delivery','delivered','exception'] as $status)
                        <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <input name="from_location" placeholder="From" class="{{ $input }}">
                <input name="to_location" placeholder="To" class="{{ $input }}">
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Save Leg</button>
            </form>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Expenses</h2>
            <div class="mt-4 space-y-3">
                @forelse($shipment->expenses as $expense)
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="flex justify-between gap-3">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $expense->expense_type)) }}</div>
                                <div class="text-xs text-gray-500">{{ ucwords($expense->status) }}</div>
                            </div>
                            <div class="text-right text-sm">
                                <div>Est: {{ number_format((float) $expense->estimated_amount, 2) }}</div>
                                <div>Act: {{ $expense->actual_amount === null ? '-' : number_format((float) $expense->actual_amount, 2) }}</div>
                            </div>
                        </div>
                        @if($expense->status !== 'approved')
                            <form method="POST" action="{{ route('admin.shipments.expenses.approve', [$shipment, $expense]) }}" class="mt-2">
                                @csrf
                                <button class="text-xs font-semibold text-brand-600 dark:text-brand-300">Approve</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No expenses recorded.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('admin.shipments.expenses.store', $shipment) }}" class="mt-5 grid gap-3 md:grid-cols-2">
                @csrf
                <select name="expense_type" class="{{ $input }}">
                    @foreach($expenseTypes as $type)
                        <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
                <select name="shipment_leg_id" class="{{ $input }}">
                    <option value="">No leg</option>
                    @foreach($shipment->legs as $leg)
                        <option value="{{ $leg->id }}">{{ $leg->sequence }} - {{ ucwords(str_replace('_', ' ', $leg->leg_type)) }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" name="estimated_amount" placeholder="Estimated" class="{{ $input }}">
                <input type="number" step="0.01" name="actual_amount" placeholder="Actual" class="{{ $input }}">
                <select name="status" class="{{ $input }}">
                    @foreach(['draft','submitted','approved','rejected'] as $status)
                        <option value="{{ $status }}">{{ ucwords($status) }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Add Expense</button>
            </form>
        </section>
    </div>

    <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Invoices</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="py-2">Number</th>
                        <th class="py-2">Type</th>
                        <th class="py-2">Status</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($shipment->invoices as $invoice)
                        <tr>
                            <td class="py-2"><a class="text-brand-600 dark:text-brand-300" href="{{ route('admin.finance.show', $invoice) }}">{{ $invoice->number }}</a></td>
                            <td class="py-2">{{ ucwords($invoice->invoice_type ?? 'standard') }}</td>
                            <td class="py-2">{{ ucwords($invoice->status) }}</td>
                            <td class="py-2 text-right">{{ number_format((float) $invoice->net_total + (float) $invoice->vat_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-500">No invoices issued.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
