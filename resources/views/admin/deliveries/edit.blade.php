@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Edit Delivery
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    #{{ $delivery->order_id }}
                </span>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Adjust route, vehicle, status or POD for this delivery.
                </p>
                @if($delivery->created_at)
                    <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Scheduled {{ $delivery->created_at->diffForHumans() }}
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.deliveries.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Deliveries
            </a>
        </div>
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
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delivery #{{ $delivery->id }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Order #{{ $delivery->order_id }} · {{ $delivery->order->agent->name ?? 'No agent' }}
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.deliveries.update', $delivery) }}" method="POST" class="p-6" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Order (Read-only display) -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Order
                    </label>
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800/50">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            #{{ $delivery->order_id }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            · {{ $delivery->order->agent->name ?? '—' }}
                        </span>
                        @if($delivery->order->total)
                            <span class="ml-auto text-xs font-medium text-gray-700 dark:text-gray-300">
                                BDT {{ number_format($delivery->order->total, 2) }}
                            </span>
                        @endif
                    </div>
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
                                <option value="{{ $route->id }}" {{ old('route_id', $delivery->route_id) == $route->id ? 'selected' : '' }}>
                                    {{ $route->name }} @if($route->zone)({{ $route->zone }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $delivery->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} · {{ $vehicle->license_plate ?? 'No plate' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                                <option value="{{ $status }}" {{ old('status', $delivery->status) == $status ? 'selected' : '' }}>
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
                                  placeholder="Enter any delivery issues, delays, or special instructions...">{{ old('exception_notes', $delivery->exception_notes) }}</textarea>
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
                    
                    @if($delivery->pod_photo)
                        <div class="mt-3 flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                                <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Current POD file</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 break-all">{{ $delivery->pod_photo }}</p>
                            </div>
                            <a href="{{ Storage::url($delivery->pod_photo) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </a>
                        </div>
                    @endif
                    
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Upload new photo to replace existing POD
                    </p>
                    @error('pod_photo')
                        <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @php
                $existingItems = $delivery->items->keyBy('order_item_id');
                $pod = $delivery->pod;
            @endphp

            <!-- POD Metadata -->
            <div class="mt-8 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">POD Details</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Capture delivery receiver and location details.</p>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Signed By</label>
                        <input type="text" name="pod_signed_by" value="{{ old('pod_signed_by', $pod?->signed_by) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Receiver Name</label>
                        <input type="text" name="pod_receiver_name" value="{{ old('pod_receiver_name', $pod?->receiver_name) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Receiver Phone</label>
                        <input type="text" name="pod_receiver_phone" value="{{ old('pod_receiver_phone', $pod?->receiver_phone) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Delivered At</label>
                        <input type="datetime-local" name="pod_delivered_at"
                               value="{{ old('pod_delivered_at', optional($pod?->delivered_at)->format('Y-m-d\\TH:i')) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
                        <input type="number" step="0.0000001" name="pod_latitude" value="{{ old('pod_latitude', $pod?->latitude) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
                        <input type="number" step="0.0000001" name="pod_longitude" value="{{ old('pod_longitude', $pod?->longitude) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">POD Notes</label>
                        <textarea name="pod_notes" rows="2"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('pod_notes', $pod?->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Delivery Item Quantities -->
            <div class="mt-6 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Delivered Item Quantities</h4>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used for POD accuracy, exception tracking, and stock movement.</p>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="px-2 py-2">Item</th>
                                <th class="px-2 py-2">Dispatch</th>
                                <th class="px-2 py-2">Delivered</th>
                                <th class="px-2 py-2">Short</th>
                                <th class="px-2 py-2">Damaged</th>
                                <th class="px-2 py-2">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($delivery->order->items as $idx => $orderItem)
                                @php
                                    $row = $existingItems->get($orderItem->id);
                                    $defaultQty = (float) $orderItem->quantity;
                                @endphp
                                <tr>
                                    <td class="px-2 py-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $orderItem->product->name ?? 'Item' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Qty ordered: {{ $orderItem->quantity }}</div>
                                        <input type="hidden" name="items[{{ $idx }}][order_item_id]" value="{{ $orderItem->id }}">
                                        <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $orderItem->product_id }}">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" min="0" step="0.01" name="items[{{ $idx }}][qty_dispatched]"
                                               value="{{ old(\"items.$idx.qty_dispatched\", $row?->qty_dispatched ?? $defaultQty) }}"
                                               class="w-24 rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" min="0" step="0.01" name="items[{{ $idx }}][qty_delivered]"
                                               value="{{ old(\"items.$idx.qty_delivered\", $row?->qty_delivered ?? $defaultQty) }}"
                                               class="w-24 rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" min="0" step="0.01" name="items[{{ $idx }}][qty_short]"
                                               value="{{ old(\"items.$idx.qty_short\", $row?->qty_short ?? 0) }}"
                                               class="w-24 rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" min="0" step="0.01" name="items[{{ $idx }}][qty_damaged]"
                                               value="{{ old(\"items.$idx.qty_damaged\", $row?->qty_damaged ?? 0) }}"
                                               class="w-24 rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="text" name="items[{{ $idx }}][notes]"
                                               value="{{ old(\"items.$idx.notes\", $row?->notes) }}"
                                               class="w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                               placeholder="Optional note">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone - Delete Section -->
    @if(Route::has('admin.deliveries.destroy'))
    <div class="rounded-2xl border border-error-200 bg-white shadow-theme-sm dark:border-error-800/30 dark:bg-gray-900">
        <div class="border-b border-error-100 px-6 py-4 dark:border-error-800/20">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-medium text-error-700 dark:text-error-400">Danger Zone</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this delivery</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Once deleted, this delivery record and any associated POD cannot be recovered.
                        This action cannot be undone.
                    </p>
                </div>
                <form action="{{ route('admin.deliveries.destroy', $delivery) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete delivery #{{ $delivery->id }} for order #{{ $delivery->order_id }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Delivery
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
