@extends('layouts.app')

@section('content')
@php($currencyCode = config('app.currency', 'BDT'))
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header with breadcrumb -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-3">
            <a href="{{ route('admin.finance.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition-colors">Finance</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span>Credit Notes</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 dark:text-white font-medium">New Credit Note</span>
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <!-- Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Create Credit Note</h1>
                        <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-xs font-medium">Invoice #{{ $invoice->number }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Order #{{ $invoice->order_id }} · {{ $invoice->order?->agent->name ?? '—' }}
                    </p>
                </div>
            </div>
            
            <a href="{{ route('admin.finance.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Finance
            </a>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <!-- Invoice Summary Section -->
        <div class="border-b border-gray-200 dark:border-gray-800 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-900 px-6 py-5">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Invoice Summary</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Net Total</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($invoice->net_total, 2) }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">VAT Amount</p>
                        <p class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $currencyCode }} {{ number_format($invoice->vat_amount, 2) }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-5m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total (incl. VAT)</p>
                        <p class="text-lg font-bold text-success-600 dark:text-success-400">{{ $currencyCode }} {{ number_format($invoice->net_total + $invoice->vat_amount, 2) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-error-100 dark:bg-error-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-error-600 dark:text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Remaining Credit Allowed</p>
                        <p class="text-lg font-bold text-error-600 dark:text-error-400">{{ $currencyCode }} {{ number_format($remainingCredit ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Credit Note Form -->
        <form action="{{ route('admin.finance.credit-notes.store', $invoice) }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Credit Note Details</h2>
                
                <!-- Amount Field -->
                <div class="space-y-2">
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Credit Amount <span class="text-error-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">{{ $currencyCode }}</span>
                        </div>
                        <input 
                            id="amount" 
                            name="amount" 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            max="{{ number_format($remainingCredit ?? 0, 2, '.', '') }}"
                            required
                            class="block w-full pl-8 pr-12 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:focus:border-brand-400 dark:focus:ring-brand-400 sm:text-sm transition-colors @error('amount') border-error-500 dark:border-error-400 @enderror"
                            placeholder="0.00"
                            value="{{ old('amount') }}"
                        >
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">BDT</span>
                        </div>
                    </div>
                    @error('amount')
                        <p class="text-sm text-error-600 dark:text-error-400 flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Remaining credit allowed after withholding, receipts, advances, and prior credit notes:
                        {{ $currencyCode }} {{ number_format($remainingCredit ?? 0, 2) }}
                    </p>
                </div>

                <!-- Reason Field -->
                <div class="space-y-2">
                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Reason (Optional)
                    </label>
                    <textarea 
                        id="reason" 
                        name="reason" 
                        rows="4"
                        class="block w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:focus:border-brand-400 dark:focus:ring-brand-400 sm:text-sm p-3 transition-colors @error('reason') border-error-500 dark:border-error-400 @enderror"
                        placeholder="e.g., Product return, Damaged goods, Customer discount, etc."
                    >{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-sm text-error-600 dark:text-error-400 flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Important Information Alert -->
                <div class="rounded-xl bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-900/20 p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-1">Important Information</h4>
                            <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
                                Creating a credit note will adjust the customer's account balance and may affect VAT reporting. 
                                This action cannot be automatically reversed. Please ensure the amount is correct before proceeding.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recent Credit Notes (if any) -->
                @if($invoice->creditNotes->isNotEmpty())
                <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                        Previous Credit Notes for this Invoice
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($invoice->creditNotes as $note)
                        <div class="flex items-center justify-between p-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                                    <span class="text-xs font-bold text-brand-700 dark:text-brand-400">CN</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $note->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $note->reason ?: 'No reason provided' }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-error-600 dark:text-error-400">-{{ $currencyCode }} {{ number_format($note->amount, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
                    <a href="{{ route('admin.finance.index') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Credit Note
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Help Card -->
    <div class="mt-6 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Need Help?</h4>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Credit notes are used to issue refunds or adjustments to customers. They will reduce the outstanding balance of this invoice 
                    and create a corresponding entry in the customer's account. For partial credits, you can create multiple credit notes against the same invoice.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
