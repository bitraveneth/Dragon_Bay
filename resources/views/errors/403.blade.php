@extends('layouts.app')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="w-full max-w-lg rounded-3xl border border-gray-200 bg-white/80 p-8 text-center shadow-theme-lg dark:border-gray-800 dark:bg-gray-900/80">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-300">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M4.5 4.5l15 15M12 4a8 8 0 018 8c0 1.676-.513 3.233-1.392 4.523M12 4a8 8 0 00-8 8c0 1.676.513 3.233 1.392 4.523" />
            </svg>
        </div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            You don’t have permission to view this page
        </h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Your role doesn’t include access to this area of ERP. If you think this is a mistake, please contact your system administrator or super admin.
        </p>

        @auth
            <p class="mt-4 text-xs uppercase tracking-[0.12em] text-gray-400 dark:text-gray-500">
                Signed in as {{ auth()->user()->email }} ·
                <span class="font-semibold">
                    {{ strtoupper(auth()->user()->role ?? 'employee') }}
                </span>
            </p>
        @endauth

        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                </svg>
                Back to dashboard
            </a>
            <button type="button"
                    onclick="history.back()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                Go back
            </button>
        </div>
    </div>
</div>
@endsection
