@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="absolute -inset-1 rounded-full bg-gradient-to-r from-brand-500 to-brand-600 blur opacity-25"></div>
                <div class="relative flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75l7.5-6 7.5 6M4.5 9.75v9.75a.75.75 0 00.75.75H9.75v-6h4.5v6h4.5a.75.75 0 00.75-.75V9.75" />
                    </svg>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Batch trace: {{ $batch->batch_code }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $batch->product->name ?? 'Unknown product' }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                QC status:
                <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold
                    @if($batch->qc_status === 'approved')
                        bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300
                    @elseif($batch->qc_status === 'rejected')
                        bg-error-100 text-error-700 dark:bg-error-900/40 dark:text-error-300
                    @else
                        bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300
                    @endif">
                    {{ ucfirst($batch->qc_status) }}
                </span>
            </span>
        </div>
    </div>

    {{-- Key facts --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900/70">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Production date</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ optional($batch->production_date)->format('d M Y') ?? '—' }}
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                Expiry: {{ optional($batch->expiry_date)->format('d M Y') ?? 'Not set' }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900/70">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Produced (units)</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                {{ number_format($producedQty) }}
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">From confirmed production runs</div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900/70">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">On hand</div>
            <div class="mt-1 text-2xl font-semibold text-success-600 dark:text-success-400">
                {{ number_format($onHandQty, 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Available across all warehouses</div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900/70">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Reserved</div>
            <div class="mt-1 text-2xl font-semibold text-warning-600 dark:text-warning-400">
                {{ number_format($reservedQty, 2) }}
            </div>
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Linked to open orders</div>
        </div>
    </div>

    {{-- Losses / returns --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-error-200 bg-error-50/70 p-4 shadow-xs dark:border-error-900/60 dark:bg-error-950/40">
            <div class="text-xs font-medium uppercase tracking-wide text-error-700 dark:text-error-300">Written off</div>
            <div class="mt-1 text-2xl font-semibold text-error-700 dark:text-error-300">
                {{ number_format($writtenOffQty, 2) }}
            </div>
            <div class="mt-2 text-xs text-error-700/80 dark:text-error-200/90">Expired / wasted / production loss</div>
        </div>

        <div class="rounded-2xl border border-brand-200 bg-brand-50/70 p-4 shadow-xs dark:border-brand-900/60 dark:bg-brand-950/40">
            <div class="text-xs font-medium uppercase tracking-wide text-brand-700 dark:text-brand-300">Customer returns</div>
            <div class="mt-1 text-2xl font-semibold text-brand-700 dark:text-brand-300">
                {{ number_format($customerReturnQty, 2) }}
            </div>
            <div class="mt-2 text-xs text-brand-700/80 dark:text-brand-200/90">Returned from field</div>
        </div>
    </div>

    {{-- Production runs --}}
    @if($batch->productionRuns->isNotEmpty())
        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Production runs</h2>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/60">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Order</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Warehouse</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quantity</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Stock confirmed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($batch->productionRuns as $run)
                            <tr>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                    {{ $run->order_number ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    {{ $run->warehouse->name ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                    {{ number_format($run->quantity) }}
                                </td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ ucfirst($run->status ?? 'planned') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                    {{ optional($run->stock_confirmed_at)->format('d M Y H:i') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Current stock by warehouse --}}
    @php
        $entriesByWarehouse = $batch->stockEntries->groupBy('warehouse_id');
    @endphp
    @if($entriesByWarehouse->isNotEmpty())
        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Current stock by warehouse</h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($entriesByWarehouse as $warehouseId => $entries)
                    @php
                        $warehouse = $entries->first()->warehouse ?? null;
                        $available = $entries->where('status', 'available')->sum('quantity');
                        $reserved  = $entries->where('status', 'reserved')->sum('quantity');
                    @endphp
                    <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-xs dark:border-gray-800 dark:bg-gray-900/80">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $warehouse->name ?? 'Warehouse #'.$warehouseId }}
                                </div>
                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $warehouse->location ?? 'No address set' }}
                                </div>
                            </div>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Available</dt>
                                <dd class="mt-1 font-semibold text-success-600 dark:text-success-400">
                                    {{ number_format($available, 2) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Reserved</dt>
                                <dd class="mt-1 font-semibold text-warning-600 dark:text-warning-400">
                                    {{ number_format($reserved, 2) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Movement timeline --}}
    <div class="space-y-3">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Movement timeline</h2>

        @if($movements->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white/70 p-4 text-sm text-gray-500 shadow-xs dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-400">
                No stock movements recorded yet for this batch.
            </div>
        @else
            <div class="space-y-3">
                @foreach($movements as $movement)
                    @php
                        $entry    = $movement->stockEntry;
                        $order    = $movement->order;
                        $sign     = $movement->quantity >= 0 ? '+' : '−';
                        $quantity = number_format(abs($movement->quantity), 2);
                    @endphp
                    <div class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white/80 p-3 text-sm shadow-xs dark:border-gray-800 dark:bg-gray-900/80">
                        <div class="mt-1 h-2 w-2 flex-shrink-0 rounded-full
                            @if($movement->quantity < 0)
                                bg-error-500
                            @else
                                bg-success-500
                            @endif">
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-medium {{ $movement->type_badge_class }}">
                                        {{ $movement->type_label }}
                                    </span>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ $sign }}{{ $quantity }} {{ $batch->product->uom ?? 'units' }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ optional($movement->created_at)->format('d M Y, H:i') }}
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Warehouse: {{ $entry->warehouse->name ?? 'N/A' }}
                                @if($order)
                                    · Order:
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        #{{ $order->id }} ({{ $order->agent->name ?? 'Agent' }})
                                    </a>
                                @endif
                            </div>
                            @if($movement->notes)
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $movement->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
