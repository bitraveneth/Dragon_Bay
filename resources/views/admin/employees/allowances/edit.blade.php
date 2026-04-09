@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Employee Avatar -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full blur opacity-20"></div>
                    <div class="relative">
                        @if($employee->photo_path)
                            <img src="{{ asset('storage/'.$employee->photo_path) }}" 
                                 alt="{{ $employee->name }}" 
                                 class="h-16 w-16 rounded-full object-cover border-4 border-white shadow-xl dark:border-gray-800">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                                {{ substr($employee->name, 0, 1) }}{{ substr($employee->name, strpos($employee->name, ' ') + 1, 1) ?? '' }}
                            </div>
                        @endif
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Edit Allowance
                        </h1>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                            {{ $employee->name }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Update dates, amounts, status, or slip
                    </p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $employee->job_position ?? 'Employee' }}</span>
                        @if($employee->department)
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>{{ $employee->department }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>Created {{ $allowance->created_at?->diffForHumans() }}</span>
                        <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                        <span>Last updated {{ $allowance->updated_at?->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees.allowances.index', $employee) }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Allowances
            </a>
        </div>
    </div>

    <!-- Current Allowance Status Banner -->
    <div class="rounded-xl border-l-4 
        {{ $allowance->status === 'paid' ? 'border-success-500 bg-success-50 dark:border-success-400 dark:bg-success-950/30' : 
           ($allowance->status === 'approved' ? 'border-brand-500 bg-brand-50 dark:border-brand-400 dark:bg-brand-950/30' : 
           ($allowance->status === 'submitted' ? 'border-orange-500 bg-orange-50 dark:border-orange-400 dark:bg-orange-950/30' : 
           'border-error-500 bg-error-50 dark:border-error-400 dark:bg-error-950/30')) }} p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                @if($allowance->status === 'paid')
                    <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @elseif($allowance->status === 'approved')
                    <svg class="h-5 w-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @elseif($allowance->status === 'submitted')
                    <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-error-600 dark:text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                @endif
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium">
                    <span class="font-semibold">Current Status:</span> 
                    <span class="inline-flex items-center gap-1.5 ml-1.5">
                        <span class="h-1.5 w-1.5 rounded-full 
                            {{ $allowance->status === 'paid' ? 'bg-success-500' : 
                               ($allowance->status === 'approved' ? 'bg-brand-500' : 
                               ($allowance->status === 'submitted' ? 'bg-orange-500' : 'bg-error-500')) }}">
                        </span>
                        <span class="capitalize">{{ $allowance->status }}</span>
                    </span>
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                    <span class="font-medium">Type:</span> {{ strtoupper($allowance->type) }}
                    @if($allowance->reference)
                        · <span class="font-medium">Reference:</span> {{ $allowance->reference }}
                    @endif
                    · <span class="font-medium">Amount:</span> BDT {{ number_format($allowance->amount, 0) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-brand-50 to-white px-8 py-6 dark:border-gray-800 dark:from-brand-950/30 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Allowance Details</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        Update the allowance information below
                    </p>
                </div>
            </div>
            
            <!-- Summary Preview -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-brand-100/50 p-4 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
                <div class="text-sm flex-1">
                    <span class="font-semibold">Allowance Summary:</span>
                    <span class="ml-2 inline-flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                            {{ strtoupper($allowance->type) }}
                        </span>
                        <span class="text-gray-900 dark:text-white font-medium">BDT {{ number_format($allowance->amount, 0) }}</span>
                        @if($allowance->attachment_path)
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Slip attached
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.employees.allowances.update', [$employee, $allowance]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                
                <!-- Allowance Form Partial -->
                @include('admin.employees.allowances.partials.form', ['allowance' => $allowance])
                
                <!-- Form Actions -->
                <div class="mt-10 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.employees.allowances.index', $employee) }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Allowance
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone - Delete Allowance -->
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
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this allowance record</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Once deleted, this allowance record and any attached slip cannot be recovered. 
                        This action cannot be undone.
                    </p>
                </div>
                <form action="{{ route('admin.employees.allowances.destroy', [$employee, $allowance]) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this {{ strtoupper($allowance->type) }} allowance of BDT {{ number_format($allowance->amount, 0) }} for {{ $employee->name }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Allowance
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
