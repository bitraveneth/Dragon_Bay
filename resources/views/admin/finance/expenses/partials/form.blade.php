<div class="form-section space-y-5">
    <div class="section-header border-b border-gray-200 pb-4 dark:border-gray-800">
        <h3 class="text-title-sm font-semibold text-gray-900 dark:text-white">Expense details</h3>
        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Enter the basic information about this expense.</p>
    </div>
    
    <div class="form-grid grid grid-cols-1 gap-5 md:grid-cols-2 lg:gap-6">
        {{-- Date Field --}}
        <div class="flex flex-col gap-1.5">
            <label for="date" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Date <span class="text-error-500">*</span>
            </label>
            <div class="relative">
                <input id="date" 
                       name="date" 
                       type="date" 
                       value="{{ old('date', optional($expense->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" 
                       required
                       class="input-date-icon h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('date')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Category Field --}}
        <div class="flex flex-col gap-1.5">
            <label for="category" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Category <span class="text-error-500">*</span>
            </label>
            @php
                $currentCategory = strtolower(old('category', $expense->category ?? 'general'));
            @endphp
            <div class="relative">
                <select id="category" 
                        name="category" 
                        required
                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-700">
                    <option value="general" {{ $currentCategory === 'general' ? 'selected' : '' }} class="dark:bg-gray-900">General</option>
                    <option value="marketing" {{ $currentCategory === 'marketing' ? 'selected' : '' }} class="dark:bg-gray-900">Marketing</option>
                    <option value="utilities" {{ $currentCategory === 'utilities' ? 'selected' : '' }} class="dark:bg-gray-900">Utilities</option>
                    <option value="salary" {{ $currentCategory === 'salary' ? 'selected' : '' }} class="dark:bg-gray-900">Salary</option>
                    <option value="travel" {{ $currentCategory === 'travel' ? 'selected' : '' }} class="dark:bg-gray-900">Travel</option>
                    <option value="other" {{ $currentCategory === 'other' ? 'selected' : '' }} class="dark:bg-gray-900">Other</option>
                </select>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" 
                     class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            @error('category')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Amount Field --}}
        <div class="flex flex-col gap-1.5">
            <label for="amount" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Amount <span class="text-error-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                <input id="amount" 
                       name="amount" 
                       type="number" 
                       step="0.01" 
                       min="0"
                       value="{{ old('amount', $expense->amount ?? '') }}" 
                       required
                       placeholder="0.00"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-7 pr-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('amount')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Reference Field --}}
        <div class="flex flex-col gap-1.5">
            <label for="reference" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Reference
                <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
            </label>
            <div class="relative">
                <input id="reference" 
                       name="reference" 
                       value="{{ old('reference', $expense->reference ?? '') }}"
                       placeholder="e.g., INV-001"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('reference')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description Field - Full Width --}}
        <div class="full-width md:col-span-2">
            <div class="flex flex-col gap-1.5">
                <label for="description" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    Description
                    <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          placeholder="Enter a detailed description of this expense..."
                          class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">{{ old('description', $expense->description ?? '') }}</textarea>
                @error('description')
                    <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Status Field --}}
        <div class="flex flex-col gap-1.5">
            <label for="status" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Status
            </label>
            @php
                $currentStatus = strtolower(old('status', $expense->status ?? 'recorded'));
            @endphp
            <div class="relative">
                <select id="status" 
                        name="status"
                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-700">
                    <option value="recorded" {{ $currentStatus === 'recorded' ? 'selected' : '' }} class="dark:bg-gray-900">Recorded</option>
                    <option value="reviewed" {{ $currentStatus === 'reviewed' ? 'selected' : '' }} class="dark:bg-gray-900">Reviewed</option>
                </select>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" 
                     class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            @error('status')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Form Hint --}}
    <div class="mt-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
        <p class="text-theme-xs text-gray-600 dark:text-gray-400">
            <span class="font-medium text-error-500">*</span> Required fields
        </p>
    </div>
</div>