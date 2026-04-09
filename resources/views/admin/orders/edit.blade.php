@extends('layouts.app')

@section('content')
@php
    $isReturnOrder = ($order->order_type ?? null) === 'return';
    $currencyCode = config('app.currency', 'BDT');
@endphp
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
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
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            {{ $isReturnOrder ? 'Edit Return Order' : 'Edit Order' }}
                        </h1>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                            #{{ $order->id }}
                        </span>
                        @if($isReturnOrder)
                            <span class="inline-flex items-center rounded-full bg-error-100 px-3 py-1 text-xs font-medium text-error-700 dark:bg-error-500/20 dark:text-error-400">
                                Customer return
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $order->agent->name }} · {{ $isReturnOrder ? 'Return' : ucfirst($order->order_type) }} · {{ $order->agent_reference ?? 'No reference' }}
                    </p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Created {{ $order->created_at?->format('d M Y, H:i') }}</span>
                <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                <span>Last updated {{ $order->updated_at?->diffForHumans() }}</span>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
        @if($isReturnOrder)
            <a href="{{ route('admin.returns.customer.create') }}?order_id={{ $order->id }}"
               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-error-500 to-error-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-error-600 hover:to-error-700 focus:outline-none focus:ring-2 focus:ring-error-500/50 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Record stock return
            </a>
        @endif
        <a href="{{ route('admin.orders.index') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Orders
        </a>
        </div>
    </div>

    <!-- Order Status Banner -->
    <div class="rounded-2xl border border-gray-200 bg-gradient-to-r from-brand-50 to-white p-5 dark:border-gray-800 dark:from-brand-950/30 dark:to-gray-900">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-7.5h9m-9 3h9m-9 3h9M3.75 6h16.5M3.75 12h16.5M3.75 18h16.5" />
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Status:</span>
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                            'pending' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            'confirmed' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'processing' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                            'shipped' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                            'delivered' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'cancelled' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                        ];
                        $statusColor = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium {{ $statusColor }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $order->status === 'confirmed' || $order->status === 'delivered' ? 'bg-success-500' : ($order->status === 'cancelled' ? 'bg-error-500' : 'bg-gray-500') }}"></span>
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 ml-2">Payment:</span>
                    @php
                        $paymentColors = [
                            'pending' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            'paid' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'partial' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                            'refunded' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                        ];
                        $paymentColor = $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium {{ $paymentColor }}">
                        {{ ucfirst($order->payment_status ?? 'pending') }}
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $isReturnOrder
                        ? 'This is a commercial return order. It records the negative sales transaction only. Use "Record stock return" after goods are physically received back into inventory.'
                        : 'Adjust delivery date and internal notes. Line items and agent are managed from the original order.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-8 py-6 dark:border-gray-800 dark:from-gray-900/50 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $isReturnOrder ? 'Return Details' : 'Planning Details' }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $isReturnOrder ? 'Update return date and internal notes' : 'Update delivery schedule and order notes' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PATCH')

                <!-- Planning Section -->
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $isReturnOrder ? 'Return & Notes' : 'Delivery & Notes' }}</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
                        <!-- Agent (Read-only) -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Agent
                            </label>
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                    <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->agent->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $order->agent->zone ?? '—' }} · {{ $order->agent->area ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Date -->
                        <div class="space-y-2">
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $isReturnOrder ? 'Return Date' : 'Delivery Date' }}
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="date" 
                                       id="delivery_date" 
                                       name="delivery_date" 
                                       value="{{ optional($order->delivery_date)->format('Y-m-d') }}"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
                            </div>
                            @error('delivery_date')
                                <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes (Full Width) -->
                        <div class="md:col-span-2 space-y-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $isReturnOrder ? 'Return Notes' : 'Order Notes' }}
                            </label>
                            <div class="relative group">
                                <div class="absolute left-3 top-3 flex items-start pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                </div>
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="4"
                                          placeholder="Add any special instructions, delivery notes, or additional information..."
                                          class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('notes', $order->notes) }}</textarea>
                            </div>
                            @error('notes')
                                <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Order Items Section (Read-only) -->
                <div class="mt-12 space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current items in this order (read-only)</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden pl-11">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">SKU</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Product</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Unit Price</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    @foreach($order->items as $item)
                                        @php 
                                            $lineTotal = $item->quantity * $item->unit_price;
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $item->product->sku ?? '—' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->product->name ?? '—' }}</p>
                                                    @if($item->product?->size)
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->product->size }}</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item->quantity }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $currencyCode }} {{ number_format($item->unit_price, 2) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-bold {{ $isReturnOrder ? 'text-error-600 dark:text-error-400' : 'text-brand-600 dark:text-brand-400' }}">{{ $currencyCode }} {{ number_format($lineTotal, 2) }}</span>
                                    </td>
                                </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                                    @php
                                        $subtotal = $order->items->sum(fn($item) => $item->quantity * $item->unit_price);
                                        $tax = $order->tax_total ?? 0;
                                        $total = $order->total ?? $subtotal + $tax;
                                    @endphp
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Subtotal
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $currencyCode }} {{ number_format($subtotal, 2) }}
                                        </td>
                                    </tr>
                                    @if($tax > 0)
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tax
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $currencyCode }} {{ number_format($tax, 2) }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                                            Total
                                        </td>
                                        <td class="px-4 py-3 text-right text-lg font-bold {{ $isReturnOrder ? 'text-error-600 dark:text-error-400' : 'text-brand-600 dark:text-brand-400' }}">
                                            {{ $currencyCode }} {{ number_format($total, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-12 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.orders.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Timeline / Actions (Optional) -->
    @if(Route::has('admin.orders.cancel') || Route::has('admin.orders.duplicate'))
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Order Actions</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-4">
                @if(Route::has('admin.orders.duplicate'))
                <a href="{{ route('admin.orders.duplicate', $order) }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Duplicate Order
                </a>
                @endif
                
                @if(Route::has('admin.orders.cancel') && $order->status !== 'cancelled' && $order->status !== 'delivered')
                <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to cancel this order?');"
                      class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg border border-error-200 bg-white px-4 py-2.5 text-sm font-medium text-error-600 shadow-sm hover:bg-error-50 dark:border-error-800 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancel Order
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
