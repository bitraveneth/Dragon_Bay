@extends('layouts.app')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 15L10 9L14 13L20 7V19H4V15Z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Manufacturing dashboard
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Current month production runs, batches and QC status.
                    </p>
                    <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                        Period: {{ $periodLabel }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 4h14v2H5zM5 9h10v2H5zM5 14h7v2H5z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Production runs</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRuns) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Created this month</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 6h16v12H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total quantity</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($totalQuantity, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Approved: {{ number_format($approvedQuantity, 0) }}
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Batches produced</p>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($batchCount, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $expiringSoon->count() }} batches expiring in next 60 days
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
                <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z" />
                </svg>
            </div>
            <div class="relative">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pending QC</p>
                <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">
                    {{ number_format($pendingQcCount, 0) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Runs not yet approved
                </p>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Top products --}}
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top products by output</h2>
            @if($topProducts->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No production runs for this period.</p>
            @else
                <ul class="space-y-3">
                    @foreach($topProducts as $product)
                        <li class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $product['product_name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Runs: {{ number_format($product['runs'], 0) }}
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($product['qty'], 0) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Output by line --}}
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Output by line</h2>
            @if($byLine->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No line data recorded.</p>
            @else
                <ul class="space-y-3">
                    @foreach($byLine as $line => $qty)
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Line {{ $line ?: '—' }}
                            </span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($qty, 0) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Output by shift --}}
        <div class="lg:col-span-1 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Output by shift</h2>
            @if($byShift->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No shift data recorded.</p>
            @else
                <ul class="space-y-3">
                    @foreach($byShift as $shift => $qty)
                        <li class="flex items-center justify-between">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Shift {{ $shift ?: '—' }}
                            </span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($qty, 0) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
