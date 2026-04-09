@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Packing Slips
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Warehouse
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Confirm packing and print slips for scheduled and in-transit deliveries.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.deliveries.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                All Deliveries
            </a>
        </div>
    </div>

    <!-- Status Message -->

    @if($deliveries->isNotEmpty())
        <!-- Summary Stats -->
        @php
            $totalDeliveries = $deliveries instanceof \Illuminate\Pagination\LengthAwarePaginator ? $deliveries->total() : $deliveries->count();
            $pendingPacking = $deliveries->filter(function($delivery) {
                return $delivery->order && $delivery->order->status === 'picked';
            })->count();
            $readyToPrint = $deliveries->filter(function($delivery) {
                return in_array($delivery->status, ['scheduled', 'in_transit']);
            })->count();
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Deliveries</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalDeliveries }}</p>
                    </div>
                    <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                        <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Ready to Print</p>
                        <p class="mt-2 text-2xl font-semibold text-blue-light-600 dark:text-blue-light-400">{{ $readyToPrint }}</p>
                    </div>
                    <div class="rounded-lg bg-blue-light-50 p-2.5 dark:bg-blue-light-500/10">
                        <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending Packing</p>
                        <p class="mt-2 text-2xl font-semibold text-orange-600 dark:text-orange-400">{{ $pendingPacking }}</p>
                    </div>
                    <div class="rounded-lg bg-orange-50 p-2.5 dark:bg-orange-500/10">
                        <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Delivered</p>
                        <p class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-400">
                            {{ $deliveries->where('status', 'delivered')->count() }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                        <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packing Slips Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Packing Slips</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $deliveries instanceof \Illuminate\Pagination\LengthAwarePaginator ? $deliveries->total() : $deliveries->count() }} deliveries
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Seq</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Agent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Route</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Vehicle</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Order Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Delivery Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($deliveries as $delivery)
                            @php
                                $order = $delivery->order;
                                $packingStatus = $order?->status;
                                $canConfirmPacking = $order
                                    && auth()->user()?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer'])
                                    && $order->status === 'picked';
                                
                                $orderStatusColors = [
                                    'pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'picked' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    'packed' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'shipped' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                                    'delivered' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'cancelled' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                ];
                                $orderStatusColor = $orderStatusColors[$order?->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                
                                $deliveryStatusColors = [
                                    'scheduled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'in_transit' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    'delivered' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'exception' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                ];
                                $deliveryStatusColor = $deliveryStatusColors[$delivery->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    @if($delivery->sequence)
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $delivery->sequence }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        #{{ $delivery->order_id }}
                                    </span>
                                    @if($order && $order->total)
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                            BDT {{ number_format($order->total, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                            <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                {{ substr($delivery->order->agent->name ?? '?', 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $delivery->order->agent->name ?? '—' }}
                                            </p>
                                            @if($delivery->order->agent->area)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $delivery->order->agent->area }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($delivery->route)
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $delivery->route->name }}
                                            </p>
                                            @if($delivery->route->zone)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $delivery->route->zone }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($delivery->vehicle)
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $delivery->vehicle->name }}
                                            </p>
                                            @if($delivery->vehicle->license_plate)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $delivery->vehicle->license_plate }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $orderStatusColor }}">
                                        {{ $packingStatus ? ucfirst($packingStatus) : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $deliveryStatusColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.deliveries.packing-slip', $delivery) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            View Slip
                                        </a>
                                        
                                        @if($canConfirmPacking)
                                            <form action="{{ route('admin.orders.status.update', $order) }}" 
                                                  method="POST" 
                                                  class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="packed">
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-success-600 shadow-theme-xs hover:bg-success-50 hover:text-success-700 dark:border-gray-700 dark:bg-gray-800 dark:text-success-500 dark:hover:bg-success-500/10">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Confirm Packing
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if(in_array($delivery->status, ['scheduled', 'in_transit']))
                                            <button onclick="window.print()" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                                </svg>
                                                Print
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($deliveries, 'links'))
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $deliveries->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No packing slips available</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                No deliveries are available for packing at this time. Schedule deliveries or confirm picking to generate packing slips.
            </p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('admin.deliveries.create') }}" 
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Schedule Delivery
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
