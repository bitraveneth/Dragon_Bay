@extends('layouts.app')

@section('content')
@php
    $isReturnOrder = ($order->order_type ?? null) === 'return';
    $currencyCode = config('app.currency', 'BDT');
@endphp
<div class="screen-order-view max-w-7xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
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
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            {{ $isReturnOrder ? 'Return Order' : 'Order' }} #{{ $order->id }}
                        </h1>
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                'confirmed' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                'picked' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                'packed' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                'dispatched' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                                'delivered' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            ];
                            $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                        @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium {{ $statusColor }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $order->status === 'delivered' || $order->status === 'confirmed' ? 'bg-success-500' : ($order->status === 'picked' ? 'bg-brand-500' : ($order->status === 'packed' ? 'bg-blue-light-500' : ($order->status === 'dispatched' ? 'bg-purple-500' : 'bg-gray-500'))) }}"></span>
                            {{ ucfirst($order->status) }}
                        </span>
                        @if($isReturnOrder)
                            <span class="inline-flex items-center rounded-full bg-error-100 px-3 py-1.5 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                Customer return
                            </span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>{{ $order->client?->name ?? $order->agent?->name ?? '—' }} · {{ $order->client?->company_name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16 4 4 4-4 4 16H7z" />
                            </svg>
                            <span class="capitalize">{{ $isReturnOrder ? 'Return' : $order->order_type }}</span>
                        </div>
                        @if($order->agent_reference)
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16 4 4 4-4 4 16H7z" />
                            </svg>
                            <span>PO: {{ $order->agent_reference }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Orders
            </a>
            @if($isReturnOrder)
                <a href="{{ route('admin.returns.customer.create') }}?order_id={{ $order->id }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-error-500 to-error-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:from-error-600 hover:to-error-700 focus:outline-none focus:ring-2 focus:ring-error-500/50 transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Record stock return
                </a>
            @endif
            <button type="button" 
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                </svg>
                Print
            </button>
            @unless($isReturnOrder)
                <a href="{{ route('admin.orders.picking-list', $order) }}" 
                   class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75z" />
                    </svg>
                    Picking List
                </a>
            @endunless
        </div>
    </div>

    @if($isReturnOrder)
        <div class="rounded-2xl border border-error-200 bg-error-50 px-5 py-4 text-sm text-error-800 dark:border-error-900/40 dark:bg-error-950/20 dark:text-error-200">
            This is a commercial return order. It reduces sales value, but it does not automatically add stock back. Use
            <a href="{{ route('admin.returns.customer.create') }}?order_id={{ $order->id }}" class="font-semibold underline underline-offset-2 hover:no-underline">
                Record stock return
            </a>
            after the returned goods are physically received into inventory.
        </div>
    @endif

    <!-- Order Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Delivery Info Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/30">
                    <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $isReturnOrder ? 'Return Date' : 'Delivery Date' }}</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ optional($order->delivery_date)->format('d M Y') ?? 'TBD' }}
                    </p>
                    @if($order->delivery_date)
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->delivery_date->diffForHumans() }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payment Info Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-success-100 dark:bg-success-900/30">
                    <svg class="h-5 w-5 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $order->payment_mode ? ucfirst(str_replace('_', ' ', $order->payment_mode)) : '—' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total: {{ $currencyCode }} {{ number_format($order->total, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Commission Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-light-100 dark:bg-blue-light-900/30">
                    <svg class="h-5 w-5 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Commission</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $currencyCode }} {{ number_format($order->commission_total ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Agent commission</p>
                </div>
            </div>
        </div>

        <!-- Order Actions Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30">
                    <svg class="h-5 w-5 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Order Status</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        @if($order->status === 'delivered')
                            <span class="text-xs text-success-600 dark:text-success-400">Completed</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Contact & Address Card (if exists) -->
    @if(!$isReturnOrder && ($order->delivery_contact_name || $order->delivery_contact_phone || $order->delivery_address))
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                <svg class="h-5 w-5 text-gray-700 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Delivery Information</h3>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($order->delivery_contact_name || $order->delivery_contact_phone)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Contact</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $order->delivery_contact_name ?? '—' }}
                            @if($order->delivery_contact_phone)
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ $order->delivery_contact_phone }}</span>
                            @endif
                        </p>
                    </div>
                    @endif
                    @if($order->delivery_address)
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Address</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $order->delivery_address }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Status Update Form -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/30">
                    <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Update Order Status</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Change the current status of this order</p>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('admin.orders.status.update', $order) }}" method="POST" class="flex flex-wrap items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="relative">
                        <select name="status" 
                                class="rounded-xl border border-gray-200 bg-white/50 px-4 py-2.5 pr-10 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                            @php
                                $statusOptions = ['draft','confirmed','picked','packed','dispatched','delivered'];
                            @endphp
                            <?php foreach ($statusOptions as $status): ?>
                                <?php
                                    $disabled = false;
                                    $currentIndex = array_search($order->status, $statusOptions, true);
                                    $targetIndex = array_search($status, $statusOptions, true);
                                    if ($targetIndex > $currentIndex + 1) {
                                        $disabled = true;
                                    }
                                    if ($order->status === 'delivered' && $status !== 'delivered') {
                                        $disabled = true;
                                    }
                                ?>
                                <option value="{{ $status }}"
                                        {{ $order->status === $status ? ' selected' : '' }}
                                        {{ $disabled ? 'disabled' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Status
                    </button>
                </form>

                @php($canConfirmPacking = auth()->user()?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer']))
                @if($canConfirmPacking && $order->status === 'picked')
                    <form action="{{ route('admin.orders.status.update', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="packed">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-5 py-2.5 text-sm font-medium text-brand-700 shadow-sm hover:bg-brand-100 dark:border-brand-800 dark:bg-brand-900/30 dark:text-brand-400 dark:hover:bg-brand-900/50 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Confirm Packing
                        </button>
                    </form>
                @endif

                @if($order->status === 'delivered')
                    <form action="{{ route('admin.orders.invoice', $order) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-2 rounded-xl border border-success-200 bg-success-50 px-5 py-2.5 text-sm font-medium text-success-700 shadow-sm hover:bg-success-100 dark:border-success-800 dark:bg-success-900/30 dark:text-success-400 dark:hover:bg-success-900/50 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Create Invoice
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Order Items Table -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                        <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    {{ $order->items->count() }} items
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Tax Class</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Line Total</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <?php foreach ($order->items as $item): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $item->product->sku ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->product->name ?? '—' }}</p>
                                    @if($item->product?->size)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->product->size }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($item->product && $item->product->taxClass)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        {{ $item->product->taxClass->name }} ({{ $item->product->taxClass->rate }}%)
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->quantity }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $currencyCode }} {{ number_format($item->unit_price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-bold {{ $isReturnOrder ? 'text-error-600 dark:text-error-400' : 'text-gray-900 dark:text-white' }}">{{ $currencyCode }} {{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div>
                                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">
                                        {{ $currencyCode }} {{ number_format($item->commission_amount ?? 0, 2) }}
                                    </span>
                                    @if($item->commission_rate)
                                        <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                            ({{ number_format($item->commission_rate, 2) }}%)
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                            Subtotal
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">
                            {{ $currencyCode }} {{ number_format($order->items->sum(function ($item) { return $item->quantity * $item->unit_price; }), 2) }}
                        </td>
                        <td></td>
                    </tr>
                    @if(($order->tax_total ?? 0) > 0)
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tax
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">
                            {{ $currencyCode }} {{ number_format($order->tax_total, 2) }}
                        </td>
                        <td></td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">
                            Total
                        </td>
                        <td class="px-6 py-4 text-right text-lg font-bold {{ $isReturnOrder ? 'text-error-600 dark:text-error-400' : 'text-brand-600 dark:text-brand-400' }}">
                            {{ $currencyCode }} {{ number_format($order->total, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Status History -->
    @if($order->statusHistory->isNotEmpty())
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                    <svg class="h-4 w-4 text-gray-700 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status History</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <?php
                    $sortedStatusHistory = $order->statusHistory->sortByDesc('changed_at')->values();
                    $statusHistoryCount = $sortedStatusHistory->count();
                ?>
                <ul role="list" class="-mb-8">
                    <?php foreach ($sortedStatusHistory as $index => $entry): ?>
                        <?php
                            $statusColors = [
                                'draft' => 'bg-gray-500',
                                'confirmed' => 'bg-success-500',
                                'picked' => 'bg-brand-500',
                                'packed' => 'bg-blue-light-500',
                                'dispatched' => 'bg-purple-500',
                                'delivered' => 'bg-success-500',
                            ];
                            $dotColor = $statusColors[$entry->status] ?? 'bg-gray-500';
                            $isLast = $index === ($statusHistoryCount - 1);
                        ?>
                        <li class="relative pb-8">
                            @if(!$isLast)
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $dotColor }} bg-opacity-20 dark:bg-opacity-30">
                                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ ucfirst($entry->status) }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Changed by {{ $entry->user->name ?? 'System' }}
                                        </p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-xs text-gray-500 dark:text-gray-400">
                                        <time datetime="{{ $entry->changed_at->format('Y-m-d') }}">
                                            {{ $entry->changed_at->format('d M Y, H:i') }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Order Notes -->
    @if($order->notes)
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                <svg class="h-5 w-5 text-gray-700 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $isReturnOrder ? 'Return Notes' : 'Order Notes' }}</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $order->notes }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="print-only mt-6 text-[12px] leading-relaxed text-gray-900">
        <div class="flex items-start justify-between mb-6">
        <div class="flex items-center gap-3">
            @if(!empty($appLogoUrl))
                <img src="{{ $appLogoUrl }}" alt="{{ $legalCompanyName }}" class="h-12 w-12 rounded-full object-cover" />
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white">
                    {{ $legalCompanyInitials }}
                </div>
            @endif
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $legalCompanyName }}</h2>
                <p class="mt-1 text-xs text-gray-600">{{ $isReturnOrder ? 'Return Summary' : 'Order &amp; Delivery' }}</p>
            </div>
        </div>
        <div class="text-right space-y-1">
            <h1 class="text-xl font-bold text-gray-900">{{ $isReturnOrder ? 'Return Order' : 'Order' }} #{{ $order->id }}</h1>
            <p class="text-xs text-gray-600">Customer: {{ $order->client?->name ?? $order->agent?->name ?? '—' }}</p>
            <p class="text-xs text-gray-600">Issued: {{ $order->created_at?->format('d M Y') ?? now()->format('d M Y') }}</p>
            @if($order->delivery_date)
                <p class="text-xs text-gray-600">{{ $isReturnOrder ? 'Return' : 'Delivery' }}: {{ $order->delivery_date->format('d M Y') }}</p>
            @endif
            <p class="text-xs text-gray-600">Status: {{ ucfirst($order->status) }}</p>
        </div>
    </div>

    <div class="flex justify-between mb-4">
        <div>
            <h3 class="font-semibold text-sm">Bill to</h3>
            <p class="mt-1">
                {{ $order->client?->name ?? $order->agent?->name ?? '—' }}<br>
                @if($order->client?->company_name)
                    {{ $order->client->company_name }}<br>
                @endif
                @if($order->client?->address)
                    {{ $order->client->address }}<br>
                @endif
                @if($order->agent_reference)
                    Ref: {{ $order->agent_reference }}
                @endif
            </p>

            @if($order->delivery_address)
                <div class="mt-6">
                    <h3 class="font-semibold text-sm uppercase tracking-wide">{{ $isReturnOrder ? 'Return address' : 'Delivery address' }}</h3>
                    <p class="mt-1">{{ $order->delivery_address }}</p>
                </div>
            @endif

            @if($order->notes)
                <div class="mt-6">
                    <h3 class="font-semibold text-sm uppercase tracking-wide">{{ $isReturnOrder ? 'Return notes' : 'Notes' }}</h3>
                    <p class="mt-1">{{ $order->notes }}</p>
                </div>
            @endif
        </div>
        <div class="text-right">
            <p>{{ $isReturnOrder ? 'Return' : 'Order' }} No: <strong>#{{ $order->id }}</strong></p>
            <p>{{ $isReturnOrder ? 'Return type' : 'Order type' }}: {{ $isReturnOrder ? 'Customer return' : ucfirst($order->order_type) }}</p>
            @if($order->payment_mode)
                <p>Payment: {{ ucfirst(str_replace('_', ' ', $order->payment_mode)) }}</p>
            @endif
            @if($order->delivery_contact_name)
                <p>Contact: <strong>{{ $order->delivery_contact_name }}</strong></p>
            @endif
            @if($order->delivery_contact_phone)
                <p>Phone: {{ $order->delivery_contact_phone }}</p>
            @endif
        </div>
    </div>

    <table class="w-full border-collapse text-[11px]">
        <thead>
            <tr>
                <th class="border border-gray-300 px-2 py-1 text-left">#</th>
                <th class="border border-gray-300 px-2 py-1 text-left">Description</th>
                <th class="border border-gray-300 px-2 py-1 text-right">Qty</th>
                <th class="border border-gray-300 px-2 py-1 text-right">Unit Price</th>
                <th class="border border-gray-300 px-2 py-1 text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="border border-gray-200 px-2 py-1 text-left">{{ $index + 1 }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-left">
                        {{ $item->product->name ?? '—' }}
                        @if($item->product?->size)
                            <div class="text-[10px] text-gray-500">{{ $item->product->size }}</div>
                        @endif
                        @if($item->product?->sku)
                            <div class="text-[10px] text-gray-500">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="border border-gray-200 px-2 py-1 text-right">{{ number_format($item->quantity, 0) }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="border border-gray-200 px-2 py-1 text-right">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4 flex justify-end">
        <table class="text-[11px]">
            <tr>
                <td class="px-3 py-1 text-right">Net total:</td>
                <td class="px-3 py-1 text-right">{{ number_format($order->items->sum(function ($item) { return $item->quantity * $item->unit_price; }), 2) }}</td>
            </tr>
            @if(($order->tax_total ?? 0) > 0)
                <tr>
                    <td class="px-3 py-1 text-right">VAT:</td>
                    <td class="px-3 py-1 text-right">{{ number_format((float) ($order->tax_total ?? 0), 2) }}</td>
                </tr>
            @endif
            @if(($order->commission_total ?? 0) > 0)
                <tr>
                    <td class="px-3 py-1 text-right">Commission:</td>
                    <td class="px-3 py-1 text-right">{{ number_format((float) ($order->commission_total ?? 0), 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="px-3 py-1 text-right font-semibold border-t border-gray-300">Total:</td>
                <td class="px-3 py-1 text-right font-semibold border-t border-gray-300">{{ number_format((float) $order->total, 2) }}</td>
            </tr>
        </table>
    </div>
</div>

@push('styles')
<style media="print">
    @page {
        size: A4;
        margin: 12mm;
    }
</style>

<style>
    @media print {
        body {
            background: #ffffff !important;
        }

        #sidebar,
        header,
        .screen-order-view {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }
    }

    @media screen {
        .print-only {
            display: none !important;
        }
    }
</style>
@endpush
@endsection
