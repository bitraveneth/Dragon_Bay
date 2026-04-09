@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <header class="panel-header mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-title-sm font-semibold text-gray-900 dark:text-white">Add expense</h1>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Record a new expense entry.</p>
            </div>
            <a href="{{ route('admin.expenses.index') }}" 
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.8333 10H4.16667M4.16667 10L8.33333 14.1667M4.16667 10L8.33333 5.83333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to expenses
            </a>
        </header>

        <form action="{{ route('admin.expenses.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.finance.expenses.partials.form', ['expense' => $expense ?? null])
            
            <div class="form-actions flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.expenses.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:bg-brand-500 dark:hover:bg-brand-600">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Save expense
                </button>
            </div>
        </form>
    </section>
</div>
@endsection