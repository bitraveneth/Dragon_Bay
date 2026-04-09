@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Production Order {{ $run->order_number ?? '' }}
                </h1>
                @php
                    $statusColors = [
                        'planned' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                        'confirmed' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                        'in_progress' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                        'completed' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                        'cancelled' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                    ];
                    $statusColor = $statusColors[$run->status ?? 'confirmed'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    
                    $qcColors = [
                        'pending' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                        'approved' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                        'rejected' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                    ];
                    $qcColor = $qcColors[$run->qc_status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                @endphp
                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium {{ $statusColor }}">
                    <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                        <circle cx="3" cy="3" r="3" />
                    </svg>
                    {{ ucfirst($run->status ?? 'confirmed') }}
                </span>
                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium {{ $qcColor }}">
                    <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                        <circle cx="3" cy="3" r="3" />
                    </svg>
                    QC: {{ ucfirst($run->qc_status) }}
                </span>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $run->product->name ?? 'Product' }} · Batch {{ $run->batch->batch_code ?? '—' }}
                </p>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Created {{ \Carbon\Carbon::parse($run->created_at)->format('d M Y, H:i') }}</span>
                <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                <span>Updated {{ \Carbon\Carbon::parse($run->updated_at)->diffForHumans() }}</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.production.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Runs
            </a>
            <button type="button" 
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
            @if($run->stock_confirmed_at && isset($stockEntry) && $stockEntry)
                <a href="{{ route('admin.stock.writeoff', [
                    'entry_id'     => $stockEntry->id,
                    'warehouse_id' => $stockEntry->warehouse_id,
                    'product_id'   => $stockEntry->product_id,
                    'batch_id'     => $stockEntry->batch_id,
                    'reason'       => 'production-loss',
                ]) }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-orange-600 shadow-theme-xs hover:bg-orange-50 hover:text-orange-700 dark:border-gray-700 dark:bg-gray-800 dark:text-orange-500 dark:hover:bg-orange-500/10">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Quick Write-off
                </a>
            @endif
        </div>
    </div>

    <!-- Status Message -->

    <!-- Overview Section -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Overview</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Production Order No.</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $run->order_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</p>
                    <div class="mt-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $run->product->name ?? '—' }}</p>
                        @if($run->product?->sku)
                            <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $run->product->sku }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Batch</p>
                    <p class="mt-1 text-sm font-mono font-medium text-gray-900 dark:text-white">{{ $run->batch->batch_code ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse</p>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ optional($run->warehouse)->name ?? 'Unassigned' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Quantity</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($run->quantity, 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">cartons</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</p>
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                            <circle cx="3" cy="3" r="3" />
                        </svg>
                        {{ ucfirst($run->status ?? 'confirmed') }}
                    </span>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">QC Status</p>
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $qcColor }}">
                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                            <circle cx="3" cy="3" r="3" />
                        </svg>
                        {{ ucfirst($run->qc_status) }}
                    </span>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">QC Approved By</p>
                    @if($run->approver)
                        <div class="mt-1 flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 dark:bg-brand-500/20">
                                <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                    {{ substr($run->approver->name, 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $run->approver->name }}</p>
                                @if($run->approved_at)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($run->approved_at)->format('d M Y') }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock Confirmed</p>
                    @if($run->stock_confirmed_at && $run->stockConfirmer)
                        <div class="mt-1 flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-success-100 dark:bg-success-500/20">
                                <span class="text-xs font-medium text-success-700 dark:text-success-400">
                                    {{ substr($run->stockConfirmer->name, 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $run->stockConfirmer->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($run->stock_confirmed_at)->format('d M Y') }}</p>
                            </div>
                        </div>
                    @else
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Supervisor</p>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ optional($run->supervisor)->name ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Line, Shift & Notes Section -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Line, Shift & Notes</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Production Line</p>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $run->line ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Shift</p>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $run->shift ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Raw Materials Reserved</p>
                    <div class="mt-2 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-300">
                        {{ $run->materials_reserved ?? '—' }}
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</p>
                    <div class="mt-2 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-800/50 dark:text-gray-300">
                        {{ $run->notes ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOM & Material Cost Section -->
    @if($bom && $bom->items->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                        <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bill of Materials & Estimated Material Cost</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Components from active BOM – calculated both per 1 unit and for
                            {{ number_format($run->quantity, 0) }} units. Material cost uses the
                            standard cost set on each raw material.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if(! is_null($estimatedUnitCost))
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Per Unit Cost</p>
                                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">BDT {{ number_format($estimatedUnitCost, 2) }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Sum of component standard costs for 1 {{ $run->product->uom ?? 'unit' }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Run Cost</p>
                                    <p class="mt-1 text-lg font-semibold text-brand-600 dark:text-brand-400">BDT {{ number_format($estimatedTotalCost, 2) }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Approximate cost for {{ number_format($run->quantity, 0) }} units
                                    </p>
                                </div>
                                <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                                    <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Component</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material Type</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Per 1 Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Total for Run</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Cost / Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($bom->items as $item)
                                @php
                                    $component = $item->component;
                                    $perUnit = $item->quantity;
                                    $totalForRun = $perUnit * $run->quantity;
                                    $unitCost = $item->calculated_unit_cost ?? 0;
                                    $totalCost = $item->calculated_total_cost ?? ($unitCost * $run->quantity);
                                    
                                    $typeColors = [
                                        'raw' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                        'service' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                                        'inhouse' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                    ];
                                    $typeColor = $typeColors[$component->product_type ?? 'raw'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $component->name ?? '—' }}
                                            </p>
                                            @if($component->sku)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    SKU: {{ $component->sku }}
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeColor }}">
                                            {{ ucfirst($component->product_type ?? 'raw') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $perUnit }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($totalForRun, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $item->unit ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-mono text-gray-700 dark:text-gray-300">
                                            BDT {{ number_format($unitCost, 4) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            BDT {{ number_format($totalCost, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total Material Cost
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-brand-600 dark:text-brand-400">
                                    BDT {{ number_format($estimatedTotalCost ?? 0, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($run->materialIssues->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Material Issue Log</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Trace of raw material consumption captured during stock confirmation.</p>
            </div>
            <div class="p-6 space-y-5">
                @foreach($run->materialIssues as $issue)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Issued at {{ optional($issue->issued_at)->format('d M Y H:i') }}
                                @if($issue->issuer)
                                    by {{ $issue->issuer->name }}
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $issue->items->count() }} lines</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead>
                                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        <th class="px-4 py-2">Component</th>
                                        <th class="px-4 py-2">Batch</th>
                                        <th class="px-4 py-2 text-right">Qty</th>
                                        <th class="px-4 py-2 text-right">Unit Cost</th>
                                        <th class="px-4 py-2 text-right">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($issue->items as $item)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $item->component->name ?? '—' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $item->batch->batch_code ?? '—' }}</td>
                                            <td class="px-4 py-2 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->quantity, 4) }}</td>
                                            <td class="px-4 py-2 text-right text-sm text-gray-700 dark:text-gray-300">{{ $item->unit_cost !== null ? number_format($item->unit_cost, 4) : '—' }}</td>
                                            <td class="px-4 py-2 text-right text-sm font-medium text-gray-900 dark:text-white">{{ $item->line_total !== null ? number_format($item->line_total, 2) : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('styles')
<style media="print">
    @page {
        size: A4;
        margin: 1.5cm;
    }
    body {
        background: white;
        color: black;
    }
    .no-print, .sidebar, .header-alert, .header-user, footer, 
    button, .flex.items-center.gap-3 a:not(.print\\:block) {
        display: none !important;
    }
    .print-only {
        display: block !important;
    }
    .rounded-2xl, .rounded-xl, .rounded-lg {
        border: 1px solid #e5e7eb !important;
        box-shadow: none !important;
    }
    .bg-white, .bg-gray-50, .bg-gray-100 {
        background: white !important;
    }
    .dark\:bg-gray-900, .dark\:bg-gray-800 {
        background: white !important;
    }
    .text-gray-900, .text-gray-700, .text-gray-600 {
        color: black !important;
    }
    .border-gray-200, .border-gray-300 {
        border-color: #e5e7eb !important;
    }
</style>
@endpush
@endsection
