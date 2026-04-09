<!-- Campaign Form Partial -->
<div class="space-y-10">
    <!-- Campaign Basics Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Campaign Basics</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pl-11">
            <!-- Campaign Name -->
            <div class="lg:col-span-2 space-y-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Campaign Name <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $campaign->name ?? '') }}" 
                           required
                           placeholder="e.g. Summer Sale 2025, Q1 Product Launch"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('name')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Campaign Code -->
            <div class="space-y-2">
                <label for="campaign_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Campaign Code
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="campaign_code" 
                           name="campaign_code" 
                           value="{{ old('campaign_code', $campaign->campaign_code ?? '') }}"
                           placeholder="e.g. SUM25-FB-01"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Internal tracking code</p>
                @error('campaign_code')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Platform -->
            <div class="space-y-2">
                <label for="platform" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Platform <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5" />
                        </svg>
                    </div>
                    @php
                        $currentPlatform = strtolower(old('platform', $campaign->platform ?? 'facebook'));
                    @endphp
                    <select id="platform" 
                            name="platform" 
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="facebook"{{ $currentPlatform === 'facebook' ? ' selected' : '' }}>Facebook</option>
                        <option value="instagram"{{ $currentPlatform === 'instagram' ? ' selected' : '' }}>Instagram</option>
                        <option value="google-ads"{{ $currentPlatform === 'google-ads' ? ' selected' : '' }}>Google Ads</option>
                        <option value="offline"{{ $currentPlatform === 'offline' ? ' selected' : '' }}>Offline (banner/flyer/fridge)</option>
                        <option value="other"{{ $currentPlatform === 'other' ? ' selected' : '' }}>Other</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                @error('platform')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="space-y-2">
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Status
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    @php
                        $currentStatus = strtolower(old('status', $campaign->status ?? 'planned'));
                    @endphp
                    <select id="status" 
                            name="status"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="planned"{{ $currentStatus === 'planned' ? ' selected' : '' }}>Planned</option>
                        <option value="running"{{ $currentStatus === 'running' ? ' selected' : '' }}>Running</option>
                        <option value="completed"{{ $currentStatus === 'completed' ? ' selected' : '' }}>Completed</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dates & Performance Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Dates & Performance</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 pl-11">
            <!-- Start Date -->
            <div class="space-y-2">
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Start Date
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date', optional($campaign->start_date ?? null)->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
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
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ old('end_date', optional($campaign->end_date ?? null)->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white transition-all">
                </div>
                @error('end_date')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reach -->
            <div class="space-y-2">
                <label for="reach" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Reach
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input type="number" 
                           id="reach" 
                           name="reach" 
                           step="1" 
                           min="0"
                           value="{{ old('reach', $campaign->reach ?? '') }}"
                           placeholder="0"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Unique users reached</p>
                @error('reach')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Impressions -->
            <div class="space-y-2">
                <label for="impressions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Impressions
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <input type="number" 
                           id="impressions" 
                           name="impressions" 
                           step="1" 
                           min="0"
                           value="{{ old('impressions', $campaign->impressions ?? '') }}"
                           placeholder="0"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total views</p>
                @error('impressions')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cost -->
            <div class="space-y-2">
                <label for="cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cost
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">BDT</span>
                    </div>
                    <input type="number" 
                           id="cost" 
                           name="cost" 
                           step="0.01" 
                           min="0"
                           value="{{ old('cost', $campaign->cost ?? '') }}"
                           placeholder="0.00"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total campaign spend</p>
                @error('cost')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <!-- Performance Summary (calculated fields) -->
        @if(!empty($campaign->reach) && !empty($campaign->impressions) && !empty($campaign->cost))
        <div class="mt-4 grid grid-cols-3 gap-4 pl-11">
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                <p class="text-xs text-gray-500 dark:text-gray-400">CTR</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $campaign->impressions > 0 ? number_format(($campaign->reach / $campaign->impressions) * 100, 2) : 0 }}%
                </p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                <p class="text-xs text-gray-500 dark:text-gray-400">CPC</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    BDT {{ $campaign->reach > 0 ? number_format($campaign->cost / $campaign->reach, 2) : 0 }}
                </p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                <p class="text-xs text-gray-500 dark:text-gray-400">CPM</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    BDT {{ $campaign->impressions > 0 ? number_format(($campaign->cost / $campaign->impressions) * 1000, 2) : 0 }}
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Attachments & Notes Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attachments & Notes</h3>
        </div>
        
        <div class="space-y-6 pl-11">
            <!-- Notes -->
            <div class="space-y-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Notes
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="4"
                          class="w-full rounded-xl border border-gray-200 bg-white/50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all"
                          placeholder="Add campaign objectives, target audience, special instructions, or any other relevant information...">{{ old('notes', $campaign->notes ?? '') }}</textarea>
                @error('notes')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Attachment -->
            <div class="space-y-2">
                <label for="attachment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Report / Creative
                </label>
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 0119.5 7.372L8.552 18.32a1.5 1.5 0 01-2.122-2.122L16.5 6.75" />
                            </svg>
                        </div>
                        <input type="file" 
                               id="attachment" 
                               name="attachment" 
                               class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-2.5 text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:file:bg-brand-900/30 dark:file:text-brand-400 dark:hover:file:bg-brand-900/50 transition-all">
                    </div>
                    
                    @if(!empty($campaign->attachment_path))
                        <div class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-4 py-2 dark:bg-gray-800/30">
                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Current:</span>
                            <a href="{{ asset('storage/'.$campaign->attachment_path) }}" 
                               target="_blank" 
                               class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                View attachment
                            </a>
                        </div>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Upload campaign reports, creative assets, or screenshots (max 10MB)</p>
                @error('attachment')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>