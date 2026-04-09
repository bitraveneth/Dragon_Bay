<div class="space-y-6">
    <!-- Leave Details Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Leave Details</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
            <!-- Start Date -->
            <div class="space-y-2">
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Start Date <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date', optional($leave->start_date)->format('Y-m-d')) }}" 
                           required
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('start_date')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- End Date -->
            <div class="space-y-2">
                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    End Date <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ old('end_date', optional($leave->end_date)->format('Y-m-d')) }}" 
                           required
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('end_date')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date Range Preview (calculated) -->
            <div class="md:col-span-2 -mt-2">
                <div id="date-range-preview" class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <!-- Dynamically updated via JavaScript -->
                </div>
            </div>

            <!-- Leave Type -->
            <div class="space-y-2">
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Leave Type <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    @php
                        $currentType = strtolower(old('type', $leave->type ?? 'annual'));
                        $typeColors = [
                            'annual' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                            'sick' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            'casual' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'unpaid' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                            'other' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                        ];
                        $typeColor = $typeColors[$currentType] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    @endphp
                    <select id="type" 
                            name="type"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="annual" {{ $currentType === 'annual' ? 'selected' : '' }}>Annual Leave</option>
                        <option value="sick" {{ $currentType === 'sick' ? 'selected' : '' }}>Sick Leave</option>
                        <option value="casual" {{ $currentType === 'casual' ? 'selected' : '' }}>Casual Leave</option>
                        <option value="unpaid" {{ $currentType === 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                        <option value="other" {{ $currentType === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div id="type-preview" class="mt-1 hidden">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeColor }}">
                        {{ ucfirst($currentType) }} Leave
                    </span>
                </div>
                @error('type')
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
                        $currentStatus = strtolower(old('status', $leave->status ?? 'pending'));
                        $statusColors = [
                            'pending' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            'approved' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'rejected' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                        ];
                        $statusColor = $statusColors[$currentStatus] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    @endphp
                    <select id="status" 
                            name="status"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Approved</option>
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
                               ($currentStatus === 'rejected' ? 'bg-error-500' : 'bg-orange-500') }}">
                        </span>
                        {{ ucfirst($currentStatus) }}
                    </span>
                </div>
                @error('status')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason (Full Width) -->
            <div class="md:col-span-2 space-y-2">
                <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Reason
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="reason" 
                           name="reason" 
                           value="{{ old('reason', $leave->reason ?? '') }}"
                           placeholder="e.g. Family vacation, Medical appointment, Personal matters"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Optional: Provide a brief explanation for the leave request</p>
                @error('reason')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const datePreview = document.getElementById('date-range-preview');
    const typeSelect = document.getElementById('type');
    const typePreview = document.getElementById('type-preview');
    const statusSelect = document.getElementById('status');
    const statusPreview = document.getElementById('status-preview');

    function updateDatePreview() {
        if (startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            const startFormatted = start.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
            const endFormatted = end.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
            
            datePreview.innerHTML = `
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    ${startFormatted} → ${endFormatted}
                    <span class="ml-1 font-semibold">(${diffDays} ${diffDays === 1 ? 'day' : 'days'})</span>
                </span>
            `;
        } else {
            datePreview.innerHTML = '';
        }
    }

    function updateTypePreview() {
        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
        const value = selectedOption.value;
        let colorClass = '';
        
        switch(value) {
            case 'annual':
                colorClass = 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400';
                break;
            case 'sick':
                colorClass = 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400';
                break;
            case 'casual':
                colorClass = 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400';
                break;
            case 'unpaid':
                colorClass = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                break;
            default:
                colorClass = 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400';
        }
        
        typePreview.innerHTML = `
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${colorClass}">
                ${selectedOption.text}
            </span>
        `;
        typePreview.classList.remove('hidden');
    }

    function updateStatusPreview() {
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

    // Event listeners
    if (startDateInput) startDateInput.addEventListener('change', updateDatePreview);
    if (endDateInput) endDateInput.addEventListener('change', updateDatePreview);
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
    
    // Initial date preview if both dates exist
    if (startDateInput?.value && endDateInput?.value) {
        updateDatePreview();
    }
});
</script>
@endpush