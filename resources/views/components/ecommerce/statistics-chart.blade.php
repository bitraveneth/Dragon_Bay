@php
    $rangeLabel = 'Last 7 days';
@endphp

<div
    class="rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
    <div class="mb-6 flex flex-col gap-5 sm:flex-row sm:justify-between">
        <div class="w-full">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Activity Trends
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Orders, internal output, and billed value over the last 7 days.
            </p>
        </div>

        <div class="flex w-full items-start gap-3 sm:justify-end">
            <div x-data="{ selected: 'overview' }"
                class="inline-flex w-fit items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">
                @php
                    $options = [
                        ['value' => 'overview', 'label' => 'Overview'],
                        ['value' => 'sales', 'label' => 'Orders'],
                        ['value' => 'production', 'label' => 'Operations'],
                        ['value' => 'revenue', 'label' => 'Billing'],
                    ];
                @endphp

                @foreach ($options as $option)
                    <button
                        type="button"
                        data-stats-tab="{{ $option['value'] }}"
                        @click="selected = '{{ $option['value'] }}'"
                        :class="selected === '{{ $option['value'] }}'
                            ? 'shadow-theme-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800'
                            : 'text-gray-500 dark:text-gray-400'"
                        class="rounded-md px-3 py-2 text-theme-sm font-medium hover:text-gray-900 dark:hover:text-white">
                        {{ $option['label'] }}
                    </button>
                @endforeach
            </div>

            <div
                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $rangeLabel }}
            </div>
        </div>
    </div>

    <div class="w-full">
        <div id="chartThree" class="w-full"></div>
    </div>
</div>
