@props(['title', 'subtitle', 'from', 'to', 'rows', 'type'])

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        </div>
        <form method="GET" class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-end">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
                <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
                <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-gray-900">Apply</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-800/70 dark:text-gray-400">
                    @if($type === 'mode')
                        <tr>
                            <th class="px-4 py-3">Mode</th>
                            <th class="px-4 py-3 text-right">Shipments</th>
                            <th class="px-4 py-3 text-right">Chargeable KG</th>
                            <th class="px-4 py-3 text-right">Revenue</th>
                            <th class="px-4 py-3 text-right">Cost</th>
                            <th class="px-4 py-3 text-right">Profit</th>
                        </tr>
                    @elseif($type === 'weight')
                        <tr>
                            <th class="px-4 py-3">Shipment</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3 text-right">Actual KG</th>
                            <th class="px-4 py-3 text-right">CBM</th>
                            <th class="px-4 py-3 text-right">Vol KG</th>
                            <th class="px-4 py-3 text-right">Charge KG</th>
                        </tr>
                    @else
                        <tr>
                            <th class="px-4 py-3">Shipment</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Mode</th>
                            <th class="px-4 py-3 text-right">Chargeable KG</th>
                            <th class="px-4 py-3 text-right">Revenue</th>
                            <th class="px-4 py-3 text-right">Cost</th>
                            <th class="px-4 py-3 text-right">Profit</th>
                        </tr>
                    @endif
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($rows as $row)
                        @if($type === 'mode')
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ strtoupper(str_replace('_', ' ', $row['mode'])) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['shipments']) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['chargeable_weight'], 3) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['revenue'], 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['cost'], 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['profit'], 2) }}</td>
                            </tr>
                        @elseif($type === 'weight')
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $row['shipment']->shipment_no }}</td>
                                <td class="px-4 py-3">{{ $row['client'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['actual_weight'], 3) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['cbm'], 4) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['volumetric_weight'], 3) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['chargeable_weight'], 3) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $row['shipment']->shipment_no }}</td>
                                <td class="px-4 py-3">{{ $row['client'] ?? '-' }}</td>
                                <td class="px-4 py-3">{{ strtoupper(str_replace('_', ' ', $row['mode'])) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['chargeable_weight'], 3) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['revenue'], 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['cost'], 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['profit'], 2) }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No report data for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
