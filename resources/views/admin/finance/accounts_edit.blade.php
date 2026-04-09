@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Account Icon -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            {{ $account->exists ? 'Edit Account' : 'Add Account' }}
                        </h1>
                        @if($account->exists && $account->code)
                            <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                {{ $account->code }}
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Define GL account code, name, and type
                    </p>
                    @if($account->exists)
                        <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>Created {{ $account->created_at?->diffForHumans() }}</span>
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>Last updated {{ $account->updated_at?->diffForHumans() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.accounts.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Accounts
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-brand-50 to-white px-8 py-6 dark:border-gray-800 dark:from-brand-950/30 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Account Details</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        {{ $account->exists ? 'Update the account information below' : 'Enter the account information below' }}
                    </p>
                </div>
            </div>
            
            <!-- Type Preview Banner -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-brand-100/50 p-4 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm flex-1">
                    <span class="font-semibold">Account Type Preview:</span>
                    <span class="ml-2 inline-flex items-center gap-2" id="account-type-preview">
                        <!-- Dynamically updated via JavaScript -->
                    </span>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ $account->exists ? route('admin.accounts.update', $account) : route('admin.accounts.store') }}" method="POST">
                @csrf
                @if($account->exists)
                    @method('PATCH')
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account Code -->
                    <div class="space-y-2">
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Account Code <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code', $account->code) }}" 
                                   required
                                   placeholder="e.g. 1000, 1100, 2000"
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        @error('code')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Account Name -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Account Name <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $account->name) }}" 
                                   required
                                   placeholder="e.g. Cash, Accounts Receivable, Inventory"
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        @error('name')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Account Type -->
                    <div class="space-y-2">
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Account Type <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            @php
                                $currentType = old('type', $account->type ?? 'asset');
                                $typeColors = [
                                    'asset' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                    'liability' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                    'equity' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                    'income' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                                    'expense' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                                ];
                            @endphp
                            <select id="type" 
                                    name="type"
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                @foreach(['asset','liability','equity','income','expense'] as $type)
                                    <option value="{{ $type }}" {{ $currentType === $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('type')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Account Status
                        </label>
                        <div class="flex items-center h-10">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       class="sr-only peer"
                                       {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ old('is_active', $account->is_active ?? true) ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Inactive accounts are hidden from dropdowns and cannot be used in new transactions</p>
                        @error('is_active')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Account Type Description -->
                <div class="mt-6 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/30">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Account type descriptions:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li><span class="font-medium text-success-600 dark:text-success-400">Asset:</span> Resources owned (cash, inventory, equipment, receivables)</li>
                                <li><span class="font-medium text-orange-600 dark:text-orange-400">Liability:</span> Obligations owed (payables, loans, accrued expenses)</li>
                                <li><span class="font-medium text-brand-600 dark:text-brand-400">Equity:</span> Owner's interest (capital, retained earnings, drawings)</li>
                                <li><span class="font-medium text-blue-light-600 dark:text-blue-light-400">Income:</span> Revenue from sales and services</li>
                                <li><span class="font-medium text-purple-600 dark:text-purple-400">Expense:</span> Costs incurred in operations</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-10 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.accounts.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        @if($account->exists)
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Account
                        @else
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Save Account
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($account->exists)
        <!-- Danger Zone - Delete Account -->
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
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this account</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Once deleted, this account cannot be recovered. This may affect historical ledger entries.
                            Consider deactivating instead of deleting if the account has been used in transactions.
                        </p>
                    </div>
                    <form action="{{ route('admin.accounts.destroy', $account) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete account {{ $account->code }} - {{ $account->name }}? This may affect historical ledger entries.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Account type preview
        const typeSelect = document.getElementById('type');
        const typePreview = document.getElementById('account-type-preview');
        const activeCheckbox = document.querySelector('input[name="is_active"]');
        const activeLabel = document.querySelector('span.ms-3');
        
        const typeColors = {
            'asset': 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
            'liability': 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
            'equity': 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
            'income': 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
            'expense': 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400'
        };
        
        function updateTypePreview() {
            if (typeSelect && typePreview) {
                const selectedType = typeSelect.value;
                const colorClass = typeColors[selectedType] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                
                typePreview.innerHTML = `
                    <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium capitalize ${colorClass}">
                        ${selectedType}
                    </span>
                    <span class="text-xs text-gray-600 dark:text-gray-400 ml-2">
                        ${selectedType === 'asset' ? '💰 Resources owned' : 
                          selectedType === 'liability' ? '📋 Obligations owed' :
                          selectedType === 'equity' ? '🏛️ Owner\'s interest' :
                          selectedType === 'income' ? '📈 Revenue from sales' :
                          selectedType === 'expense' ? '📉 Costs incurred' : ''}
                    </span>
                `;
            }
        }
        
        function updateActiveStatus() {
            if (activeCheckbox && activeLabel) {
                activeLabel.textContent = activeCheckbox.checked ? 'Active' : 'Inactive';
            }
        }
        
        if (typeSelect) {
            typeSelect.addEventListener('change', updateTypePreview);
            updateTypePreview();
        }
        
        if (activeCheckbox) {
            activeCheckbox.addEventListener('change', updateActiveStatus);
            updateActiveStatus();
        }
    });
</script>
@endpush
@endsection