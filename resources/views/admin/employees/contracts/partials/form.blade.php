<div class="space-y-10">
    <!-- Basic Details Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Details</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
            <!-- Contract Reference -->
            <div class="space-y-2">
                <label for="reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Contract Reference <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="reference" 
                           name="reference" 
                           value="{{ old('reference', $contract->reference ?? '') }}" 
                           required
                           placeholder="e.g. CT-2026-001, EMP-001-2026"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('reference')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="space-y-2">
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Contract Status <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    @php
                        $currentStatus = old('status', $contract->status ?? 'active');
                    @endphp
                    <select id="status" 
                            name="status"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_hold" {{ $currentStatus === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="ended" {{ $currentStatus === 'ended' ? 'selected' : '' }}>Ended</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div id="status-preview" class="mt-1 hidden">
                    @php
                        $statusColors = [
                            'active' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                            'on_hold' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            'ended' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                        ];
                        $statusColor = $statusColors[$currentStatus] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                        <span class="h-1.5 w-1.5 rounded-full 
                            {{ $currentStatus === 'active' ? 'bg-success-500' : 
                               ($currentStatus === 'on_hold' ? 'bg-orange-500' : 'bg-gray-500') }}">
                        </span>
                        {{ ucfirst(str_replace('_', ' ', $currentStatus)) }}
                    </span>
                </div>
                @error('status')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Period & Schedule Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Period & Schedule</h3>
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
                    @php
                        $startDefault = $contract->start_date ? $contract->start_date->format('Y-m-d') : now()->format('Y-m-d');
                    @endphp
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date', $startDefault) }}" 
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
                    End Date
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
                           value="{{ old('end_date', optional($contract->end_date)->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank for open-ended contracts</p>
                @error('end_date')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date Range Preview -->
            <div class="md:col-span-2 -mt-2">
                <div id="contract-date-preview" class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <!-- Dynamically updated via JavaScript -->
                </div>
            </div>

            <!-- Working Schedule (Full Width) -->
            <div class="md:col-span-2 space-y-2">
                <label for="working_schedule" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Working Schedule
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="working_schedule" 
                           name="working_schedule" 
                           list="working-schedules" 
                           value="{{ old('working_schedule', $contract->working_schedule ?? '') }}"
                           placeholder="e.g. Mon–Sat, 9:00–17:00"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @isset($workingSchedules)
                    <datalist id="working-schedules">
                        @foreach($workingSchedules as $schedule)
                            <option value="{{ $schedule }}"></option>
                        @endforeach
                    </datalist>
                @endisset
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Select an existing schedule or type a new one
                </p>
                @error('working_schedule')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Salary & Allowances Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Salary & Allowances</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pl-11">
            <!-- Salary Amount -->
            <div class="space-y-2">
                <label for="salary_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Base Salary
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">BDT</span>
                    </div>
                    <input type="number" 
                           id="salary_amount" 
                           name="salary_amount" 
                           step="0.01" 
                           min="0"
                           value="{{ old('salary_amount', $contract->salary_amount ?? '') }}"
                           placeholder="0.00"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('salary_amount')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Travel Allowance (TA) -->
            <div class="space-y-2">
                <label for="travel_allowance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Travel Allowance (TA)
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">BDT</span>
                    </div>
                    <input type="number" 
                           id="travel_allowance" 
                           name="travel_allowance" 
                           step="0.01" 
                           min="0"
                           value="{{ old('travel_allowance', $contract->travel_allowance ?? '') }}"
                           placeholder="0.00"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('travel_allowance')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dearness Allowance (DA) -->
            <div class="space-y-2">
                <label for="dearness_allowance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Dearness Allowance (DA)
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">BDT</span>
                    </div>
                    <input type="number" 
                           id="dearness_allowance" 
                           name="dearness_allowance" 
                           step="0.01" 
                           min="0"
                           value="{{ old('dearness_allowance', $contract->dearness_allowance ?? '') }}"
                           placeholder="0.00"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('dearness_allowance')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bonus -->
            <div class="space-y-2">
                <label for="bonus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Annual Bonus
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">BDT</span>
                    </div>
                    <input type="number" 
                           id="bonus" 
                           name="bonus" 
                           step="0.01" 
                           min="0"
                           value="{{ old('bonus', $contract->bonus ?? '') }}"
                           placeholder="0.00"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('bonus')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Total Compensation Preview -->
        <div id="total-compensation" class="pl-11 mt-2 hidden">
            <div class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-4 py-2 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                </svg>
                <span>Total Monthly: <span id="total-monthly" class="font-semibold">BDT 0</span></span>
                <span class="mx-1">·</span>
                <span>Total Annual: <span id="total-annual" class="font-semibold">BDT 0</span></span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contract Date Preview
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const datePreview = document.getElementById('contract-date-preview');
    
    function updateDatePreview() {
        if (startDateInput?.value) {
            const start = new Date(startDateInput.value);
            const startFormatted = start.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
            
            if (endDateInput?.value) {
                const end = new Date(endDateInput.value);
                const endFormatted = end.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
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
                datePreview.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        ${startFormatted} → Open-ended
                        <span class="ml-1 font-semibold">(No end date)</span>
                    </span>
                `;
            }
        } else {
            datePreview.innerHTML = '';
        }
    }

    // Status Preview
    const statusSelect = document.getElementById('status');
    const statusPreview = document.getElementById('status-preview');
    
    function updateStatusPreview() {
        if (statusSelect) {
            const selectedOption = statusSelect.options[statusSelect.selectedIndex];
            const value = selectedOption.value;
            let colorClass = '';
            let dotColor = '';
            
            switch(value) {
                case 'active':
                    colorClass = 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400';
                    dotColor = 'bg-success-500';
                    break;
                case 'on_hold':
                    colorClass = 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400';
                    dotColor = 'bg-orange-500';
                    break;
                default:
                    colorClass = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                    dotColor = 'bg-gray-500';
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

    // Total Compensation Calculator
    const salaryInput = document.getElementById('salary_amount');
    const taInput = document.getElementById('travel_allowance');
    const daInput = document.getElementById('dearness_allowance');
    const bonusInput = document.getElementById('bonus');
    const totalCompensation = document.getElementById('total-compensation');
    const totalMonthlySpan = document.getElementById('total-monthly');
    const totalAnnualSpan = document.getElementById('total-annual');
    
    function updateCompensation() {
        const salary = parseFloat(salaryInput?.value) || 0;
        const ta = parseFloat(taInput?.value) || 0;
        const da = parseFloat(daInput?.value) || 0;
        const bonus = parseFloat(bonusInput?.value) || 0;
        
        const monthlyTotal = salary + ta + da;
        const annualTotal = (monthlyTotal * 12) + bonus;
        
        if (monthlyTotal > 0 || annualTotal > 0) {
            totalMonthlySpan.textContent = `BDT ${monthlyTotal.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
            totalAnnualSpan.textContent = `BDT ${annualTotal.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}`;
            totalCompensation.classList.remove('hidden');
        } else {
            totalCompensation.classList.add('hidden');
        }
    }

    // Event Listeners
    if (startDateInput) startDateInput.addEventListener('change', updateDatePreview);
    if (endDateInput) endDateInput.addEventListener('change', updateDatePreview);
    
    if (statusSelect) {
        statusSelect.addEventListener('change', updateStatusPreview);
        updateStatusPreview();
    }
    
    if (salaryInput) salaryInput.addEventListener('input', updateCompensation);
    if (taInput) taInput.addEventListener('input', updateCompensation);
    if (daInput) daInput.addEventListener('input', updateCompensation);
    if (bonusInput) bonusInput.addEventListener('input', updateCompensation);
    
    // Initial updates
    if (startDateInput?.value) updateDatePreview();
    updateCompensation();
});
</script>
@endpush