@extends('layouts.app')

@php
    // Use only the invoice number so browser "Save as PDF" defaults to e.g. "INV-000001.pdf"
    $title = $invoice->number;
@endphp

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
@endphp
<div class="space-y-8 print-invoice print:!mx-0 print:!w-full print:!max-w-none">
    <!-- Header with gradient (screen only) -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 print-hidden">
        <div>
            <div class="flex items-start gap-4">
                <!-- Invoice Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Invoice {{ $invoice->number }}
                        </h1>
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                'issued' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                'sent' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                'paid' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                'overdue' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                            ];
                            $statusColor = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                            $statusLabel = ucfirst(str_replace('_', ' ', $invoice->status));
                        @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium {{ $statusColor }}">
                            <span class="h-1.5 w-1.5 rounded-full 
                                {{ $invoice->status === 'paid' ? 'bg-success-500' : 
                                   ($invoice->status === 'overdue' ? 'bg-error-500' : 
                                   ($invoice->status === 'sent' ? 'bg-blue-light-500' : 'bg-gray-500')) }}">
                            </span>
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Order #{{ $invoice->order_id ?? '—' }} · {{ $invoice->order?->agent->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="mt-1 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>Issued {{ optional($invoice->issued_at)->format('d M Y') }}</span>
                        @if($invoice->due_at)
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>Due {{ $invoice->due_at->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.finance.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Finance
            </a>
            <button type="button" 
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                </svg>
                Print
            </button>
            <button type="button" 
                    id="btn-open-credit-modal"
                    class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-5 py-2.5 text-sm font-medium text-orange-700 shadow-xs hover:bg-orange-100 dark:border-orange-800 dark:bg-orange-900/30 dark:text-orange-400 dark:hover:bg-orange-900/50 transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                Add Credit
            </button>
            <button type="button" 
                    id="btn-open-receipt-modal"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
                Add Receipt
            </button>
        </div>
    </div>

    <!-- Clean A4-style header for print only -->
    <div class="hidden print-only print-header mb-8 print:!mx-0 print:!w-full print:!max-w-none">
        <div class="flex items-start justify-between gap-8">
                <div class="flex items-center gap-3">
                    @php
                    $printCompanyName = $legalCompanyName ?? config('app.name');
                    $printCompanyInitials = $legalCompanyInitials ?? mb_strtoupper(mb_substr($printCompanyName, 0, 2));
                @endphp
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white">
                    {{ $printCompanyInitials }}
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $printCompanyName }}
                    </h2>
                    <p class="mt-1 text-xs text-gray-600">
                        Invoice &amp; Finance
                    </p>
                </div>
            </div>
            <div class="print-header-summary text-right space-y-1">
                <h1 class="text-xl font-bold text-gray-900">
                    Invoice {{ $invoice->number }}
                </h1>
                <p class="text-xs text-gray-600">
                    Order #{{ $invoice->order_id ?? '—' }} · {{ $invoice->order?->agent->name ?? 'Unassigned' }}
                </p>
                <p class="text-xs text-gray-600">
                    Issued: {{ optional($invoice->issued_at)->format('d M Y') }}
                    @if($invoice->due_at)
                        · Due: {{ $invoice->due_at->format('d M Y') }}
                    @endif
                </p>
                <p class="text-xs text-gray-600">
                    Status: {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </p>
            </div>
        </div>
    </div>

    @php
        // Gross invoice value (net + VAT)
        $grossTotal = $invoice->net_total + $invoice->vat_amount;
        // Amount customer should actually pay in cash after withholding
        $cashTotal = $grossTotal - $invoice->withholding;
        $creditsTotal = $invoice->creditNotes->sum('amount');
        $receiptsTotal = $invoice->receipts->sum('amount');
        $advancesTotal = $invoice->advanceApplications->sum('amount');
        $outstanding   = $cashTotal - $creditsTotal - $receiptsTotal - $advancesTotal;
    @endphp

    <!-- Financial Summary Cards (screen only) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 print-hidden">
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Net Total</span>
                    <div class="rounded-lg bg-brand-100 p-2 dark:bg-brand-900/30">
                        <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($invoice->net_total, 2) }}</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">VAT</span>
                    <div class="rounded-lg bg-success-100 p-2 dark:bg-success-900/30">
                        <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($invoice->vat_amount, 2) }}</p>
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
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Withholding</span>
                    <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30">
                        <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-1.5-2.25h3M12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($invoice->withholding, 2) }}</p>
                    <form action="{{ route('admin.finance.withholding.update', $invoice) }}" method="POST" class="flex items-center gap-1">
                        @csrf
                        @method('PATCH')
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">{{ $currencyCode }}</span>
                            <input id="withholding_amount" 
                                   name="withholding" 
                                   type="number" 
                                   step="0.01"
                                   value="{{ old('withholding', $invoice->withholding) }}" 
                                   class="w-28 rounded-lg border border-gray-200 bg-white/50 pl-8 pr-2 py-1.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
                        </div>
                        <button type="submit" 
                                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                            Update
                        </button>
                    </form>
                </div>
                @error('withholding')
                    <p class="mt-1 text-xs text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H3m6.75 0H21" />
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Invoice Total</span>
                    <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                        <svg class="h-4 w-4 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v9.25m-1.5-9H5.625m-.75 0H4.5m10.5 6h3.75M4.5 15h9.75" />
                        </svg>
                    </div>
                </div>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($grossTotal, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Credits & Receipts Summary (screen only) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 print-hidden">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30">
                        <svg class="h-4 w-4 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Credits</h3>
                </div>
                <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $currencyCode }} {{ number_format($creditsTotal, 2) }}</span>
            </div>
            
            @if($invoice->creditNotes->isNotEmpty())
                <div class="mt-4 space-y-3">
                    @foreach($invoice->creditNotes as $credit)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-medium text-gray-900 dark:text-white">{{ $credit->number }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $credit->issued_at?->format('d M Y') }}</span>
                                </div>
                                @if($credit->reason)
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $credit->reason }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ $currencyCode }} {{ number_format($credit->amount, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No credit notes for this invoice.</p>
            @endif

            @if($invoice->creditNotes->isNotEmpty())
                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Credit notes are preserved for audit history and cannot be deleted from this screen.
                </p>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-success-100 dark:bg-success-900/30">
                        <svg class="h-4 w-4 text-success-700 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Receipts</h3>
                </div>
                <span class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $currencyCode }} {{ number_format($receiptsTotal, 2) }}</span>
            </div>
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3 dark:border-gray-800">
                <span class="text-sm text-gray-600 dark:text-gray-400">Outstanding Balance</span>
                <span class="text-lg font-bold {{ $outstanding > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-400' }}">
                    {{ $currencyCode }} {{ number_format(max(0, $outstanding), 2) }}
                </span>
            </div>
            
            @if($invoice->receipts->isNotEmpty())
                <div class="mt-4 space-y-3">
                    @foreach($invoice->receipts as $receipt)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $receipt->payment_method ?? '—' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $receipt->received_at?->format('d M Y') }}</span>
                                </div>
                                @if($receipt->notes)
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $receipt->notes }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-success-600 dark:text-success-400">{{ $currencyCode }} {{ number_format($receipt->amount, 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No receipts recorded yet.</p>
            @endif

            @if($invoice->receipts->isNotEmpty())
                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Receipts are preserved for audit history and cannot be deleted from this screen.
                </p>
            @endif
        </div>
    </div>

    <!-- Simple A4-style invoice body (print only) -->
    <div class="print-only print-sheet mt-6 text-[12px] leading-relaxed text-gray-900 print:!mx-0 print:!w-full print:!max-w-none">
        <div class="print-meta-grid mb-6">
            <div class="print-billto">
                <h3 class="font-semibold text-[13px] uppercase tracking-[0.08em]">Bill to</h3>
                <p class="mt-2 text-[13px] leading-6">
                    {{ $invoice->order?->agent->name ?? 'Customer' }}<br>
                </p>
            </div>
            <div class="print-meta-card">
                <div class="print-meta-row">
                    <span>Invoice No</span>
                    <strong>{{ $invoice->number }}</strong>
                </div>
                <div class="print-meta-row">
                    <span>Order No</span>
                    <span>{{ $invoice->order_id ?? '—' }}</span>
                </div>
                <div class="print-meta-row">
                    <span>Issued</span>
                    <span>{{ optional($invoice->issued_at)->format('d M Y') }}</span>
                </div>
                @if($invoice->due_at)
                    <div class="print-meta-row">
                        <span>Due</span>
                        <span>{{ $invoice->due_at->format('d M Y') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Items table --}}
        <table class="print-items-table w-full border-collapse text-[11px]">
            <colgroup>
                <col style="width: 6%">
                <col style="width: 50%">
                <col style="width: 12%">
                <col style="width: 16%">
                <col style="width: 16%">
            </colgroup>
            <thead>
                <tr>
                    <th class="border border-gray-300 px-3 py-2 text-left">#</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Description</th>
                    <th class="border border-gray-300 px-3 py-2 text-right">Qty</th>
                    <th class="border border-gray-300 px-3 py-2 text-right">Unit Price</th>
                    <th class="border border-gray-300 px-3 py-2 text-right">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td class="border border-gray-200 px-3 py-2 align-top text-left">{{ $idx + 1 }}</td>
                        <td class="border border-gray-200 px-3 py-2 align-top text-left">
                            {{ $item->description }}
                        </td>
                        <td class="border border-gray-200 px-3 py-2 align-top text-right">{{ $item->quantity }}</td>
                        <td class="border border-gray-200 px-3 py-2 align-top text-right">
                            {{ number_format($item->unit_price, 2) }}
                        </td>
                        <td class="border border-gray-200 px-3 py-2 align-top text-right">
                            {{ number_format($item->line_total, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="mt-6 flex justify-end">
            <table class="print-totals text-[11px]">
                <tr>
                    <td class="px-0 py-1.5 text-left">Net total:</td>
                    <td class="px-0 py-1.5 text-right">{{ number_format($invoice->net_total, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-1.5 text-left">VAT:</td>
                    <td class="px-0 py-1.5 text-right">{{ number_format($invoice->vat_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-1.5 text-left">Gross total:</td>
                    <td class="px-0 py-1.5 text-right">{{ number_format($grossTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-1.5 text-left">Withholding:</td>
                    <td class="px-0 py-1.5 text-right">- {{ number_format($invoice->withholding, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-1.5 text-left">Cash due:</td>
                    <td class="px-0 py-1.5 text-right">{{ number_format($cashTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-1.5 text-left">Credits:</td>
                    <td class="px-0 py-1.5 text-right">- {{ number_format($creditsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-1.5 text-left">Receipts:</td>
                    <td class="px-0 py-1.5 text-right">- {{ number_format($receiptsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-1.5 text-left">Advances:</td>
                    <td class="px-0 py-1.5 text-right">- {{ number_format($advancesTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-0 py-2.5 text-left font-semibold border-t border-gray-300">Outstanding:</td>
                    <td class="px-0 py-2.5 text-right font-semibold border-t border-gray-300">
                        {{ number_format(max(0, $outstanding), 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Invoice Items (screen only) -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden print-hidden">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                    <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Items</h3>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($invoice->items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $item->product->sku ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $item->description }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                                {{ $currencyCode }} {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">
                                {{ $currencyCode }} {{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receipt-modal" 
         class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm dark:bg-gray-900/80"
         style="display: none;">
        <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                        <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Record Receipt</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Invoice {{ $invoice->number }} · Outstanding {{ $currencyCode }} {{ number_format(max(0, $outstanding), 2) }}
                        </p>
                    </div>
                </div>
                <button type="button" 
                        data-receipt-modal-close
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.finance.receipts.store', $invoice) }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="modal_amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Amount <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">{{ $currencyCode }}</span>
                            @php
                                $suggestedModal  = max(0, $outstanding);
                            @endphp
                            <input type="number" 
                                   step="0.01" 
                                   name="amount" 
                                   id="modal_amount"
                                   value="{{ old('amount', $suggestedModal) }}" 
                                   required
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                    
                    <div>
                        <label for="modal_payment_method" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Payment Method
                        </label>
                        <select name="payment_method" 
                                id="modal_payment_method"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @php
                                $currentMethod = strtolower(old('payment_method', 'bank_transfer'));
                            @endphp
                            <option value="bank_transfer"{{ $currentMethod === 'bank_transfer' ? ' selected' : '' }}>Bank Transfer</option>
                            <option value="cash"{{ $currentMethod === 'cash' ? ' selected' : '' }}>Cash</option>
                            <option value="cheque"{{ $currentMethod === 'cheque' ? ' selected' : '' }}>Cheque</option>
                            <option value="mobile_banking"{{ $currentMethod === 'mobile_banking' ? ' selected' : '' }}>Mobile Banking</option>
                            <option value="other"{{ $currentMethod === 'other' ? ' selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="modal_received_at" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Received Date
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                            <input type="date" 
                                   name="received_at" 
                                   id="modal_received_at" 
                                   value="{{ old('received_at', now()->toDateString()) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                    
                    <div class="sm:col-span-2">
                        <label for="modal_notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   name="notes" 
                                   id="modal_notes" 
                                   value="{{ old('notes') }}" 
                                   placeholder="Optional reference or remarks"
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button type="button" 
                            data-receipt-modal-close
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Save Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Credit Note Modal -->
    <div id="credit-modal" 
         class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm dark:bg-gray-900/80"
         style="display: none;">
        <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-500/10">
                        <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add Credit Note</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Invoice {{ $invoice->number }} · Total {{ $currencyCode }} {{ number_format($grossTotal, 2) }}
                        </p>
                    </div>
                </div>
                <button type="button" 
                        data-credit-modal-close
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.finance.credit-notes.store', $invoice) }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="credit_amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Credit Amount <span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">{{ $currencyCode }}</span>
                            <input type="number" 
                                   id="credit_amount" 
                                   name="amount" 
                                   step="0.01" 
                                   min="0.01"
                                   value="{{ old('amount') }}" 
                                   required
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="credit_reason" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reason
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute left-3 top-3 flex items-start">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                            <textarea id="credit_reason" 
                                      name="reason" 
                                      rows="3"
                                      placeholder="e.g. Damaged goods, Pricing adjustment, Customer satisfaction"
                                      class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button type="button" 
                            data-credit-modal-close
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Save Credit Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/invoice-print.css') }}?v={{ filemtime(public_path('css/invoice-print.css')) }}">
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Receipt modal
        const receiptModal = document.getElementById('receipt-modal');
        const btnOpenReceipt = document.getElementById('btn-open-receipt-modal');
        const btnCloseReceipt = document.querySelectorAll('[data-receipt-modal-close]');
        
        if (btnOpenReceipt) {
            btnOpenReceipt.addEventListener('click', () => {
                receiptModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        }
        
        btnCloseReceipt.forEach(btn => {
            btn.addEventListener('click', () => {
                receiptModal.style.display = 'none';
                document.body.style.overflow = '';
            });
        });
        
        // Credit modal
        const creditModal = document.getElementById('credit-modal');
        const btnOpenCredit = document.getElementById('btn-open-credit-modal');
        const btnCloseCredit = document.querySelectorAll('[data-credit-modal-close]');
        
        if (btnOpenCredit) {
            btnOpenCredit.addEventListener('click', () => {
                creditModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        }
        
        btnCloseCredit.forEach(btn => {
            btn.addEventListener('click', () => {
                creditModal.style.display = 'none';
                document.body.style.overflow = '';
            });
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === receiptModal) {
                receiptModal.style.display = 'none';
                document.body.style.overflow = '';
            }
            if (e.target === creditModal) {
                creditModal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });
</script>
@endpush
@endsection
