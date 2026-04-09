@props([
    'agentCount' => 0,
    'totalOrderCount' => 0,
    'returnOrderCount' => 0,
    'currencyCode' => 'BDT',
    'monthlyRevenue' => 0,
    'outstandingReceivables' => 0,
    'pendingDeliveryCount' => 0,
    'todayProductionQty' => 0,
    'lowStockAlertCount' => 0,
])

@php
    $cards = [
        [
            'label' => 'Clients',
            'value' => number_format($agentCount ?? 0),
            'caption' => 'Active client accounts',
            'tone' => 'success',
            'icon' => 'agent',
            'href' => route('admin.agents.index'),
        ],
        [
            'label' => 'Client Orders',
            'value' => number_format($totalOrderCount ?? 0),
            'caption' => 'Active booking and sales orders',
            'tone' => 'brand',
            'icon' => 'orders',
            'href' => route('admin.orders.index'),
        ],
        [
            'label' => 'Returns',
            'value' => number_format($returnOrderCount ?? 0),
            'caption' => 'Client return orders',
            'tone' => 'error',
            'icon' => 'returns',
            'href' => route('admin.orders.index', ['type' => 'return']),
        ],
        [
            'label' => 'Billed Value',
            'value' => $currencyCode . ' ' . number_format((float) ($monthlyRevenue ?? 0), 0),
            'caption' => 'Current month client invoicing',
            'tone' => 'violet',
            'icon' => 'revenue',
            'href' => route('admin.finance.index'),
        ],
        [
            'label' => 'Open Receivables',
            'value' => $currencyCode . ' ' . number_format((float) ($outstandingReceivables ?? 0), 0),
            'caption' => 'Outstanding client invoice balance',
            'tone' => 'warning',
            'icon' => 'receivables',
            'href' => route('admin.finance.index'),
        ],
        [
            'label' => 'Pending Dispatches',
            'value' => number_format($pendingDeliveryCount ?? 0),
            'caption' => 'Confirmed orders waiting on delivery flow',
            'tone' => 'sky',
            'icon' => 'delivery',
            'href' => route('admin.deliveries.index'),
        ],
        [
            'label' => 'Operational Output',
            'value' => number_format((float) ($todayProductionQty ?? 0), 0),
            'caption' => 'Today tracked through internal operations',
            'tone' => 'amber',
            'icon' => 'production',
            'href' => route('admin.inventory.index'),
        ],
        [
            'label' => 'Low Stock Alerts',
            'value' => number_format($lowStockAlertCount ?? 0),
            'caption' => 'Tracked catalog items at or below threshold',
            'tone' => 'rose',
            'icon' => 'alert',
            'href' => route('admin.inventory.index'),
        ],
    ];

    $toneClasses = [
        'success' => [
            'card' => 'border-emerald-100/70 dark:border-emerald-500/20',
            'iconWrap' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
            'value' => 'text-emerald-600 dark:text-emerald-300',
            'pill' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        ],
        'brand' => [
            'card' => 'border-brand-100/70 dark:border-brand-500/20',
            'iconWrap' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300',
            'value' => 'text-brand-600 dark:text-brand-300',
            'pill' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300',
        ],
        'error' => [
            'card' => 'border-error-100/70 dark:border-error-500/20',
            'iconWrap' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-300',
            'value' => 'text-error-600 dark:text-error-300',
            'pill' => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-300',
        ],
        'warning' => [
            'card' => 'border-orange-100/70 dark:border-orange-500/20',
            'iconWrap' => 'bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300',
            'value' => 'text-orange-600 dark:text-orange-300',
            'pill' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
        ],
        'sky' => [
            'card' => 'border-sky-100/70 dark:border-sky-500/20',
            'iconWrap' => 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
            'value' => 'text-sky-600 dark:text-sky-300',
            'pill' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        ],
        'amber' => [
            'card' => 'border-amber-100/70 dark:border-amber-500/20',
            'iconWrap' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
            'value' => 'text-amber-500 dark:text-amber-300',
            'pill' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        ],
        'rose' => [
            'card' => 'border-rose-100/70 dark:border-rose-500/20',
            'iconWrap' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
            'value' => 'text-rose-500 dark:text-rose-300',
            'pill' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        ],
        'violet' => [
            'card' => 'border-violet-100/70 dark:border-violet-500/20',
            'iconWrap' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
            'value' => 'text-violet-500 dark:text-violet-300',
            'pill' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
        ],
    ];
@endphp

<div class="rounded-3xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 md:p-5">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Operations Snapshot</p>
            <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">Today at a Glance</h2>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Core client, finance, dispatch, and stock signals in one place.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($cards as $card)
            @php
                $tone = $toneClasses[$card['tone']] ?? $toneClasses['brand'];
            @endphp

            <a href="{{ $card['href'] }}"
               class="group block rounded-2xl border bg-gradient-to-br from-white to-gray-50/80 p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-theme-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:from-gray-900 dark:to-gray-900 {{ $tone['card'] }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $card['label'] }}</h3>
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $tone['iconWrap'] }}">
                        @if($card['icon'] === 'agent')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.804 5.602a2.197 2.197 0 1 0 0 4.394 2.197 2.197 0 0 0 0-4.394ZM5.107 7.799a3.697 3.697 0 1 1 7.394 0 3.697 3.697 0 0 1-7.394 0Zm-.245 7.522C4.087 16.088 3.703 17.061 3.516 17.861c-.033.142.004.256.09.35.095.103.26.188.47.188h9.349c.209 0 .374-.085.468-.188.086-.094.124-.208.09-.35-.186-.8-.57-1.773-1.345-2.541-.756-.749-1.948-1.366-3.888-1.366-1.94 0-3.132.617-3.888 1.366Z" />
                            </svg>
                        @elseif($card['icon'] === 'orders')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.665 3.756a.75.75 0 0 1 .671 0l6.446 3.223-6.446 3.223a.75.75 0 0 1-.672 0L5.22 6.979l6.445-3.223Zm-7.629 4.436V16.095c0 .284.16.544.414.671l6.542 3.271V11.65a2.6 2.6 0 0 1-.256-.108l-6.7-3.35Zm8.714 12.282 6.543-3.272a.75.75 0 0 0 .413-.671V8.192l-6.7 3.35a2.6 2.6 0 0 1-.256.108v8.824Z" />
                            </svg>
                        @elseif($card['icon'] === 'returns')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.53 4.47a.75.75 0 0 1 0 1.06L7.81 7.25H13a5.75 5.75 0 0 1 0 11.5h-3a.75.75 0 0 1 0-1.5h3a4.25 4.25 0 0 0 0-8.5H7.81l1.72 1.72a.75.75 0 1 1-1.06 1.06l-3-3a.75.75 0 0 1 0-1.06l3-3a.75.75 0 0 1 1.06 0Z" />
                            </svg>
                        @elseif($card['icon'] === 'receivables')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                            </svg>
                        @elseif($card['icon'] === 'revenue')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 17.25V6.75m5.25 10.5V9.75m5.25 7.5v-4.5m5.25 4.5V4.5" />
                            </svg>
                        @elseif($card['icon'] === 'delivery')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 18.75a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0Zm10.5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0ZM8.25 18.75h7.5M3.75 5.25h10.5v9.75H3.75V5.25Zm10.5 3h3.129c.398 0 .779.158 1.061.439l1.371 1.372c.281.281.439.663.439 1.06V15h-6V8.25Z" />
                            </svg>
                        @elseif($card['icon'] === 'production')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 19.5h15m-13.5 0V9.75l4.5-3 4.5 3v9.75m-9 0h9m-4.5 0V14.25" />
                            </svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.57 3.862l-7.5 13.5A1.5 1.5 0 004.38 19.5h15.24a1.5 1.5 0 001.31-2.138l-7.5-13.5a1.5 1.5 0 00-2.62 0Z" />
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="mt-8">
                    <div class="text-4xl font-bold leading-none {{ $tone['value'] }}">
                        {{ $card['value'] }}
                    </div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ $card['caption'] }}
                    </p>
                </div>
            </a>
        @endforeach
    </div>
</div>
