@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $client->name }}</h1>
            @if($client->company_name)
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->company_name }}</p>
            @endif
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.clients.edit', $client) }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Edit</a>
            <a href="{{ route('admin.clients.index') }}"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Credit Limit</p>
            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $client->currency }} {{ number_format($client->credit_limit, 2) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Outstanding</p>
            <p class="mt-1 text-xl font-bold {{ $outstanding > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                {{ $client->currency }} {{ number_format($outstanding, 2) }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Total Orders</p>
            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $orders->count() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Withholding</p>
            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ $client->withholding_rate }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Details --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 space-y-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Details</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $client->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Phone</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $client->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Currency</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $client->currency }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Assigned Agent</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $client->agent?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd>
                        @if($client->is_active)
                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                        @endif
                    </dd>
                </div>
                @if($client->address)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400 mb-1">Address</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $client->address }}</dd>
                </div>
                @endif
                @if($client->notes)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400 mb-1">Notes</dt>
                    <dd class="text-gray-600 dark:text-gray-300 text-xs">{{ $client->notes }}</dd>
                </div>
                @endif
            </dl>

            @if($portalUsers->count())
            <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-2">Portal Users</p>
                @foreach($portalUsers as $pUser)
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $pUser->name }} <span class="text-gray-400">{{ $pUser->email }}</span></p>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Shipments --}}
        <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Shipments</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Shipment No</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Mode</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Final Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($shipments as $shipment)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.shipments.show', $shipment) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $shipment->shipment_no }}</a>
                        </td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ strtoupper(str_replace('_',' ',$shipment->mode)) }}</td>
                        <td class="px-4 py-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ str_replace('_',' ', $shipment->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-gray-900 dark:text-white">
                            {{ $client->currency }} {{ number_format($shipment->final_price ?? $shipment->estimated_price ?? 0, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No shipments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Recent Invoices --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Recent Invoices</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Invoice #</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Issued</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Total</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Outstanding</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($invoices as $invoice)
                <tr>
                    <td class="px-4 py-2 font-mono text-gray-900 dark:text-white">{{ $invoice->number }}</td>
                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ ucfirst($invoice->invoice_type ?? 'final') }}</td>
                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-2 text-right font-mono text-gray-900 dark:text-white">{{ number_format($invoice->cash_total ?? 0, 2) }}</td>
                    <td class="px-4 py-2 text-right font-mono {{ (float)$invoice->outstanding > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                        {{ number_format($invoice->outstanding ?? 0, 2) }}
                    </td>
                    <td class="px-4 py-2">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $invoice->status ?? '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
