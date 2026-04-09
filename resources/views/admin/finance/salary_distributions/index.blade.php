@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $selectedMonth = request('month', $month->format('Y-m'));
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5h18M6.75 3v3m10.5-3v3M5.25 21h13.5A2.25 2.25 0 0021 18.75V7.5a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 7.5v11.25A2.25 2.25 0 005.25 21z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Salary Distributions</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                        Payroll breakdown for {{ $month->format('F Y') }}
                        @if($selectedEmployeeName)
                            <span class="font-medium text-gray-700 dark:text-gray-200">· {{ $selectedEmployeeName }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.salary-distributions.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add distribution
            </a>
        </div>
    </div>

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
            <form method="GET" action="{{ route('admin.salary-distributions.index') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Payroll filters</div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Choose the payroll month and narrow the register to one employee if needed.</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900 dark:text-gray-200">
                        {{ $month->format('F Y') }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Month</span>
                        <input type="month"
                               name="month"
                               value="{{ $selectedMonth }}"
                               class="h-12 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </label>
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Employee</span>
                        <select name="employee_id"
                                class="h-12 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">All employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected((string) request('employee_id') === (string) $employee->id)>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-300">
                        @if($selectedEmployeeName)
                            Showing payroll rows for <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $selectedEmployeeName }}</span>
                        @else
                            Showing payroll rows for <span class="font-semibold text-gray-700 dark:text-gray-200">all employees</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if(request()->filled('employee_id') || request()->filled('month'))
                            <a href="{{ route('admin.salary-distributions.index') }}"
                               class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                                Reset
                            </a>
                        @endif
                        <button type="submit"
                                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Apply filters
                        </button>
                    </div>
                </div>
            </form>

            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/50">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Period summary</div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">Quick payroll totals for the selected month.</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-theme-xs dark:bg-gray-900 dark:text-gray-200">
                        {{ $rows->total() }} rows
                    </span>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-white p-5 shadow-theme-xs dark:bg-gray-900">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total payroll</div>
                        <div class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($total, 2) }}</div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Gross salary, allowances, and commission combined</div>
                    </div>
                    <div class="rounded-2xl bg-white p-5 shadow-theme-xs dark:bg-gray-900">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Average per employee</div>
                        <div class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($averageDistribution, 2) }}</div>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">Average distribution across the filtered employee set</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Employees paid</span>
                <span class="rounded-xl bg-brand-50 p-2 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5V4H2v16h5m10 0v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m12 0H7m10-10a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ $employeeCount }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-300">Unique employees in this payroll run</div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Attached documents</span>
                <span class="rounded-xl bg-purple-50 p-2 text-purple-600 dark:bg-purple-500/10 dark:text-purple-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 3h6.879a2.25 2.25 0 011.591.659l3.371 3.371A2.25 2.25 0 0120 8.621V19.5A1.5 1.5 0 0118.5 21h-13A1.5 1.5 0 014 19.5v-15A1.5 1.5 0 015.5 3h2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ $withDocuments }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-300">Distributions with uploaded support</div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Bank transfers</span>
                <span class="rounded-xl bg-success-50 p-2 text-success-600 dark:bg-success-500/10 dark:text-success-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5L12 4l9 6.5M4.5 9.75V18h15V9.75M9 13.5h6" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ $bankTransfers }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-300">Records marked for bank payment</div>
        </article>

        <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Month</span>
                <span class="rounded-xl bg-orange-50 p-2 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ $month->format('F Y') }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-300">Current salary distribution window</div>
        </article>
    </section>

    <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Distribution Register</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">One payroll row per employee for the selected period.</p>
            </div>
            <div class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $rows->total() }} distribution{{ $rows->total() === 1 ? '' : 's' }}
            </div>
        </div>

        @if($rows->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/60">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Employee</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Period</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Breakdown</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Payment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Support</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Total</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($rows as $row)
                            @php
                                $totalRow = (float) $row->base_salary
                                    + (float) $row->bonus
                                    + (float) $row->ta_allowances
                                    + (float) $row->da_allowances
                                    + (float) $row->commission;
                                $paymentMethod = strtolower((string) ($row->payment_method ?? 'bank'));
                                $paymentTone = match ($paymentMethod) {
                                    'cash' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                                    'cheque', 'check' => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400',
                                    'online' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300',
                                    default => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                                };
                            @endphp
                            <tr class="align-top transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                                            {{ strtoupper(substr($row->employee?->name ?? '?', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $row->employee?->name ?? '—' }}</div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Employee ID: {{ $row->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm text-gray-700 dark:text-gray-200">
                                    <div>{{ optional($row->period_start)->format('d M Y') }}</div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">to {{ optional($row->period_end)->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <div class="rounded-xl bg-gray-50 px-3 py-2 text-xs dark:bg-gray-800/60">
                                            <div class="text-gray-500 dark:text-gray-400">Base salary</div>
                                            <div class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($row->base_salary, 2) }}</div>
                                        </div>
                                        <div class="rounded-xl bg-gray-50 px-3 py-2 text-xs dark:bg-gray-800/60">
                                            <div class="text-gray-500 dark:text-gray-400">Bonus</div>
                                            <div class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($row->bonus, 2) }}</div>
                                        </div>
                                        <div class="rounded-xl bg-gray-50 px-3 py-2 text-xs dark:bg-gray-800/60">
                                            <div class="text-gray-500 dark:text-gray-400">TA / DA</div>
                                            <div class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format((float) $row->ta_allowances + (float) $row->da_allowances, 2) }}</div>
                                        </div>
                                        <div class="rounded-xl bg-gray-50 px-3 py-2 text-xs dark:bg-gray-800/60">
                                            <div class="text-gray-500 dark:text-gray-400">Commission</div>
                                            <div class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($row->commission, 2) }}</div>
                                        </div>
                                    </div>
                                    @if($row->remarks)
                                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Remarks:</span> {{ $row->remarks }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $paymentTone }}">
                                        {{ ucfirst($row->payment_method ?? 'bank') }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    @if($row->document_path)
                                        <a href="{{ asset('storage/'.$row->document_path) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16" />
                                            </svg>
                                            View file
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400 dark:text-gray-500">No document</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="text-lg font-bold text-brand-700 dark:text-brand-400">{{ $currencyCode }} {{ number_format($totalRow, 2) }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.salary-distributions.edit', $row) }}"
                                           class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white p-2.5 text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.7 6.3l3 3M5 16.5V19h2.5l9.8-9.8-2.5-2.5L5 16.5z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.salary-distributions.destroy', $row) }}" method="POST" class="inline-flex" onsubmit="return confirm('Delete this salary distribution record? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white p-2.5 text-error-700 shadow-theme-xs hover:bg-error-50 dark:border-gray-700 dark:bg-gray-800 dark:text-error-400 dark:hover:bg-error-500/20">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12M9 7V5.5A1.5 1.5 0 0110.5 4h3A1.5 1.5 0 0115 5.5V7m-7.5 0l.6 10.2A1.5 1.5 0 009.6 18.6h4.8a1.5 1.5 0 001.5-1.4L16.5 7" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $rows->links() }}
            </div>
        @else
            <div class="px-6 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-gray-100 dark:bg-gray-800">
                    <svg class="h-8 w-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-xl font-semibold text-gray-900 dark:text-white">No salary distributions found</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Try another month or create the first payroll distribution for this period.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.salary-distributions.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add first distribution
                    </a>
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
