@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
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
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Edit Customer Gift
                        </h1>
                        <span class="inline-flex items-center rounded-full bg-pink-100 px-3 py-1 text-xs font-medium text-pink-700 dark:bg-pink-500/20 dark:text-pink-400">
                            {{ $gift->agent->name }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>{{ $gift->date?->format('d M Y') }}</span>
                        <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                        <span>Created {{ $gift->created_at?->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.gifts.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Gifts
            </a>
        </div>
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
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">Update the gift information below</p>
                </div>
            </div>
            
            <!-- Current Status Banner -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-pink-100/50 p-4 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">
                    <span class="font-semibold">Current Status:</span> 
                    <span class="inline-flex items-center gap-1.5 ml-1">
                        @php
                            $statusColors = [
                                'planned' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                'given' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                'cancelled' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                            ];
                            $statusColor = $statusColors[$gift->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $gift->status === 'given' ? 'bg-success-500' : ($gift->status === 'cancelled' ? 'bg-error-500' : 'bg-orange-500') }} mr-1"></span>
                            {{ ucfirst($gift->status) }}
                        </span>
                    </span>
                </p>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.gifts.update', $gift) }}" method="POST">
                @csrf
                @method('PATCH')
                
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Gift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone - Delete Gift -->
    <div class="rounded-2xl border border-error-200 bg-white shadow-sm dark:border-error-800/30 dark:bg-gray-900">
        <div class="border-b border-error-100 px-6 py-4 dark:border-error-800/20">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-medium text-error-700 dark:text-error-400">Danger Zone</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this gift record</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Once deleted, this gift record cannot be recovered. This action cannot be undone.
                    </p>
                </div>
                <form action="{{ route('admin.gifts.destroy', $gift) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this gift record for {{ $gift->agent->name }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Gift
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
