<div class="space-y-6">
    <!-- Badge Details Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Badge Details</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
            <!-- Badge Name -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Badge Name <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $badge->name ?? '') }}" 
                           required
                           placeholder="e.g. Employee of the Month, Top Performer"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('name')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Badge Code -->
            <div class="space-y-2">
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Badge Code <span class="text-error-500">*</span>
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
                           value="{{ old('code', $badge->code ?? '') }}" 
                           required
                           placeholder="e.g. EMPLOYEE_MONTH, TOP_PERFORMER"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Short unique identifier (e.g., TOP_SR, BEST_SELLER)</p>
                @error('code')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Badge Color -->
            <div class="space-y-2">
                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Badge Color
                </label>
                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.662M16.5 9.75l.338-.338a2.625 2.625 0 013.712 0l.664.664" />
                            </svg>
                        </div>
                        @php
                            $currentColor = old('color', $badge->color ?? '#465fff');
                        @endphp
                        <input type="color" 
                               id="color" 
                               name="color" 
                               value="{{ $currentColor }}"
                               class="w-16 h-10 rounded-xl border border-gray-200 bg-white/50 cursor-pointer dark:border-gray-700 dark:bg-gray-800/50 transition-all">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Selected color:</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                  style="background: {{ $currentColor }}15; color: {{ $currentColor }}; border: 1px solid {{ $currentColor }}30;">
                                <span class="h-2 w-2 rounded-full" style="background: {{ $currentColor }};"></span>
                                {{ $currentColor }}
                            </span>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Choose a custom color for the badge</p>
                @error('color')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Badge Status
                </label>
                <div class="flex items-center h-10">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               class="sr-only peer"
                               {{ old('is_active', $badge->is_active ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ old('is_active', $badge->is_active ?? true) ? 'Active' : 'Inactive' }}
                        </span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Inactive badges cannot be granted to employees</p>
                @error('is_active')
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
                              placeholder="Describe what this badge represents, the criteria for earning it, and any other relevant details..."
                              class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">{{ old('description', $badge->description ?? '') }}</textarea>
                </div>
                @error('description')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>