@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
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
                        New Order
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Capture SKU, quantity, and delivery info for the agent
                    </p>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.orders.index') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Orders
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-8 py-6 dark:border-gray-800 dark:from-gray-900/50 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Order Details</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Enter agent information and order items</p>
                </div>
            </div>
            
            <!-- Progress Steps -->
            <div class="mt-6 flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-900/50 dark:text-brand-400">1</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Agent & Order</span>
                </div>
                <div class="h-0.5 w-8 bg-gray-200 dark:bg-gray-700"></div>
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">2</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Order Items</span>
                </div>
                <div class="h-0.5 w-8 bg-gray-200 dark:bg-gray-700"></div>
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">3</span>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Review</span>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form action="{{ route('admin.orders.store') }}" method="POST">
                @csrf
                
                <!-- Agent & Order Section -->
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Agent & Order Information</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pl-11">
                        <!-- Agent -->
                        <div class="space-y-2">
                            <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Agent <span class="text-error-500">*</span>
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <select id="agent_id" 
                                        name="agent_id" 
                                        required
                                        class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                    <option value="">Select agent</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}"{{ old('agent_id') == $agent->id ? ' selected' : '' }}>
                                            {{ $agent->name }} · {{ $agent->zone ?? '—' }} · {{ $agent->area ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('agent_id')
                                <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Agent PO / Reference -->
                        <div class="space-y-2">
                            <label for="agent_reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Agent PO / Reference
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 20l4-16 4 4 4-4 4 16H7z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="agent_reference" 
                                       name="agent_reference" 
                                       value="{{ old('agent_reference') }}"
                                       placeholder="e.g. PO-2025-001"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                            </div>
                            @error('agent_reference')
                                <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Order Type -->
                        <div class="space-y-2">
                            <label for="order_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Order Type
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <select id="order_type" 
                                        name="order_type"
                                        class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                    @foreach(['regular','bulk','sample','return'] as $type)
                                        <option value="{{ $type }}"{{ old('order_type', 'regular') == $type ? ' selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                "Sample" orders are non-billable free issues. "Return" orders are pickup authorizations and do not reserve stock or generate invoices. "Bulk" orders default to credit if no payment mode is chosen.
                            </p>
                        </div>

                        <!-- Delivery Date -->
                        <div class="space-y-2">
                            <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Date
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
                                       value="{{ old('delivery_date', now()->addDays(7)->format('Y-m-d')) }}"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
                            </div>
                            @error('delivery_date')
                                <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Mode -->
                        <div class="space-y-2">
                            <label for="payment_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Payment Mode
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                                    </svg>
                                </div>
                                <select id="payment_mode" 
                                        name="payment_mode"
                                        class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                    <option value="">Select payment mode</option>
                                    @foreach(['cash' => 'Cash', 'credit' => 'Credit', 'bkash' => 'bKash', 'bank_transfer' => 'Bank Transfer'] as $value => $label)
                                        <option value="{{ $value }}"{{ old('payment_mode') === $value ? ' selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Contact Name -->
                        <div class="space-y-2">
                            <label for="delivery_contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Contact Name
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="delivery_contact_name" 
                                       name="delivery_contact_name" 
                                       value="{{ old('delivery_contact_name') }}"
                                       placeholder="e.g. Md. Rahim Uddin"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                            </div>
                        </div>

                        <!-- Delivery Contact Phone -->
                        <div class="space-y-2">
                            <label for="delivery_contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Contact Phone
                            </label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="delivery_contact_phone" 
                                       name="delivery_contact_phone" 
                                       value="{{ old('delivery_contact_phone') }}"
                                       placeholder="e.g. +880 1XXX-XXXXXX"
                                       class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                            </div>
                        </div>

                        <!-- Delivery Address (Full Width) -->
                        <div class="lg:col-span-3 space-y-2">
                            <label for="delivery_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Delivery Address
                            </label>
                            <div class="relative group">
                                <div class="absolute left-3 top-3 flex items-start pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </div>
                                <textarea id="delivery_address" 
                                          name="delivery_address" 
                                          rows="3"
                                          placeholder="Street address, city, postal code, country"
                                          class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('delivery_address') }}</textarea>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Leave blank to use agent's default address</p>
                        </div>

                        <!-- Notes (Full Width) -->
                        <div class="lg:col-span-3 space-y-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <div class="relative group">
                                <div class="absolute left-3 top-3 flex items-start pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                    </svg>
                                </div>
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Add any special instructions, delivery notes, or additional information..."
                                          class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items Section -->
                <div class="mt-12 space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Add SKUs, quantity, and negotiated price (from price list or default)</p>
                        </div>
                    </div>

                    <!-- Order Items Container -->
                    <div id="order-items" class="space-y-4 pl-11">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800">
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Product</label>
                                <select name="items[0][product_id]" 
                                        data-product-select 
                                        required
                                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                                    <option value="">Select SKU</option>
                                    @foreach($products as $product)
                                        @php
                                            $available = $availability[$product->id] ?? 0;
                                        @endphp
                                        <option value="{{ $product->id }}" data-stock="{{ $available }}">
                                            {{ $product->sku }} — {{ $product->name }}
                                            ({{ number_format($available, 0) }} avail.)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                <input type="number" 
                                       name="items[0][quantity]" 
                                       value="1" 
                                       min="1" 
                                       required
                                       class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Unit Price (BDT)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-gray-500">BDT</span>
                                    <input type="number" 
                                           name="items[0][unit_price]" 
                                           step="0.01" 
                                           min="0" 
                                           data-unit-price 
                                           required
                                           placeholder="0.00"
                                           class="w-full rounded-lg border border-gray-200 bg-white pl-12 pr-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Item Button -->
                    <div class="pl-11">
                        <button type="button" 
                                id="add-item"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800 transition-all duration-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Another Item
                        </button>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.agentPricing = @json($priceLists);
        window.productBasePrices = @json($products->pluck('base_price', 'id'));

        const itemsWrapper = document.getElementById('order-items');
        const addBtn = document.getElementById('add-item');
        const agentSelect = document.getElementById('agent_id');
        let index = {{ isset($order) && $order->items ? $order->items->count() : 1 }};

        function refreshPrices() {
            const agentId = agentSelect?.value || null;
            const agentPrices = (window.agentPricing && agentId && window.agentPricing[agentId]) || {};

            const productSelects = itemsWrapper.querySelectorAll('[data-product-select]');
            productSelects.forEach((select) => {
                const row = select.closest('.grid');
                const priceInput = row.querySelector('[data-unit-price]');
                if (!priceInput) return;

                const productId = select.value;
                if (!productId) return;

                let price = null;
                if (agentPrices && Object.prototype.hasOwnProperty.call(agentPrices, productId)) {
                    price = agentPrices[productId];
                } else if (window.productBasePrices && Object.prototype.hasOwnProperty.call(window.productBasePrices, productId)) {
                    price = window.productBasePrices[productId];
                }

                if (price !== null && price !== undefined && price !== '') {
                    priceInput.value = price;
                }
            });
        }

        addBtn?.addEventListener('click', () => {
            const template = document.createElement('div');
            template.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/30 border border-gray-100 dark:border-gray-800';
            template.innerHTML = `
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Product</label>
                    <select name="items[${index}][product_id]" data-product-select required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                        <option value="">Select SKU</option>
                        @foreach($products as $product)
                            @php
                                $available = $availability[$product->id] ?? 0;
                            @endphp
                            <option value="{{ $product->id }}" data-stock="{{ $available }}">
                                {{ $product->sku }} — {{ $product->name }}
                                ({{ number_format($available, 0) }} avail.)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                    <input type="number" name="items[${index}][quantity]" value="1" min="1" required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                </div>
                <div class="space-y-2 relative">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Unit Price (BDT)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-gray-500">BDT</span>
                        <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" data-unit-price required placeholder="0.00" class="w-full rounded-lg border border-gray-200 bg-white pl-12 pr-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white transition-all">
                    </div>
                </div>
            `;
            itemsWrapper.appendChild(template);
            
            const newSelect = template.querySelector('[data-product-select]');
            newSelect.addEventListener('change', refreshPrices);
            index += 1;
        });

        agentSelect?.addEventListener('change', refreshPrices);

        const initialSelects = itemsWrapper.querySelectorAll('[data-product-select]');
        initialSelects.forEach((select) => {
            select.addEventListener('change', refreshPrices);
        });
        
        // Trigger initial price load
        refreshPrices();
    });
</script>
@endpush
@endsection
