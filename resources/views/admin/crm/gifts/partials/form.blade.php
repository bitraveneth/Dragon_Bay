<div class="space-y-6">
    <!-- Gift Details Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-pink-100 to-rose-100 dark:from-pink-900/30 dark:to-rose-900/30">
                <svg class="h-4 w-4 text-pink-700 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875zm0 0V3m0 9.75v-1.5M3.375 7.5h17.25" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gift Details</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pl-11">
            <!-- Date -->
            <div class="space-y-2">
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Date <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="{{ old('date', optional($gift->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" 
                           required
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('date')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Agent -->
            <div class="space-y-2">
                <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Agent <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <select id="agent_id" 
                            name="agent_id" 
                            required
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="">Select agent</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id', $gift->agent_id ?? '') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }} @if($agent->zone)({{ $agent->zone }})@endif
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                @error('agent_id')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Handled by (Employee) -->
            <div class="space-y-2">
                <label for="employee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Handled by
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <select id="employee_id" 
                            name="employee_id"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="">— Not assigned —</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id', $gift->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} @if($employee->job_position)({{ $employee->job_position }})@endif
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                @error('employee_id')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Occasion -->
            <div class="space-y-2">
                <label for="occasion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Occasion
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="occasion" 
                           name="occasion" 
                           value="{{ old('occasion', $gift->occasion ?? '') }}"
                           placeholder="e.g. Birthday, Yearly, Festival, Bonus"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('occasion')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Gift Type -->
            <div class="space-y-2">
                <label for="gift_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Gift Type
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875zm0 0V3m0 9.75v-1.5M3.375 7.5h17.25" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="gift_type" 
                           name="gift_type" 
                           value="{{ old('gift_type', $gift->gift_type ?? '') }}"
                           placeholder="e.g. Fridge, Banner, Gift Item, Rack"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('gift_type')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Amount -->
            <div class="space-y-2">
                <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Amount
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
                           value="{{ old('amount', $gift->amount ?? '') }}"
                           placeholder="0.00"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('amount')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campaign Code -->
            <div class="space-y-2">
                <label for="campaign_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Campaign Code
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="campaign_code" 
                           name="campaign_code" 
                           value="{{ old('campaign_code', $gift->campaign_code ?? '') }}"
                           placeholder="e.g. BDAY-2025, FEST-01"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('campaign_code')
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
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    @php
                        $currentStatus = strtolower(old('status', $gift->status ?? 'planned'));
                    @endphp
                    <select id="status" 
                            name="status"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="planned" {{ $currentStatus === 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="given" {{ $currentStatus === 'given' ? 'selected' : '' }}>Given</option>
                        <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                @error('status')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description (Full Width) -->
            <div class="md:col-span-2 lg:col-span-3 space-y-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="description" 
                           name="description" 
                           value="{{ old('description', $gift->description ?? '') }}"
                           placeholder="Additional details about the gift"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('description')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
