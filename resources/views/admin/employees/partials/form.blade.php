<div class="space-y-10">
    <!-- Basic Information Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Information</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pl-11">
            <!-- Name -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Full Name <span class="text-error-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $employee->name ?? '') }}" 
                           required
                           placeholder="e.g. John Doe"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('name')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department -->
            <div class="space-y-2">
                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Department
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4" />
                        </svg>
                    </div>
                    <select id="department" 
                            name="department"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="">Select department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ old('department', $employee->department ?? '') === $department ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                @error('department')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Job Position -->
            <div class="space-y-2">
                <label for="job_position" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Job Position
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <select id="job_position" 
                            name="job_position"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="">Select job position</option>
                        @foreach($jobPositions as $jobPosition)
                            <option value="{{ $jobPosition }}" {{ old('job_position', $employee->job_position ?? '') === $jobPosition ? 'selected' : '' }}>
                                {{ $jobPosition }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                @error('job_position')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Work Zone -->
            <div class="space-y-2">
                <label for="work_zone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Work Zone
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                    </div>
                    <select id="work_zone" 
                            name="work_zone"
                            class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                        <option value="">Select work zone</option>
                        @foreach($workZones as $workZone)
                            <option value="{{ $workZone }}" {{ old('work_zone', $employee->work_zone ?? '') === $workZone ? 'selected' : '' }}>
                                {{ $workZone }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                @error('work_zone')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Work Contact Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Work Contact</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pl-11">
            <!-- Work Email -->
            <div class="space-y-2">
                <label for="work_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Work Email
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.57 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <input type="email" 
                           id="work_email" 
                           name="work_email" 
                           value="{{ old('work_email', $employee->work_email ?? '') }}"
                           placeholder="e.g. john.doe@company.com"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('work_email')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Work Phone -->
            <div class="space-y-2">
                <label for="work_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Work Phone
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="work_phone" 
                           name="work_phone" 
                           value="{{ old('work_phone', $employee->work_phone ?? '') }}"
                           placeholder="e.g. +880 2-12345678"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('work_phone')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Work Mobile -->
            <div class="space-y-2">
                <label for="work_mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Work Mobile
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="work_mobile" 
                           name="work_mobile" 
                           value="{{ old('work_mobile', $employee->work_mobile ?? '') }}"
                           placeholder="e.g. +880 1XXX-XXXXXX"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                </div>
                @error('work_mobile')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tags -->
            <div class="md:col-span-2 lg:col-span-3 space-y-2">
                <label for="tags-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tags
                </label>
                <div class="tag-input-wrapper relative"
                     data-tag-input
                     data-tag-options='@json($tagOptions ?? [])'>
                    <div class="flex flex-wrap items-center gap-2 min-h-[3rem] w-full rounded-xl border border-gray-200 bg-white/50 px-3 py-2 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:focus-within:border-brand-400">
                        <div class="tag-chips flex flex-wrap gap-2" data-tag-chips></div>
                        <input type="text"
                               id="tags-input"
                               class="tag-input-field flex-1 min-w-[120px] border-0 bg-transparent p-1 text-sm text-gray-900 placeholder-gray-400 outline-none dark:text-white dark:placeholder-gray-500"
                               data-tag-field
                               autocomplete="off"
                               placeholder="Start typing to add tags…">
                    </div>
                    <input type="hidden"
                           name="tags"
                           data-tag-hidden
                           value="{{ old('tags', isset($employee->tags) && is_array($employee->tags) ? implode(', ', $employee->tags) : '') }}">
                    <div class="tag-suggestions absolute z-10 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900 hidden" data-tag-suggestions></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Press comma or enter to add tags. Use tags to categorize employees (e.g., "manager", "driver", "certified").
                </p>
                @error('tags')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="space-y-6">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-800/30">
                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 0119.5 7.372L8.552 18.32a1.5 1.5 0 01-2.122-2.122L16.5 6.75" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Documents</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-11">
            <!-- Photo Upload -->
            <div class="space-y-2">
                <label for="photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Profile Photo
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                        </svg>
                    </div>
                    <input type="file" 
                           id="photo" 
                           name="photo" 
                           accept="image/*"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-2.5 text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:file:bg-brand-900/30 dark:file:text-brand-400 dark:hover:file:bg-brand-900/50 transition-all">
                </div>
                @if(!empty($employee->photo_path))
                    <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('storage/'.$employee->photo_path) }}" 
                                 alt="Employee photo" 
                                 class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-sm dark:border-gray-700">
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Current photo</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ basename($employee->photo_path) }}</p>
                        </div>
                    </div>
                @endif
                @error('photo')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- CV Upload -->
            <div class="space-y-2">
                <label for="cv" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Curriculum Vitae (CV)
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <input type="file" 
                           id="cv" 
                           name="cv" 
                           accept=".pdf,.doc,.docx"
                           class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-2.5 text-sm text-gray-900 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:file:bg-brand-900/30 dark:file:text-brand-400 dark:hover:file:bg-brand-900/50 transition-all">
                </div>
                @if(!empty($employee->cv_path))
                    <div class="mt-3 flex items-center gap-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/30">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12.75v6m3-3H9" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Current CV</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ basename($employee->cv_path) }}</p>
                        </div>
                        <a href="{{ asset('storage/'.$employee->cv_path) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </a>
                    </div>
                @endif
                @error('cv')
                    <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>