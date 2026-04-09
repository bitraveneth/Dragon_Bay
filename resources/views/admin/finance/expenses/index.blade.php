@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <header class="panel-header mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-title-sm font-semibold text-gray-900 dark:text-white">Expenses</h1>
                <p class="text-theme-sm text-gray-500 dark:text-gray-300">Daily, weekly, or monthly expenses by category.</p>
            </div>
            <div class="button-group">
                <a href="{{ route('admin.expenses.create') }}" 
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:bg-brand-500 dark:hover:bg-brand-600">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Add expense
                </a>
            </div>
        </header>

        <form method="GET" action="{{ route('admin.expenses.index') }}" class="mb-6 flex flex-wrap items-end gap-4">
            <label class="flex flex-col gap-1.5">
                <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-200">From</span>
                <input type="date" 
                       name="from" 
                       value="{{ request('from', $from->format('Y-m-d')) }}"
                       class="input-date-icon h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </label>
            <label class="flex flex-col gap-1.5">
                <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-200">To</span>
                <input type="date" 
                       name="to" 
                       value="{{ request('to', $to->format('Y-m-d')) }}"
                       class="input-date-icon h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </label>
            <label class="flex flex-col gap-1.5">
                <span class="text-theme-sm font-medium text-gray-700 dark:text-gray-200">Category</span>
                <select name="category" 
                        class="h-11 min-w-[160px] rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-700">
                    <option value="" class="dark:bg-gray-900">All</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }} class="dark:bg-gray-900">
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" 
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.5 17.5L13.3333 13.3333M15 8.33333C15 12.0152 12.0152 15 8.33333 15C4.65144 15 1.66667 12.0152 1.66667 8.33333C1.66667 4.65144 4.65144 1.66667 8.33333 1.66667C12.0152 1.66667 15 4.65144 15 8.33333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Filter
            </button>
        </form>

        <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
            <p class="text-theme-sm text-gray-600 dark:text-gray-300">
                Total in period: 
                <strong class="text-title-sm font-semibold text-brand-500 dark:text-brand-400">
                    {{ number_format($total, 2) }} {{ config('app.currency', 'BDT') }}
                </strong>
            </p>
        </div>

        @if($expenses->isNotEmpty())
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="data-table w-full min-w-[900px] border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Date</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Category</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Description</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Amount</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Reference</th>
                            <th class="px-5 py-4 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Status</th>
                            <th class="px-5 py-4 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($expenses as $expense)
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-4 text-theme-sm text-gray-700">
                                    {{ $expense->date?->format('Y-m-d') }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-theme-xs font-medium"
                                          @style([
                                              'background: ' . match($expense->category) {
                                                  'food' => '#ecfdf3',
                                                  'transport' => '#fef7e7',
                                                  'utilities' => '#e9f3ff',
                                                  'entertainment' => '#fce9f2',
                                                  default => '#f2f4f7'
                                              },
                                              'color: ' . match($expense->category) {
                                                  'food' => '#067647',
                                                  'transport' => '#b54708',
                                                  'utilities' => '#175cd3',
                                                  'entertainment' => '#c11574',
                                                  default => '#344054'
                                              }
                                          ])>
                                        {{ ucfirst($expense->category) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700">
                                    {{ $expense->description ?: '—' }}
                                </td>
                                <td class="px-5 py-4 text-theme-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($expense->amount, 2) }}
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-700">
                                    {{ $expense->reference ?: '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    @php
                                        $statusColors = [
                                            'reviewed' => ['bg' => 'success-50', 'text' => 'success-700', 'dark' => ['bg' => 'success-500/20', 'text' => 'success-400']],
                                            'recorded' => ['bg' => 'warning-50', 'text' => 'warning-700', 'dark' => ['bg' => 'warning-500/20', 'text' => 'warning-400']],
                                        ];
                                        $status = $expense->status ?? 'recorded';
                                        $colors = $statusColors[$status] ?? $statusColors['recorded'];
                                    @endphp
                                    <span class="inline-flex rounded-full bg-{{ $colors['bg'] }} px-2.5 py-1 text-theme-xs font-medium text-{{ $colors['text'] }} dark:bg-{{ $colors['dark']['bg'] }} dark:text-{{ $colors['dark']['text'] }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" 
                                           class="edit-button inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14.1667 2.5L17.5 5.83333M2.5 14.1667L11.6667 5L15 8.33333L5.83333 17.5H2.5V14.1667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.expenses.destroy', $expense) }}" 
                                              method="POST" 
                                              class="inline-flex"
                                              onsubmit="return confirm('Delete this expense? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-error-700 shadow-theme-xs transition hover:bg-error-50 hover:text-error-800 dark:border-gray-700 dark:bg-gray-800 dark:text-error-400 dark:hover:bg-error-500/20">
                                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M2.5 5H4.16667H17.5M15.8333 5V16.6667C15.8333 17.5 15 18.3333 14.1667 18.3333H5.83333C5 18.3333 4.16667 17.5 4.16667 16.6667V5M6.66667 5V3.33333C6.66667 2.5 7.5 1.66667 8.33333 1.66667H11.6667C12.5 1.66667 13.3333 2.5 13.3333 3.33333V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
            
            <div class="mt-6">
                {{ $expenses->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 py-12 dark:border-gray-800">
                <div class="mb-4 rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#98A2B3" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <p class="text-theme-lg font-medium text-gray-700 dark:text-gray-300">No expenses found</p>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or add a new expense.</p>
                <a href="{{ route('admin.expenses.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white hover:bg-brand-600">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 4.16667V15.8333M4.16667 10H15.8333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Add your first expense
                </a>
            </div>
        @endif
    </section>
</div>
@endsection
