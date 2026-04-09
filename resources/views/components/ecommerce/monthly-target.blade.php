@props([
    'currencyCode' => 'BDT',
    'periodLabel' => '',
    'targetBasis' => 'No monthly sales target configured',
    'monthlySalesTarget' => 0,
    'monthlyAchieved' => 0,
    'todayAchieved' => 0,
    'progressPercent' => 0,
    'targetMonthOptions' => [],
])

<div id="dashboard-monthly-target-card"
    class="flex min-h-full items-center justify-center">
    <form method="GET" action="{{ route('admin.dashboard') }}" data-dashboard-target-form class="hidden">
        <input type="hidden" name="sales_range" value="{{ request('sales_range', 12) }}">
        <label for="monthly-target-range" class="sr-only">Filter monthly target month</label>
        <select
            id="monthly-target-range"
            name="target_month"
            data-dashboard-target-month
        >
            @foreach($targetMonthOptions as $option)
                <option value="{{ $option['value'] }}" @selected(request('target_month', now()->format('Y-m')) === $option['value'])>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
    </form>

    @php
        $progressValue = max(0, min(100, round((float) $progressPercent, 2)));
        $targetValue = max((float) $monthlySalesTarget, 0);
    @endphp

    <div class="w-full max-w-[420px] rounded-[28px] border border-gray-200/80 bg-gradient-to-br from-gray-50 via-white to-gray-100/70 px-4 py-5 dark:border-gray-800 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800/80">
        <div class="relative mx-auto max-w-[360px] overflow-hidden">
            <div data-target-visual class="min-h-[240px] overflow-hidden pt-1">
                <div
                    id="chartTwo"
                    class="-ml-2 min-h-[220px]"
                    data-progress-value="{{ $progressValue }}"
                    data-progress-label="{{ $progressValue }}%"
                ></div>
            </div>
        </div>

        <div class="-mt-1 text-center">
            <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-brand-700 dark:bg-brand-500/15 dark:text-brand-200">
                Monthly Sales Target
            </span>
            <p class="mt-3 text-lg font-semibold text-gray-800 dark:text-white/90">
                {{ $currencyCode }} {{ number_format($targetValue, 2) }}
            </p>
        </div>
    </div>
</div>
