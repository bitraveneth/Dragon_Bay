@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Employee Avatar -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full blur opacity-20"></div>
                    <div class="relative">
                        @if($employee->photo_path)
                            <img src="{{ asset('storage/'.$employee->photo_path) }}" 
                                 alt="{{ $employee->name }}" 
                                 class="h-16 w-16 rounded-full object-cover border-4 border-white shadow-xl dark:border-gray-800">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                                {{ substr($employee->name, 0, 1) }}{{ substr($employee->name, strpos($employee->name, ' ') + 1, 1) ?? '' }}
                            </div>
                        @endif
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Grant Badge
                        </h1>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                            {{ $employee->name }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Select a badge and optionally add a note
                    </p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $employee->job_position ?? 'Employee' }}</span>
                        @if($employee->department)
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span>{{ $employee->department }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees.edit', $employee) }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Employee
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-3xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <!-- Card Header -->
        <div class="border-b border-gray-100 bg-gradient-to-r from-brand-50 to-white px-8 py-6 dark:border-gray-800 dark:from-brand-950/30 dark:to-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-md">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Grant Recognition</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        Award a badge to recognize achievements and contributions
                    </p>
                </div>
            </div>
            
            <!-- Tip Banner -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-brand-100/50 p-4 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm">
                    <span class="font-semibold">Tip:</span> 
                    <span class="ml-1">Badges are visible on the employee's profile and can be used for recognition programs.</span>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.employees.badges.grant', $employee) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Badge Selection -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="badge_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Select Badge <span class="text-error-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                </svg>
                            </div>
                            <select id="badge_id" 
                                    name="badge_id" 
                                    required
                                    class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-10 py-3.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white appearance-none transition-all">
                                <option value="">Select a badge</option>
                                @foreach($badges as $badge)
                                    <option value="{{ $badge->id }}" 
                                            data-color="{{ $badge->color ?? '#465fff' }}"
                                            data-code="{{ $badge->code }}"
                                            data-description="{{ $badge->description ?? '' }}"
                                            {{ old('badge_id') == $badge->id ? 'selected' : '' }}>
                                        {{ $badge->name }} ({{ $badge->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Selected Badge Preview -->
                        <div id="badge-preview" class="mt-3 hidden">
                            <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-4 dark:bg-gray-800/30">
                                <div id="badge-color-preview" class="flex h-12 w-12 items-center justify-center rounded-xl">
                                    <span id="badge-initial" class="text-lg font-bold"></span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 id="badge-name" class="text-sm font-semibold text-gray-900 dark:text-white"></h4>
                                        <span id="badge-code" class="font-mono text-xs text-gray-500 dark:text-gray-400"></span>
                                    </div>
                                    <p id="badge-description" class="mt-1 text-xs text-gray-600 dark:text-gray-400"></p>
                                </div>
                            </div>
                        </div>
                        @error('badge_id')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Granted Date -->
                    <div class="space-y-2">
                        <label for="granted_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Granted Date
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" 
                                   id="granted_at" 
                                   name="granted_at" 
                                   value="{{ old('granted_at', now()->format('Y-m-d')) }}"
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Defaults to today's date</p>
                        @error('granted_at')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note -->
                    <div class="space-y-2">
                        <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Note
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-brand-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="note" 
                                   name="note" 
                                   value="{{ old('note') }}"
                                   placeholder="e.g. Awarded for outstanding performance in Q1"
                                   class="w-full rounded-xl border border-gray-200 bg-white/50 pl-10 pr-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-500 transition-all">
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Optional: Add context or reason for granting this badge</p>
                        @error('note')
                            <p class="text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-10 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.employees.edit', $employee) }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Grant Badge
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Tips Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/30">
                <svg class="h-5 w-5 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">About Badges</h3>
                <ul class="mt-2 space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start gap-2">
                        <span class="text-brand-500 mt-0.5">•</span>
                        <span><span class="font-medium">Badges</span> are visual recognitions that appear on employee profiles</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-brand-500 mt-0.5">•</span>
                        <span>Each badge can be granted to multiple employees</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-brand-500 mt-0.5">•</span>
                        <span>Use the <span class="font-medium">Granted Date</span> to set when the badge was awarded</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-brand-500 mt-0.5">•</span>
                        <span>Add a <span class="font-medium">Note</span> to provide context about why the badge was granted</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const badgeSelect = document.getElementById('badge_id');
    const badgePreview = document.getElementById('badge-preview');
    const badgeColorPreview = document.getElementById('badge-color-preview');
    const badgeInitial = document.getElementById('badge-initial');
    const badgeName = document.getElementById('badge-name');
    const badgeCode = document.getElementById('badge-code');
    const badgeDescription = document.getElementById('badge-description');

    function updateBadgePreview() {
        const selectedOption = badgeSelect.options[badgeSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const badgeId = selectedOption.value;
            const badgeColor = selectedOption.dataset.color || '#465fff';
            const badgeCodeText = selectedOption.dataset.code || '';
            const badgeDescriptionText = selectedOption.dataset.description || '';
            const badgeNameText = selectedOption.text.split(' (')[0];
            const initial = badgeNameText.charAt(0);
            
            // Update preview
            badgeColorPreview.style.backgroundColor = `${badgeColor}15`;
            badgeColorPreview.style.border = `1px solid ${badgeColor}30`;
            badgeInitial.style.color = badgeColor;
            badgeInitial.textContent = initial;
            badgeName.textContent = badgeNameText;
            badgeCode.textContent = badgeCodeText;
            
            if (badgeDescriptionText) {
                badgeDescription.textContent = badgeDescriptionText;
                badgeDescription.classList.remove('hidden');
            } else {
                badgeDescription.textContent = 'No description provided';
                badgeDescription.classList.add('italic', 'text-gray-500');
            }
            
            badgePreview.classList.remove('hidden');
        } else {
            badgePreview.classList.add('hidden');
        }
    }

    badgeSelect.addEventListener('change', updateBadgePreview);
    
    // Initial preview if a badge is pre-selected
    if (badgeSelect.value) {
        updateBadgePreview();
    }
});
</script>
@endpush
@endsection