@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">New Sales Target</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Assign one target to either an agent or a sales employee for a defined period.</p>
    </div>

    <form action="{{ route('admin.sales-targets.store') }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 space-y-6">
        @csrf
        @include('admin.sales-targets.partials.form', ['salesTarget' => null])
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.sales-targets.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Cancel</a>
            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-sm hover:bg-brand-600">Save Target</button>
        </div>
    </form>
</div>
@endsection
