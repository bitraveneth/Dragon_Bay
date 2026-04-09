@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Purchase Bills
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    Accounts Payable
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Track supplier bills and payments.
            </p>
        </div>
        <a href="{{ route('admin.bills.create') }}" 
           class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Bill
        </a>
    </div>

    <!-- Status Message -->

    @if($bills->isNotEmpty())
        @php
            $totalOutstanding = $bills->where('status', '!=', 'paid')->sum(function($bill) {
                return ($bill->net_total + $bill->vat_amount) - $bill->payments->sum('amount');
            });
            $totalDue = $bills->where('status', '!=', 'paid')->count();
            $totalPaid = $bills->sum(function($bill) {
                return $bill->payments->sum('amount');
            });
        @endphp

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Bills</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $bills->total() }}</p>
                    </div>
                    <div class="rounded-lg bg-brand-50 p-2.5 dark:bg-brand-500/10">
                        <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Outstanding</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">BDT {{ number_format($totalOutstanding, 2) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $totalDue }} {{ Str::plural('bill', $totalDue) }} due</p>
                    </div>
                    <div class="rounded-lg bg-orange-50 p-2.5 dark:bg-orange-500/10">
                        <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Paid</p>
                        <p class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-400">BDT {{ number_format($totalPaid, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-success-50 p-2.5 dark:bg-success-500/10">
                        <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-5 border border-gray-200 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Overdue</p>
                        @php
                            $overdueCount = $bills->where('status', '!=', 'paid')
                                ->filter(fn($bill) => $bill->due_date && $bill->due_date->isPast())
                                ->count();
                        @endphp
                        <p class="mt-2 text-2xl font-semibold {{ $overdueCount > 0 ? 'text-error-600 dark:text-error-500' : 'text-gray-900 dark:text-white' }}">
                            {{ $overdueCount }}
                        </p>
                    </div>
                    <div class="rounded-lg {{ $overdueCount > 0 ? 'bg-error-50 dark:bg-error-500/10' : 'bg-gray-100 dark:bg-gray-800' }}">
                        <svg class="h-5 w-5 {{ $overdueCount > 0 ? 'text-error-500' : 'text-gray-500 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bills Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bill Register</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $bills->count() }} {{ Str::plural('bill', $bills->count()) }} shown
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Bill #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Supplier</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Net Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Paid</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($bills as $bill)
                            @php
                                $paid = $bill->payments->sum('amount');
                                $total = $bill->net_total + $bill->vat_amount;
                                $due = $total - $paid;
                                $isOverdue = $bill->due_date && $bill->due_date->isPast() && $bill->status !== 'paid';
                                
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'sent' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    'received' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'paid' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'cancelled' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                ];
                                $statusColor = $statusColors[$bill->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $isOverdue ? 'bg-error-50/30 dark:bg-error-500/5' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $bill->number }}
                                        </span>
                                        @if($bill->reference)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Ref: {{ $bill->reference }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-brand-100 dark:bg-brand-500/20 flex items-center justify-center">
                                            <span class="text-xs font-medium text-brand-700 dark:text-brand-400">
                                                {{ substr($bill->supplier->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $bill->supplier->name }}
                                            </p>
                                            @if($bill->supplier->tax_id)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    TAX: {{ $bill->supplier->tax_id }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $bill->bill_date->format('d M Y') }}
                                        </p>
                                        @if($bill->due_date)
                                            <p class="text-xs {{ $isOverdue ? 'text-error-600 dark:text-error-500' : 'text-gray-500 dark:text-gray-400' }}">
                                                Due: {{ $bill->due_date->format('d M Y') }}
                                                @if($isOverdue)
                                                    <span class="ml-1">(Overdue)</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        BDT {{ number_format($total, 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                                        <svg class="h-1.5 w-1.5 fill-current" viewBox="0 0 6 6">
                                            <circle cx="3" cy="3" r="3" />
                                        </svg>
                                        {{ ucfirst($bill->status) }}
                                    </span>
                                    @if($due > 0 && $bill->status !== 'paid')
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                            Due: BDT {{ number_format($due, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-medium {{ $paid >= $total ? 'text-success-600 dark:text-success-400' : 'text-gray-700 dark:text-gray-300' }}">
                                        BDT {{ number_format($paid, 2) }}
                                    </span>
                                    @if($paid > 0 && $paid < $total)
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                            ({{ round(($paid / $total) * 100) }}%)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($bill->status !== 'paid' && Route::has('admin.bills.pay'))
                                            <form action="{{ route('admin.bills.pay', $bill) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                    Pay Full
                                                </button>
                                            </form>
                                        @endif

                                        @if(Route::has('admin.bills.edit'))
                                            <a href="{{ route('admin.bills.edit', $bill) }}" 
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                        @endif

                                        @if(Route::has('admin.bills.destroy'))
                                            <form action="{{ route('admin.bills.destroy', $bill) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this bill?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $bills->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No purchase bills</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-md mx-auto">
                No purchase bills have been recorded yet. Add your first bill to start tracking supplier payments.
            </p>
            <a href="{{ route('admin.bills.create') }}" 
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Bill
            </a>
        </div>
    @endif
</div>
@endsection