@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Packaging Master Section -->
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Packaging Master
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Define bottles, crates, cartons and their labels.
                </p>
            </div>
            <a href="{{ route('admin.packaging.index') }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </a>
        </div>

        <!-- Status Message -->

        <!-- Add Packaging Form -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Packaging</h3>
            </div>
            <form action="{{ route('admin.packaging.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Packaging Name -->
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Packaging Name <span class="text-error-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                            @error('name')
                                <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unit Label -->
                        <div>
                            <label for="unit" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Unit Label
                            </label>
                            <input type="text" id="unit" name="unit" value="{{ old('unit') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">{{ old('description') }}</textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Packaging
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Packaging Types List -->
        @if($packagingTypes->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Packaging Types</h3>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $packagingTypes->total() }} {{ Str::plural('type', $packagingTypes->total()) }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Updated</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($packagingTypes as $type)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $type->name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $type->unit ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $type->description ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $type->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.packaging.edit', $type) }}" 
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.packaging.destroy', $type) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Delete this packaging type? This action cannot be undone.');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($packagingTypes, 'links'))
                    <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                        {{ $packagingTypes->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No packaging types</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    No packaging types defined yet. Add your first packaging type using the form above.
                </p>
            </div>
        @endif
    </div>

    <!-- Unit Conversions Section -->
    <div class="space-y-6">
        <!-- Section Header -->
        <div class="border-t border-gray-200 pt-8 dark:border-gray-800">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Unit Conversions</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Define how many base units exist between packaging types.
            </p>
        </div>

        <!-- Add Conversion Form -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Conversion</h3>
            </div>
            <form action="{{ route('admin.packaging.conversions.store') }}" method="POST" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- From Packaging -->
                        <div>
                            <label for="from_packaging_type_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                From Packaging <span class="text-error-500">*</span>
                            </label>
                            <select id="from_packaging_type_id" name="from_packaging_type_id" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select packaging</option>
                                @foreach($packagingTypes as $type)
                                    <option value="{{ $type->id }}"{{ old('from_packaging_type_id') == $type->id ? ' selected' : '' }}>
                                        {{ $type->name }} {{ $type->unit ? '(' . $type->unit . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('from_packaging_type_id')
                                <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- To Packaging -->
                        <div>
                            <label for="to_packaging_type_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                To Packaging <span class="text-error-500">*</span>
                            </label>
                            <select id="to_packaging_type_id" name="to_packaging_type_id" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select packaging</option>
                                @foreach($packagingTypes as $type)
                                    <option value="{{ $type->id }}"{{ old('to_packaging_type_id') == $type->id ? ' selected' : '' }}>
                                        {{ $type->name }} {{ $type->unit ? '(' . $type->unit . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_packaging_type_id')
                                <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Conversion Factor -->
                        <div>
                            <label for="factor" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Conversion Factor <span class="text-error-500">*</span>
                            </label>
                            <input type="number" id="factor" name="factor" 
                                   min="0.0001" step="0.0001" value="{{ old('factor', 1) }}" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                            @error('factor')
                                <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end border-t border-gray-100 pt-4 dark:border-gray-800">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Save Conversion
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Conversions List -->
        @if($conversions->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Conversion Rules</h3>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $conversions->total() }} {{ Str::plural('rule', $conversions->total()) }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">From</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">To</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Factor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Notes</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($conversions as $conversion)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $conversion->fromType->name }}
                                        </span>
                                        @if($conversion->fromType->unit)
                                            <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                                ({{ $conversion->fromType->unit }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $conversion->toType->name }}
                                        </span>
                                        @if($conversion->toType->unit)
                                            <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                                ({{ $conversion->toType->unit }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($conversion->factor, 4) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $conversion->notes ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form action="{{ route('admin.packaging.conversions.destroy', $conversion) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Delete this conversion rule? This action cannot be undone.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-error-600 shadow-theme-xs hover:bg-error-50 hover:text-error-700 dark:border-gray-700 dark:bg-gray-800 dark:text-error-500 dark:hover:bg-error-500/10">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($conversions, 'links'))
                    <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                        {{ $conversions->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-700">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No conversion rules</h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    No unit conversions defined yet. Add your first conversion rule using the form above.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection