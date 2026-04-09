@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        VAT Summary
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        VAT payable for {{ $month->format('F Y') }} (from ledger)
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <!-- Month Navigation - Only show if route exists -->
            @if(Route::has('admin.vat.summary'))
            <div class="flex items-center gap-1 rounded-lg border border-gray-300 bg-white shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                <a href="{{ route('admin.vat.summary', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" 
                   class="inline-flex h-9 w-9 items-center justify-center rounded-l-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="h-4 w-px bg-gray-200 dark:bg-gray-700"></span>
                <span class="px-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $month->format('M Y') }}
                </span>
                <span class="h-4 w-px bg-gray-200 dark:bg-gray-700"></span>
                <a href="{{ route('admin.vat.summary', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" 
                   class="inline-flex h-9 w-9 items-center justify-center rounded-r-lg text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @else
            <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg">
                {{ $month->format('M Y') }}
            </span>
            @endif
            
            <!-- Current Period Button - Only show if route exists -->
            @if(Route::has('admin.vat.summary'))
            <a href="{{ route('admin.vat.summary') }}" 
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Current Period
            </a>
            @endif

            @include('admin.finance.partials.print_button', ['label' => 'Print Report'])
        </div>
    </div>

    <!-- VAT Payable Card -->
    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
        <!-- Decorative background -->
        <div class="absolute right-0 top-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-64 w-64 rounded-full bg-gradient-to-br from-success-100 to-success-50 opacity-20 dark:from-success-900 dark:to-success-800 blur-3xl"></div>
        
        <div class="relative p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Net VAT Payable</p>
                        <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                            BDT {{ number_format($vatCollected, 2) }}
                        </p>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            Output VAT less input VAT for {{ $month->format('F Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Output VAT</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">BDT {{ number_format($outputVat, 2) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">From issued sales invoices</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Input VAT</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">BDT {{ number_format($inputVat, 2) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">From recorded purchase bills</p>
        </div>
    </div>

    <!-- Detailed VAT table -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Detailed VAT by invoice</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    From {{ $from->toDateString() }} to {{ $to->toDateString() }}
                </p>
            </div>
        </div>

        @if($invoiceRows->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No invoices found in this period.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Invoice</th>
                            <th class="px-3 py-2 text-left">Customer</th>
                            <th class="px-3 py-2 text-right">Taxable amount</th>
                            <th class="px-3 py-2 text-right">VAT</th>
                            <th class="px-3 py-2 text-right">VAT rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($invoiceRows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                    {{ \Illuminate\Support\Carbon::parse($row['date'])->toDateString() }}
                                </td>
                                <td class="px-3 py-2 font-mono text-[11px] text-gray-600 dark:text-gray-300">
                                    {{ $row['number'] }}
                                </td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                                    {{ $row['customer'] ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-800 dark:text-gray-100">
                                    BDT {{ number_format($row['taxable'], 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-800 dark:text-gray-100">
                                    BDT {{ number_format($row['vat'], 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300">
                                    {{ $row['vat_rate'] !== null ? $row['vat_rate'].'%' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right">Totals</td>
                            <td class="px-3 py-2 text-right">
                                BDT {{ number_format($totals['taxable'], 2) }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                BDT {{ number_format($totals['vat'], 2) }}
                            </td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Input VAT by purchase bill</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Input VAT recorded on supplier bills in this period.</p>
            </div>
        </div>

        @if($purchaseRows->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No purchase bills found in this period.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Bill</th>
                            <th class="px-3 py-2 text-left">Supplier</th>
                            <th class="px-3 py-2 text-right">Taxable amount</th>
                            <th class="px-3 py-2 text-right">VAT</th>
                            <th class="px-3 py-2 text-right">VAT rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($purchaseRows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ \Illuminate\Support\Carbon::parse($row['date'])->toDateString() }}</td>
                                <td class="px-3 py-2 font-mono text-[11px] text-gray-600 dark:text-gray-300">{{ $row['number'] }}</td>
                                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['supplier'] ?? '—' }}</td>
                                <td class="px-3 py-2 text-right text-gray-800 dark:text-gray-100">BDT {{ number_format($row['taxable'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-800 dark:text-gray-100">BDT {{ number_format($row['vat'], 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300">{{ $row['vat_rate'] !== null ? $row['vat_rate'].'%' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-right">Totals</td>
                            <td class="px-3 py-2 text-right">BDT {{ number_format($purchaseTotals['taxable'], 2) }}</td>
                            <td class="px-3 py-2 text-right">BDT {{ number_format($purchaseTotals['vat'], 2) }}</td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
