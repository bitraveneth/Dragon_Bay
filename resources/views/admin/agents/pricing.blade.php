@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Commercial Terms
                </h1>
                <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                    {{ $agent->code ?? 'AGENT' }}
                </span>
            </div>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-lg font-medium text-gray-900 dark:text-white">{{ $agent->name }}</span>
                @if($agent->area || $agent->zone)
                    <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $agent->area ?? '—' }}@if($agent->zone) · Zone {{ $agent->zone }} @endif
                    </span>
                @endif
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Maintain per-SKU price lists and commission rules for this agent.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agents.show', $agent) }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Agent Profile
            </a>
            <a href="{{ route('admin.agents.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                All Agents
            </a>
        </div>
    </div>

    <!-- Status Message -->

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Commercial Terms Configuration</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Set up agent-specific pricing and commission structures</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.agents.pricing.update', $agent) }}" method="POST" class="p-6">
            @csrf
            @method('PATCH')

            <!-- Price List Section -->
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Price List</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Override catalog prices per SKU. Leave blank to use default product price.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($products as $product)
                        @php
                            $entry = $priceLists[$product->id] ?? null;
                        @endphp
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                            <label for="price_{{ $product->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span class="font-mono text-xs uppercase text-gray-500 dark:text-gray-400">{{ $product->sku }}</span>
                                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $product->name }}</span>
                            </label>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
                                    <input type="number"
                                           id="price_{{ $product->id }}"
                                           name="prices[{{ $product->id }}]"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('prices.' . $product->id, optional($entry)->price) }}"
                                           placeholder="Use base price"
                                           class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Base: BDT {{ number_format($product->base_price ?? 0, 2) }}
                            </p>
                            @error('prices.' . $product->id)
                                <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Commission Rules Section -->
            <div class="mt-8 space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Commission Rules</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Start simple: usually one row is enough (e.g. 2% on all regular orders). Leave other rows empty.
                            </p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            Priority order
                        </span>
                    </div>
                </div>

                @php
                    $rows = old('commissions', $commissions->toArray());
                    if (count($rows) < 5) {
                        $rows = array_pad($rows, 5, []);
                    }
                @endphp

                <div class="space-y-4">
                    @foreach($rows as $index => $row)
                        @php
                            $isEmptyRule = empty(array_filter($row ?? []));
                            $hidden = $index > 0 && $isEmptyRule;
                        @endphp
                        <div id="commission-rule-{{ $index }}"
                             class="commission-rule rounded-xl border {{ $hidden ? 'border-gray-200 dark:border-gray-700' : 'border-brand-200 dark:border-brand-800' }} bg-white p-5 shadow-theme-xs dark:bg-gray-900 {{ $hidden ? 'hidden' : '' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full {{ !$isEmptyRule ? 'bg-brand-100 dark:bg-brand-500/20' : 'bg-gray-100 dark:bg-gray-800' }}">
                                        <span class="text-xs font-semibold {{ !$isEmptyRule ? 'text-brand-700 dark:text-brand-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <h4 class="text-base font-medium text-gray-900 dark:text-white">
                                        Rule {{ $index + 1 }}
                                        @if(!$isEmptyRule)
                                            <span class="ml-2 inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                                Active
                                            </span>
                                        @endif
                                    </h4>
                                </div>
                                @if($index > 0)
                                    <button type="button"
                                            onclick="document.getElementById('commission-rule-{{ $index }}').remove()"
                                            class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Remove
                                    </button>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                                <!-- SKU Filter -->
                                <div>
                                    <label for="commissions_{{ $index }}_sku" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Applies to SKU
                                    </label>
                                    <input type="text"
                                           id="commissions_{{ $index }}_sku"
                                           name="commissions[{{ $index }}][sku]"
                                           value="{{ $row['sku'] ?? '' }}"
                                           placeholder="Blank = all SKUs"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Blank = applies to all SKUs.</p>
                                </div>

                                <!-- Calculation Type -->
                                <div>
                                    <label for="commissions_{{ $index }}_type" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        How to calculate
                                    </label>
                                    <select id="commissions_{{ $index }}_type"
                                            name="commissions[{{ $index }}][type]"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">None</option>
                                        @foreach(['percentage' => '% of line', 'fixed' => 'Fixed per order'] as $value => $label)
                                            <option value="{{ $value }}" {{ ($row['type'] ?? null) === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">"% of line" = percent, "Fixed" = flat BDT.</p>
                                </div>

                                <!-- Value -->
                                <div>
                                    <label for="commissions_{{ $index }}_value" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Amount
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">
                                            {{ (isset($row['type']) && $row['type'] === 'fixed') ? 'BDT' : '%' }}
                                        </span>
                                        <input type="number"
                                               id="commissions_{{ $index }}_value"
                                               name="commissions[{{ $index }}][value]"
                                               step="0.01"
                                               min="0"
                                               value="{{ $row['value'] ?? '' }}"
                                               placeholder="0.00"
                                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">%: enter percent (2 = 2%). Fixed: enter BDT.</p>
                                </div>

                                <div>
                                    <label for="commissions_{{ $index }}_threshold_min" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Tier Min Sales
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">BDT</span>
                                        <input type="number"
                                               id="commissions_{{ $index }}_threshold_min"
                                               name="commissions[{{ $index }}][threshold_min]"
                                               step="0.01"
                                               min="0"
                                               value="{{ $row['threshold_min'] ?? '' }}"
                                               placeholder="No minimum"
                                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Blank = no lower threshold.</p>
                                </div>

                                <div>
                                    <label for="commissions_{{ $index }}_threshold_max" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Tier Max Sales
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">BDT</span>
                                        <input type="number"
                                               id="commissions_{{ $index }}_threshold_max"
                                               name="commissions[{{ $index }}][threshold_max]"
                                               step="0.01"
                                               min="0"
                                               value="{{ $row['threshold_max'] ?? '' }}"
                                               placeholder="No maximum"
                                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use min/max to define monthly or line-value tiers.</p>
                                </div>

                                <!-- Order Type Filter -->
                                <div>
                                    <label for="commissions_{{ $index }}_order_type" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Order type filter
                                    </label>
                                    <select id="commissions_{{ $index }}_order_type"
                                            name="commissions[{{ $index }}][order_type]"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">All types</option>
                                        @foreach(['regular', 'bulk', 'sample', 'return'] as $type)
                                            <option value="{{ $type }}" {{ ($row['order_type'] ?? null) === $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Blank = all order types.</p>
                                </div>

                                <!-- Frequency -->
                                <div class="sm:col-span-2 lg:col-span-4">
                                    <label for="commissions_{{ $index }}_frequency" class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                        When to apply
                                    </label>
                                    <div class="flex flex-wrap gap-4">
                                        @foreach(['per_order' => 'Per order', 'monthly' => 'Monthly summary'] as $value => $label)
                                            <label class="inline-flex items-center">
                                                <input type="radio"
                                                       name="commissions[{{ $index }}][frequency]"
                                                       value="{{ $value }}"
                                                       {{ ($row['frequency'] ?? 'per_order') === $value ? 'checked' : '' }}
                                                       class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Monthly rules now pay from realized monthly invoiced sales. Use non-overlapping min/max tiers.</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Add Rule Button -->
                <div class="flex justify-start">
                    <button type="button"
                            id="add-commission-rule"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Commission Rule
                    </button>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.agents.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Commercial Terms
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addBtn = document.getElementById('add-commission-rule');
    if (!addBtn) return;

    addBtn.addEventListener('click', function () {
        const hiddenRule = document.querySelector('.commission-rule.hidden');
        if (hiddenRule) {
            hiddenRule.classList.remove('hidden');
            hiddenRule.classList.add('border-brand-200', 'dark:border-brand-800');
            
            // Scroll to the newly visible rule
            hiddenRule.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            // Optional: Show toast message if no more rules available
            alert('All commission rules are already visible.');
        }
    });

    // Update currency prefix based on commission type
    document.querySelectorAll('[id$="_type"]').forEach(select => {
        select.addEventListener('change', function() {
            const index = this.id.split('_')[1];
            const valueInput = document.getElementById(`commissions_${index}_value`);
            const prefix = valueInput?.parentElement?.querySelector('span');
            
            if (prefix) {
                prefix.textContent = this.value === 'fixed' ? 'BDT' : '%';
            }
        });
    });
});
</script>
@endpush
@endsection
