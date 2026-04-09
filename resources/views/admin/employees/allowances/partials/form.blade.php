<div class="space-y-6">
    <!-- Allowance Details Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Allowance Details</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
            <!-- Date -->
            <div class="space-y-2">
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Date <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="{{ old('date', optional($allowance->date)->format('Y-m-d')) }}" 
                           required
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('date')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div class="space-y-2">
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Allowance Type <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    @php
                        $currentType = strtoupper(old('type', $allowance->type ?? 'TA'));
                        $typeColors = [
                            'TA' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                            'DA' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            'BONUS' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'OTHER' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                        ];
                        $typeColor = $typeColors[$currentType] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    @endphp
                    <select id="type" 
                            name="type"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="TA" {{ $currentType === 'TA' ? 'selected' : '' }}>TA (Travel Allowance)</option>
                        <option value="DA" {{ $currentType === 'DA' ? 'selected' : '' }}>DA (Dearness Allowance)</option>
                        <option value="BONUS" {{ $currentType === 'BONUS' ? 'selected' : '' }}>Bonus</option>
                        <option value="OTHER" {{ $currentType === 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div id="type-preview" class="mt-1 hidden">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeColor }}">
                        {{ $currentType === 'TA' ? 'Travel Allowance' : ($currentType === 'DA' ? 'Dearness Allowance' : ($currentType === 'BONUS' ? 'Bonus' : 'Other')) }}
                    </span>
                </div>
                @error('type')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reference -->
            <div class="space-y-2">
                <label for="reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Reference
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 20l4-16 4 4 4-4 4 16H7z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="reference" 
                           name="reference" 
                           value="{{ old('reference', $allowance->reference ?? '') }}"
                           placeholder="e.g. INV-2026-001, TRIP-123"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Optional invoice or approval number</p>
                @error('reference')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Amount -->
            <div class="space-y-2">
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Amount <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">BDT</span>
                    </div>
                    <input type="number" 
                           id="amount" 
                           name="amount" 
                           step="0.01" 
                           min="0"
                           value="{{ old('amount', $allowance->amount ?? '') }}"
                           placeholder="0.00"
                           required
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('amount')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="space-y-2">
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Status
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    @php
                        $currentStatus = old('status', $allowance->status ?? 'submitted');
                        $statusColors = [
                            'submitted' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            'approved' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'paid' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                            'rejected' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                        ];
                        $statusColor = $statusColors[$currentStatus] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    @endphp
                    <select id="status" 
                            name="status"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="submitted" {{ $currentStatus === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ $currentStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div id="status-preview" class="mt-1 hidden">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                        <span class="h-1.5 w-1.5 rounded-full 
                            {{ $currentStatus === 'approved' ? 'bg-success-500' : 
                               ($currentStatus === 'paid' ? 'bg-brand-500' :
                               ($currentStatus === 'rejected' ? 'bg-error-500' : 'bg-orange-500')) }}">
                        </span>
                        {{ ucfirst($currentStatus) }}
                    </span>
                </div>
                @error('status')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description (Full Width) -->
            <div class="md:col-span-2 space-y-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description
                </label>
                <div class="relative group">
                    <div class="absolute left-3 top-3 flex items-start pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </div>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              placeholder="Describe the purpose of this allowance, trip details, or any relevant information..."
                              class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('description', $allowance->description ?? '') }}</textarea>
                </div>
                @error('description')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Attachment / Slip -->
            <div class="md:col-span-2 space-y-2">
                <label for="attachment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Slip / Attachment
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 0119.5 7.372L8.552 18.32a1.5 1.5 0 01-2.122-2.122L16.5 6.75" />
                        </svg>
                    </div>
                    <input type="file" 
                           id="attachment" 
                           name="attachment" 
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-2.5 text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:file:bg-brand-900/30 dark:file:text-brand-400 dark:hover:file:bg-brand-900/50 transition-all">
                </div>
                
                @if(!empty($allowance->attachment_path))
                    <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 0119.5 7.372L8.552 18.32a1.5 1.5 0 01-2.122-2.122L16.5 6.75" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Current attachment</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ basename($allowance->attachment_path) }}</p>
                        </div>
                        <a href="{{ asset('storage/'.$allowance->attachment_path) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-brand-400 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </a>
                    </div>
                @endif
                <p class="text-xs text-gray-500 dark:text-gray-400">Upload receipt, approval document, or supporting evidence (PDF, JPG, PNG)</p>
                @error('attachment')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Type preview
    const typeSelect = document.getElementById('type');
    const typePreview = document.getElementById('type-preview');
    
    function updateTypePreview() {
        if (typeSelect) {
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            const value = selectedOption.value;
            let colorClass = '';
            let displayText = '';
            
            switch(value) {
                case 'TA':
                    colorClass = 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400';
                    displayText = 'Travel Allowance';
                    break;
                case 'DA':
                    colorClass = 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400';
                    displayText = 'Dearness Allowance';
                    break;
                case 'BONUS':
                    colorClass = 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400';
                    displayText = 'Bonus';
                    break;
                default:
                    colorClass = 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400';
                    displayText = 'Other';
            }
            
            typePreview.innerHTML = `
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${colorClass}">
                    ${displayText}
                </span>
            `;
            typePreview.classList.remove('hidden');
        }
    }

    // Status preview
    const statusSelect = document.getElementById('status');
    const statusPreview = document.getElementById('status-preview');
    
    function updateStatusPreview() {
        if (statusSelect) {
            const selectedOption = statusSelect.options[statusSelect.selectedIndex];
            const value = selectedOption.value;
            let colorClass = '';
            let dotColor = '';
            
            switch(value) {
                case 'approved':
                    colorClass = 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400';
                    dotColor = 'bg-success-500';
                    break;
                case 'rejected':
                    colorClass = 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400';
                    dotColor = 'bg-error-500';
                    break;
                default:
                    colorClass = 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400';
                    dotColor = 'bg-orange-500';
            }
            
            statusPreview.innerHTML = `
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ${colorClass}">
                    <span class="h-1.5 w-1.5 rounded-full ${dotColor}"></span>
                    ${selectedOption.text}
                </span>
            `;
            statusPreview.classList.remove('hidden');
        }
    }

    // Amount formatting helper
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('blur', function() {
            if (this.value) {
                // Format to 2 decimal places on blur
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    }

    // Event listeners
    if (typeSelect) {
        typeSelect.addEventListener('change', updateTypePreview);
        // Initial preview
        if (typeSelect.value) updateTypePreview();
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', updateStatusPreview);
        // Initial preview
        if (statusSelect.value) updateStatusPreview();
    }
});
</script>
@endpush
