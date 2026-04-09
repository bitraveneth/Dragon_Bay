@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6" data-tour="finance-overview-header">
        <div>
            <div class="flex items-start gap-4">
                <!-- Finance Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                        </svg>
                    </div>
                </div>
                
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Client invoices
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Client billing, receipts, and outstanding balances
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Message -->

    @if($invoices->isNotEmpty())
        @php
            $totalNet = $invoices->sum('net_total');
            $totalVAT = $invoices->sum('vat_amount');
            $totalWithholding = $invoices->sum('withholding');
            $totalReceipts = $invoices->sum(fn($invoice) => $invoice->receipts->sum('amount'));
            $outstanding = $invoices->sum(fn($invoice) => (float) $invoice->outstanding);
            
            $statusBreakdown = $invoices->groupBy('status')->map->count();
        @endphp

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Invoices</span>
                        <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $invoices->total() ?? $invoices->count() }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if(isset($statusBreakdown['draft'])) <span class="text-gray-600">{{ $statusBreakdown['draft'] }} draft</span> · @endif
                        @if(isset($statusBreakdown['sent'])) <span class="text-blue-light-600">{{ $statusBreakdown['sent'] }} sent</span> · @endif
                        @if(isset($statusBreakdown['paid'])) <span class="text-success-600">{{ $statusBreakdown['paid'] }} paid</span> @endif
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Net Billed Value</span>
                        <div class="rounded-lg bg-blue-light-100 p-2 dark:bg-blue-light-900/30">
                            <svg class="h-4 w-4 text-blue-light-700 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">BDT {{ number_format($totalNet, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        VAT: BDT {{ number_format($totalVAT, 0) }}
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.25 8.25c0-1.5 1.5-3 3-3h13.5c1.5 0 3 1.5 3 3v7.5c0 1.5-1.5 3-3 3H5.25c-1.5 0-3-1.5-3-3v-7.5z" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Collected</span>
                        <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                            <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-success-600 dark:text-success-400">BDT {{ number_format($totalReceipts, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Withholding: BDT {{ number_format($totalWithholding, 0) }}
                    </p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                    <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6v6l4 2" />
                    </svg>
                </div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Outstanding</span>
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                            <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">BDT {{ number_format($outstanding, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $outstanding > 0 ? 'Awaiting payment' : 'Fully paid' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Client Invoices</h3>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $invoices->count() }} of {{ $invoices->total() ?? $invoices->count() }} invoices
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Order / Client</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Net</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">VAT</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Withholding</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Receipts</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($invoices as $invoice)
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                    'sent' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    'paid' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'overdue' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                    'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                ];
                                $statusColor = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                
                                $receiptsTotal = $invoice->receipts->sum('amount');
                                $cashTotal = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
                                $outstandingAmount = (float) $invoice->outstanding;
                                $canMarkPaid = $invoice->status !== 'paid' && $outstandingAmount > 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                            <span class="text-xs font-semibold text-brand-700 dark:text-brand-400">
                                                #{{ substr($invoice->number, -4) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $invoice->number }}
                                            </span>
                                            @if($invoice->issued_at)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $invoice->issued_at->format('d M Y') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($invoice->order)
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                #{{ $invoice->order_id }}
                                            </p>
                                            @if($invoice->order->agent)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $invoice->order->agent->name }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($invoice->net_total, 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ number_format($invoice->vat_amount, 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ number_format($invoice->withholding, 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-bold {{ $receiptsTotal >= $cashTotal ? 'text-success-600 dark:text-success-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ number_format($receiptsTotal, 0) }}
                                    </span>
                                    @if($outstandingAmount > 0)
                                        <p class="text-xs text-orange-600 dark:text-orange-400">
                                            Due: {{ number_format($outstandingAmount, 0) }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                                        <span class="h-1.5 w-1.5 rounded-full 
                                            {{ $invoice->status === 'paid' ? 'bg-success-500' : 
                                               ($invoice->status === 'sent' ? 'bg-blue-light-500' : 
                                               ($invoice->status === 'overdue' ? 'bg-error-500' : 'bg-gray-500')) }}">
                                        </span>
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($canMarkPaid)
                                            <form action="{{ route('admin.finance.receipts.store', $invoice) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Mark invoice {{ $invoice->number }} as paid for BDT {{ number_format($outstandingAmount, 0) }}?');">
                                                @csrf
                                                <input type="hidden" name="amount" value="{{ $outstandingAmount }}">
                                                <input type="hidden" name="payment_method" value="bank_transfer">
                                                <input type="hidden" name="received_at" value="{{ now()->format('Y-m-d') }}">
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Mark Paid
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{ route('admin.finance.credit-notes.create', $invoice) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-orange-600 shadow-sm hover:bg-orange-50 hover:text-orange-700 dark:border-gray-700 dark:bg-gray-800 dark:text-orange-500 dark:hover:bg-orange-500/10 transition-colors">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            Credit
                                        </a>
                                        
                                        <a href="{{ route('admin.finance.show', $invoice) }}" 
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                        
                                        <form action="{{ route('admin.finance.destroy', $invoice) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete invoice {{ $invoice->number }}? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-sm hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(method_exists($invoices, 'links'))
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm p-16 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900/50">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-gray-100 to-gray-50 opacity-20 dark:from-gray-900 dark:to-gray-800 blur-3xl"></div>
            
            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-700">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-500 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">No Client Invoices</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    No invoices have been created yet. Client billing records will appear here when orders are processed.
                </p>
            </div>
        </div>
    @endif
</div>
@endsection
