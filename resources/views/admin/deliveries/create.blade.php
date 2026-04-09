@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Schedule Delivery
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Last Mile
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Attach a route/vehicle to an order and upload POD after delivery.
            </p>
        </div>
        <a href="{{ route('admin.deliveries.index') }}" 
           class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Deliveries
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delivery Schedule</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Assign order to route and vehicle</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.deliveries.store') }}" method="POST" class="p-6" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Order Selection -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="order_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Order <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <select id="order_id" 
                                name="order_id" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select order</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" {{ old('order_id') == $order->id ? 'selected' : '' }}>
                                    #{{ $order->id }} · {{ $order->agent->name ?? '—' }} · BDT {{ number_format($order->total ?? 0, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('order_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Delivery Route -->
                <div>
                    <label for="route_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Route
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <select id="route_id" 
                                name="route_id"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}" {{ old('route_id') == $route->id ? 'selected' : '' }}>
                                    {{ $route->name }} @if($route->zone)({{ $route->zone }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Assign to predefined route</p>
                    @error('route_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Vehicle -->
                <div>
                    <label for="vehicle_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Vehicle
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4-4m-4 4l4 4"/>
                            </svg>
                        </div>
                        <select id="vehicle_id" 
                                name="vehicle_id"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">Select vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} · {{ $vehicle->license_plate ?? 'No plate' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional: Assign specific vehicle</p>
                    @error('vehicle_id')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Status
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <select id="status" 
                                name="status"
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @foreach(['scheduled', 'in_transit', 'delivered', 'exception'] as $status)
                                <option value="{{ $status }}" {{ old('status', 'scheduled') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Exception Notes (Full Width) -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="exception_notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Exception Notes
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute left-3 top-3 flex items-start">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <textarea id="exception_notes" 
                                  name="exception_notes" 
                                  rows="3"
                                  class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                  placeholder="Enter any delivery issues, delays, or special instructions...">{{ old('exception_notes') }}</textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required if status is "exception"</p>
                    @error('exception_notes')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- POD Photo -->
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="pod_photo" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Proof of Delivery (POD) Photo
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <input type="file" 
                               id="pod_photo" 
                               name="pod_photo" 
                               accept="image/*"
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:file:bg-gray-700 dark:file:text-gray-300">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Upload photo of delivery receipt, signature, or proof (JPEG, PNG, max 5MB)
                    </p>
                    @error('pod_photo')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.deliveries.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Schedule Delivery
                </button>
            </div>
        </form>
    </div>
</div>
@endsection