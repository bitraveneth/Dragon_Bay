@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h4" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Orders
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Track agent orders and their current statuses
                    </p>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.orders.create') }}" 
           data-tour="orders-primary-action"
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Order
        </a>
    </div>

    <!-- Status Message -->

    <div class="flex flex-wrap items-center gap-3">
        @php
            $typeFilter = $orderTypeFilter ?? 'all';
            $filterTabs = [
                ['value' => 'all', 'label' => 'All orders'],
                ['value' => 'sales', 'label' => 'Sales orders'],
                ['value' => 'return', 'label' => 'Return orders'],
            ];
        @endphp

        @foreach($filterTabs as $tab)
            <a
                href="{{ route('admin.orders.index', ['type' => $tab['value']]) }}"
                class="{{ $typeFilter === $tab['value']
                    ? 'bg-brand-500 text-white border-brand-500 shadow-md'
                    : 'border-gray-200 bg-white text-gray-600 hover:border-brand-200 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-brand-700 dark:hover:text-brand-400' }} inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition-colors"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    @if($orders->isNotEmpty())
        @php
            $totalOrders = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->total() : $orders->count();
            $totalRevenue = $orders->sum('total');
            $totalCommission = $orders->sum('commission_total');
            $pendingOrders = $orders->where('status', 'pending')->count();
            $processingOrders = $orders->where('status', 'processing')->count();
            $deliveredOrders = $orders->where('status', 'delivered')->count();
            $cancelledOrders = $orders->where('status', 'cancelled')->count();
        @endphp

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Orders</span>
                        <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalOrders }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total orders placed</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Revenue</span>
                        <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                            <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ config('app.currency', 'BDT') }} {{ number_format($totalRevenue, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total order value</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Commission</span>
                        <div class="rounded-lg bg-blue-light-100 p-2 dark:bg-blue-light-900/30">
                            <svg class="h-4 w-4 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ config('app.currency', 'BDT') }} {{ number_format($totalCommission, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total agent commission</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending</span>
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $pendingOrders + $processingOrders }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium">{{ $pendingOrders }} pending</span> · {{ $processingOrders }} processing
                    </p>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $typeFilter === 'sales' ? 'Sales Orders' : ($typeFilter === 'return' ? 'Return Orders' : 'All Orders') }}
                    </h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $orders->count() }} of {{ $totalOrders }} orders
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Agent</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Delivery</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Commission</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($orders as $order)
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'pending' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                    'confirmed' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'picked' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'packed' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'dispatched' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    'processing' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'shipped' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    'delivered' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'cancelled' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                ];
                                $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                            <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                                                {{ substr($order->agent->name ?? '?', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-semibold text-gray-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400 transition-colors">
                                                {{ $order->agent->name }}
                                            </a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                #{{ $order->id }} · {{ $order->agent->zone ?? '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize {{ $order->order_type === 'return' ? 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $order->order_type === 'return' ? 'Customer return' : $order->order_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        @if($order->delivery_date)
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $order->delivery_date->format('d M Y') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->delivery_date->diffForHumans() }}</p>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">TBD</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $order->status === 'confirmed' || $order->status === 'delivered' ? 'bg-success-500' : ($order->status === 'cancelled' ? 'bg-error-500' : ($order->status === 'pending' ? 'bg-orange-500' : 'bg-gray-500')) }}"></span>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-bold {{ $order->order_type === 'return' ? 'text-error-600 dark:text-error-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ config('app.currency', 'BDT') }} {{ number_format($order->total, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">
                                        {{ config('app.currency', 'BDT') }} {{ number_format($order->commission_total ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('admin.orders.edit', $order) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.orders.destroy', $order) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete order #{{ $order->id }}? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-sm hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 transition-colors">
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
            
            @if(method_exists($orders, 'links'))
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm p-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 opacity-20 dark:from-gray-900 dark:to-gray-800 blur-3xl"></div>
            
            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-500 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">No Orders Yet</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    No orders have been placed yet. Click the "New Order" button to create your first order.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <a href="{{ route('admin.orders.create') }}" 
                       class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create First Order
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
