@props([
    'monthLabels' => [],
    'monthlyOrders' => [],
    'salesRangeMonths' => 12,
])

<div
    id="dashboard-monthly-sales-card"
    class="rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Monthly Sales
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Current month: {{ now()->format('F Y') }}
            </p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" data-dashboard-sales-form>
            <label for="monthly-sales-range" class="sr-only">Filter monthly sales range</label>
            <select
                id="monthly-sales-range"
                name="sales_range"
                data-dashboard-sales-range
                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-theme-xs focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
            >
                <option value="3" @selected((int) $salesRangeMonths === 3)>Last 3 months</option>
                <option value="6" @selected((int) $salesRangeMonths === 6)>Last 6 months</option>
                <option value="12" @selected((int) $salesRangeMonths === 12)>Last 12 months</option>
            </select>
        </form>
    </div>

    <div class="min-w-0 max-w-full pr-2">
        <div id="chartOne" class="h-56 w-full min-w-0"></div>
    </div>
</div>
