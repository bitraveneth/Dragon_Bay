<button
    type="button"
    onclick="window.print()"
    class="print-hidden inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
>
    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 9V4a1 1 0 011-1h10a1 1 0 011 1v5M6 15H5a2 2 0 01-2-2v-1a2 2 0 012-2h14a2 2 0 012 2v1a2 2 0 01-2 2h-1M9 15h6v5H9z"
        />
    </svg>
    {{ $label ?? 'Print' }}
</button>
