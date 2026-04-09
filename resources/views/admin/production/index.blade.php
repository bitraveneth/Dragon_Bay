@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Production Orders & Runs
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Manufacturing
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Create production orders for batches, record actual runs, QC, and stock posting.
            </p>
        </div>
        <a href="{{ route('admin.production.create') }}" 
           data-tour="production-primary-action"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Production Order / Run
        </a>
    </div>

    <!-- Today's Production Metrics -->
    @if(isset($byLineShift) && $byLineShift->isNotEmpty())
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Today's Production</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $today }}</span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($byLineShift as $key => $qty)
                    @php
                        [$line, $shift] = explode('|', $key);
                        $normalizedKey = strtolower(trim($key));
                        $capacity = $lineCapacities[$normalizedKey] ?? null;
                        $util = $capacity ? ($qty / $capacity) * 100 : null;
                        
                        $utilColor = 'bg-gray-200 dark:bg-gray-700';
                        if ($util !== null) {
                            if ($util >= 100) $utilColor = 'bg-error-500';
                            elseif ($util >= 85) $utilColor = 'bg-success-500';
                            elseif ($util >= 70) $utilColor = 'bg-brand-500';
                            else $utilColor = 'bg-orange-500';
                        }
                    @endphp
                    <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ $line }} · {{ $shift }}
                                </p>
                                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($qty) }}
                                </p>
                                <div class="mt-3 flex items-center gap-2">
                                    @if($util)
                                        <div class="w-20 h-1.5 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700">
                                            <div class="h-full rounded-full {{ $utilColor }}" 
                                                 style="width: {{ min($util, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs {{ $util >= 100 ? 'text-error-600 dark:text-error-500' : 'text-gray-600 dark:text-gray-400' }}">
                                            {{ number_format($util, 0) }}% capacity
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Capacity not set</span>
                                    @endif
                                </div>
                            </div>
                            <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                                <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Status Message -->

    <!-- Production Runs Table -->
    @if($runs->isNotEmpty())
        @php
            $totalRuns = $runs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $runs->total() : $runs->count();
            $pendingQC = $runs->where('qc_status', 'pending')->count();
            $approved = $runs->where('qc_status', 'approved')->count();
            $stockConfirmed = $runs->whereNotNull('stock_confirmed_at')->count();
        @endphp

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Runs</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalRuns }}</p>
                    </div>
                    <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                        <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending QC</p>
                        <p class="mt-2 text-2xl font-semibold text-orange-600 dark:text-orange-400">{{ $pendingQC }}</p>
                    </div>
                    <div class="rounded-lg bg-orange-50 p-2.5 dark:bg-orange-500/10">
                        <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Approved</p>
                        <p class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-400">{{ $approved }}</p>
                    </div>
                    <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                        <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock Confirmed</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stockConfirmed }}</p>
                    </div>
                    <div class="rounded-lg bg-blue-light-50 p-2.5 dark:bg-blue-light-500/10">
                        <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Runs Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Production Runs</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $totalRuns }} total runs
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Line / Shift</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Order No.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">QC</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Approved By</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Stock</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($runs as $run)
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
                                
                                $canConfirmStock = $run->qc_status === 'approved'
                                    && auth()->user()?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer']);
                                $isStockConfirmed = $run->stock_confirmed_at !== null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $run->product->name ?? '—' }}
                                        </p>
                                        @if($run->product?->sku)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                SKU: {{ $run->product->sku }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $run->batch->batch_code ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $run->line ?? '—' }} / {{ $run->shift ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($run->quantity) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-gray-600 dark:text-gray-400">
                                        {{ $run->order_number ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        {{ ucfirst($run->status ?? 'confirmed') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $qcColor }}">
                                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        {{ ucfirst($run->qc_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($run->qc_status === 'approved' && $run->approver)
                                        <div>
                                            <p class="text-xs font-medium text-gray-900 dark:text-white">
                                                {{ $run->approver->name }}
                                            </p>
                                            @if($run->approved_at)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ \Carbon\Carbon::parse($run->approved_at)->format('d M Y') }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($isStockConfirmed)
                                        <div>
                                            <p class="text-xs font-medium text-gray-900 dark:text-white">
                                                {{ optional($run->stockConfirmer)->name ?? '—' }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($run->stock_confirmed_at)->format('d M Y') }}
                                            </p>
                                        </div>
                                    @elseif($canConfirmStock)
                                        <form action="{{ route('admin.production.confirm-stock', $run) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Confirm stock to warehouse for this run?');"
                                              class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Confirm Stock
                                            </button>
                                        </form>
                                    @elseif($run->qc_status === 'approved')
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Waiting for warehouse
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Waiting for QC
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.production.show', $run) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('admin.production.edit', $run) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.production.destroy', $run) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete production run #{{ $run->order_number ?? $run->id }}? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($runs, 'links'))
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $runs->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M8 7h8M8 11h6M8 15h4"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No production runs</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                No production runs have been recorded yet. Create your first production order to start manufacturing.
            </p>
            <a href="{{ route('admin.production.create') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Production Order
            </a>
        </div>
    @endif
</div>
@endsection
