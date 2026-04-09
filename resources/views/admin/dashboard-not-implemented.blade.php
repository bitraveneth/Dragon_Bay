@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-300">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 9V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <circle cx="12" cy="17" r="1" fill="currentColor"/>
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard setup pending</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    This dashboard design is not implemented yet for your role.
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Please contact Super Admin to map this role to an existing dashboard or create a dedicated one.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Current session</h2>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">User</p>
                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $userName }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Role keys</p>
                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                    {{ empty($roleKeys) ? 'No role assigned' : implode(', ', $roleKeys) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
