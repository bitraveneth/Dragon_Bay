{{-- Employee & Period Section --}}
<div class="form-section space-y-5">
    <div class="section-header border-b border-gray-200 pb-4 dark:border-gray-800">
        <h3 class="text-title-sm font-semibold text-gray-900 dark:text-white">Employee & period</h3>
        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Select employee and define the payment period.</p>
    </div>
    
    <div class="form-grid grid grid-cols-1 gap-5 md:grid-cols-2 lg:gap-6">
        {{-- Employee Select --}}
        <div class="flex flex-col gap-1.5">
            <label for="employee_id" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Employee <span class="text-error-500">*</span>
            </label>
            <div class="relative">
                <select id="employee_id" 
                        name="employee_id" 
                        required
                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-700">
                    <option value="" class="dark:bg-gray-900">Select employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ (string)old('employee_id', $distribution->employee_id ?? '') === (string)$employee->id ? 'selected' : '' }} class="dark:bg-gray-900">
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" 
                     class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            @error('employee_id')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Period Start --}}
        <div class="flex flex-col gap-1.5">
            <label for="period_start" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Period start <span class="text-error-500">*</span>
            </label>
            <div class="relative">
                <input id="period_start" 
                       name="period_start" 
                       type="date"
                       value="{{ old('period_start', optional($distribution->period_start)->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d')) }}"
                       required
                       class="input-date-icon h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('period_start')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Period End --}}
        <div class="flex flex-col gap-1.5">
            <label for="period_end" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Period end <span class="text-error-500">*</span>
            </label>
            <div class="relative">
                <input id="period_end" 
                       name="period_end" 
                       type="date"
                       value="{{ old('period_end', optional($distribution->period_end)->format('Y-m-d') ?? now()->endOfMonth()->format('Y-m-d')) }}"
                       required
                       class="input-date-icon h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('period_end')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- Salary Breakdown Section --}}
<div class="form-section mt-8 space-y-5">
    <div class="section-header border-b border-gray-200 pb-4 dark:border-gray-800">
        <h3 class="text-title-sm font-semibold text-gray-900 dark:text-white">Salary breakdown</h3>
        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Enter the detailed compensation components.</p>
    </div>
    
    <div class="form-grid grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
        {{-- Base Salary --}}
        <div class="flex flex-col gap-1.5">
            <label for="base_salary" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Base salary <span class="text-error-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                <input id="base_salary" 
                       name="base_salary" 
                       type="number" 
                       step="0.01" 
                       min="0"
                       value="{{ old('base_salary', $distribution->base_salary ?? 0) }}" 
                       required
                       placeholder="0.00"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-7 pr-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('base_salary')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Bonus --}}
        <div class="flex flex-col gap-1.5">
            <label for="bonus" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Bonus
                <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                <input id="bonus" 
                       name="bonus" 
                       type="number" 
                       step="0.01" 
                       min="0"
                       value="{{ old('bonus', $distribution->bonus ?? 0) }}"
                       placeholder="0.00"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-7 pr-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('bonus')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- TA Allowances --}}
        <div class="flex flex-col gap-1.5">
            <label for="ta_allowances" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                TA allowances
                <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                <input id="ta_allowances" 
                       name="ta_allowances" 
                       type="number" 
                       step="0.01" 
                       min="0"
                       value="{{ old('ta_allowances', $distribution->ta_allowances ?? 0) }}"
                       placeholder="0.00"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-7 pr-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('ta_allowances')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- DA Allowances --}}
        <div class="flex flex-col gap-1.5">
            <label for="da_allowances" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                DA allowances
                <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                <input id="da_allowances" 
                       name="da_allowances" 
                       type="number" 
                       step="0.01" 
                       min="0"
                       value="{{ old('da_allowances', $distribution->da_allowances ?? 0) }}"
                       placeholder="0.00"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-7 pr-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('da_allowances')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Commission --}}
        <div class="flex flex-col gap-1.5">
            <label for="commission" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Commission
                <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                <input id="commission" 
                       name="commission" 
                       type="number" 
                       step="0.01" 
                       min="0"
                       value="{{ old('commission', $distribution->commission ?? 0) }}"
                       placeholder="0.00"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pl-7 pr-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>
            @error('commission')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Total Preview (Read-only) --}}
        <div class="flex flex-col gap-1.5">
            <label class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Total compensation
            </label>
            <div class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-theme-sm font-semibold text-brand-700 dark:border-gray-700 dark:bg-gray-800/50 dark:text-brand-400 flex items-center">
                ${{ number_format(
                    (old('base_salary', $distribution->base_salary ?? 0)) +
                    (old('bonus', $distribution->bonus ?? 0)) +
                    (old('ta_allowances', $distribution->ta_allowances ?? 0)) +
                    (old('da_allowances', $distribution->da_allowances ?? 0)) +
                    (old('commission', $distribution->commission ?? 0)), 2
                ) }}
            </div>
        </div>
    </div>
</div>

{{-- Payment & Documents Section --}}
<div class="form-section mt-8 space-y-5">
    <div class="section-header border-b border-gray-200 pb-4 dark:border-gray-800">
        <h3 class="text-title-sm font-semibold text-gray-900 dark:text-white">Payment & documents</h3>
        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Payment method and supporting documentation.</p>
    </div>
    
    <div class="form-grid grid grid-cols-1 gap-5 md:grid-cols-2 lg:gap-6">
        {{-- Payment Method --}}
        <div class="flex flex-col gap-1.5">
            <label for="payment_method" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Payment method
                <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
            </label>
            @php
                $currentMethod = old('payment_method', $distribution->payment_method ?? 'bank');
            @endphp
            <div class="relative">
                <select id="payment_method" 
                        name="payment_method"
                        class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-700">
                    <option value="" class="dark:bg-gray-900">Select payment method</option>
                    <option value="bank" {{ $currentMethod === 'bank' ? 'selected' : '' }} class="dark:bg-gray-900">Bank Transfer</option>
                    <option value="cash" {{ $currentMethod === 'cash' ? 'selected' : '' }} class="dark:bg-gray-900">Cash</option>
                    <option value="cheque" {{ $currentMethod === 'cheque' ? 'selected' : '' }} class="dark:bg-gray-900">Cheque</option>
                    <option value="online" {{ $currentMethod === 'online' ? 'selected' : '' }} class="dark:bg-gray-900">Online Payment</option>
                </select>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" 
                     class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            @error('payment_method')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Document Upload --}}
        <div class="flex flex-col gap-1.5">
            <label for="document" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                Document
                <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
            </label>
            <div class="relative">
                <input id="document" 
                       name="document" 
                       type="file"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-theme-xs file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:file:bg-brand-500/20 dark:file:text-brand-400 dark:hover:file:bg-brand-500/30">
            </div>
            @if(!empty($distribution->document_path))
                <div class="preview mt-2 flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-500 dark:text-gray-400">
                        <path d="M4.16667 17.5H15.8333C16.7538 17.5 17.5 16.7538 17.5 15.8333V4.16667C17.5 3.24619 16.7538 2.5 15.8333 2.5H4.16667C3.24619 2.5 2.5 3.24619 2.5 4.16667V15.8333C2.5 16.7538 3.24619 17.5 4.16667 17.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M7.5 7.5H12.5M7.5 10.8333H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span class="text-theme-xs text-gray-600 dark:text-gray-400">Current document:</span>
                    <a href="{{ asset('storage/'.$distribution->document_path) }}" 
                       target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-theme-xs font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 13.3333V4.16667M10 13.3333L7.5 10.8333M10 13.3333L12.5 10.8333M17.5 13.3333V15.8333C17.5 16.7538 16.7538 17.5 15.8333 17.5H4.16667C3.24619 17.5 2.5 16.7538 2.5 15.8333V13.3333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        View
                    </a>
                </div>
            @endif
            @error('document')
                <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remarks - Full Width --}}
        <div class="full-width md:col-span-2">
            <div class="flex flex-col gap-1.5">
                <label for="remarks" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    Remarks
                    <span class="text-theme-xs font-normal text-gray-500 dark:text-gray-400 ml-1">(optional)</span>
                </label>
                <textarea id="remarks" 
                          name="remarks" 
                          rows="3"
                          placeholder="Add any additional notes or comments..."
                          class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">{{ old('remarks', $distribution->remarks ?? '') }}</textarea>
                @error('remarks')
                    <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Form Hint --}}
    <div class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
        <p class="text-theme-xs text-gray-600 dark:text-gray-400">
            <span class="font-medium text-error-500">*</span> Required fields
        </p>
    </div>
</div>

@push('scripts')
<script>
    // Auto-calculate total compensation
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = ['base_salary', 'bonus', 'ta_allowances', 'da_allowances', 'commission'];
        const totalDisplay = document.querySelector('.total-compensation');
        
        function calculateTotal() {
            let total = 0;
            inputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    total += parseFloat(input.value) || 0;
                }
            });
            if (totalDisplay) {
                totalDisplay.textContent = `$${total.toFixed(2)}`;
            }
        }
        
        inputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', calculateTotal);
            }
        });
        
        // Initial calculation
        calculateTotal();
    });
</script>
@endpush