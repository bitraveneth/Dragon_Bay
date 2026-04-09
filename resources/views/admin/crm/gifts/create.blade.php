@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-pink-500 to-rose-500 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875zm0 0V3m0 9.75v-1.5M3.375 7.5h17.25" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                        Add Customer Gift
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Record a gift for an agent/dealer
                    </p>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.gifts.index') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Gifts
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header with gift icon -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-pink-50 to-white px-8 py-6 dark:border-gray-800 dark:from-pink-950/30 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875zm0 0V3m0 9.75v-1.5M3.375 7.5h17.25" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gift Details</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">Fill in the information below to record a new gift</p>
                </div>
            </div>
            
            <!-- Info Banner -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-pink-100/50 p-4 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">
                    <span class="font-semibold">Tip:</span> Gifts help build stronger relationships with your agents and dealers. Track birthdays, anniversaries, and special occasions.
                </p>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.gifts.store') }}" method="POST">
                @csrf
                
                <!-- Gift Form Partial -->
                @include('admin.crm.gifts.partials.form', [
                    'gift' => $gift, 
                    'agents' => $agents, 
                    'employees' => $employees
                ])
                
                <!-- Form Actions -->
                <div class="mt-10 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.gifts.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-pink-500 to-rose-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-pink-600 hover:to-rose-600 focus:outline-none focus:ring-2 focus:ring-pink-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Save Gift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection