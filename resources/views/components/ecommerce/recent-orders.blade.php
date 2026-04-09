@props([
    'orders' => [],
    'currencyCode' => 'BDT',
])

@php
    $getStatusClasses = function($status) {
        $base = 'rounded-full px-2 py-0.5 text-theme-xs font-medium';
        return match($status) {
            'delivered' => $base . ' bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'packed', 'picked', 'dispatched' => $base . ' bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400',
            'exception', 'cancelled', 'canceled' => $base . ' bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            'confirmed' => $base . ' bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400',
            default => $base . ' bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        };
    };
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Client Orders</h3>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Latest 10
            </span>

            <a href="{{ route('admin.orders.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                See all
            </a>
        </div>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="min-w-full">
            <thead>
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    <th class="py-3 text-left">
                        <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Order</p>
                    </th>
                    <th class="py-3 text-left">
                        <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Client</p>
                    </th>
                    <th class="py-3 text-left">
                        <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Delivery date</p>
                    </th>
                    <th class="py-3 text-left">
                        <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Total</p>
                    </th>
                    <th class="py-3 text-left">
                        <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="whitespace-nowrap py-3">
                            <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                #{{ $order->id }}
                            </p>
                            @php
                                $type = $order->order_type ?? 'regular';
                                $isReturn = $type === 'return';
                            @endphp
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-theme-xs font-medium {{ $isReturn ? 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300' }}">
                                @if($isReturn)
                                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-error-500"></span>
                                @else
                                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                @endif
                                {{ ucfirst($type) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap py-3">
                            <p class="text-theme-sm text-gray-800 dark:text-white/90">
                                {{ $order->agent->name ?? '—' }}
                            </p>
                            @if($order->agent?->area || $order->agent?->zone)
                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">
                                    {{ $order->agent->area }}{{ $order->agent->area && $order->agent->zone ? ', ' : '' }}{{ $order->agent->zone }}
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap py-3">
                            <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                                {{ optional($order->delivery_date)->format('d M Y') ?? '—' }}
                            </p>
                        </td>
                        <td class="whitespace-nowrap py-3">
                            <p class="text-theme-sm {{ isset($isReturn) && $isReturn ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90' }}">
                                {{ $currencyCode }} {{ number_format($order->total ?? 0, 2) }}
                            </p>
                        </td>
                        <td class="whitespace-nowrap py-3">
                            <span class="{{ $getStatusClasses($order->status ?? '') }}">
                                {{ ucfirst(str_replace('_', ' ', $order->status ?? '')) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                            No recent client orders yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
