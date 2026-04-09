@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-4">
                <!-- Badge Preview -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full blur opacity-20"></div>
                    <div class="relative flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                        <span style="color: white;">
                            {{ substr($badge->name, 0, 1) }}
                        </span>
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Edit Badge
                        </h1>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                              style="background: {{ $badge->color ?? '#465fff' }}15; color: {{ $badge->color ?? '#465fff' }}; border: 1px solid {{ $badge->color ?? '#465fff' }}30;">
                            <span class="h-1.5 w-1.5 rounded-full mr-1" style="background: {{ $badge->color ?? '#465fff' }};"></span>
                            {{ $badge->code }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Update badge details
                    </p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>Created {{ $badge->created_at?->diffForHumans() }}</span>
                        <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                        <span>Last updated {{ $badge->updated_at?->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.badges.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Badges
            </a>
        </div>
    </div>

    <!-- Current Badge Status Banner -->
    <div class="rounded-xl border-l-4 
        {{ $badge->is_active ? 'border-success-500 bg-success-50 dark:border-success-400 dark:bg-success-950/30' : 'border-gray-500 bg-gray-50 dark:border-gray-400 dark:bg-gray-950/30' }} p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                @if($badge->is_active)
                    <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @endif
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium">
                    <span class="font-semibold">Badge Status:</span> 
                    <span class="inline-flex items-center gap-1.5 ml-1.5">
                        <span class="h-1.5 w-1.5 rounded-full {{ $badge->is_active ? 'bg-success-500' : 'bg-gray-500' }}"></span>
                        <span>{{ $badge->is_active ? 'Active' : 'Inactive' }}</span>
                    </span>
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                    <span class="font-medium">Code:</span> {{ $badge->code }}
                    @if($badge->employees_count ?? 0)
                        · <span class="font-medium">{{ $badge->employees_count ?? 0 }}</span> employees awarded
                    @endif
                </p>
            </div>
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
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Badge Details</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        Update the badge information below
                    </p>
                </div>
            </div>
            
            <!-- Live Preview Banner -->
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-brand-100/50 p-4 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                </svg>
                <div class="text-sm flex-1">
                    <span class="font-semibold">Live Preview:</span>
                    <span class="ml-2 inline-flex items-center gap-2" id="badge-live-preview">
                        <!-- Dynamically updated via JavaScript -->
                    </span>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.badges.update', $badge) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <!-- Badge Form Partial -->
                @include('admin.employees.badges.partials.form', ['badge' => $badge])
                
                <!-- Form Actions -->
                <div class="mt-10 flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('admin.badges.index') }}" 
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Badge
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone - Delete Badge -->
    <div class="rounded-2xl border border-error-200 bg-white shadow-sm dark:border-error-800/30 dark:bg-gray-900">
        <div class="border-b border-error-100 px-6 py-4 dark:border-error-800/20">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-medium text-error-700 dark:text-error-400">Danger Zone</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this badge</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Once deleted, this badge will be removed from all employees who have been awarded it. 
                        This action cannot be undone.
                    </p>
                </div>
                <form action="{{ route('admin.badges.destroy', $badge) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete the badge {{ $badge->name }}? This will remove it from all employees and cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Badge
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live badge preview
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    const colorInput = document.getElementById('color');
    const isActiveCheckbox = document.getElementById('is_active');
    const livePreview = document.getElementById('badge-live-preview');
    
    function updateLivePreview() {
        const name = nameInput?.value || 'Badge';
        const code = codeInput?.value || 'CODE';
        const color = colorInput?.value || '#465fff';
        const isActive = isActiveCheckbox?.checked || false;
        
        const initial = name.charAt(0);
        
        livePreview.innerHTML = `
            <span class="inline-flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold"
                      style="background: ${color}15; color: ${color}; border: 1px solid ${color}30;">
                    ${initial}
                </span>
                <span class="font-medium text-gray-900 dark:text-white">${name}</span>
                <span class="font-mono text-xs text-gray-500 dark:text-gray-400">${code}</span>
                ${isActive ? 
                    '<span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400"><span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>Active</span>' : 
                    '<span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400"><span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>Inactive</span>'
                }
            </span>
        `;
    }
    
    if (nameInput) nameInput.addEventListener('input', updateLivePreview);
    if (codeInput) codeInput.addEventListener('input', updateLivePreview);
    if (colorInput) colorInput.addEventListener('input', updateLivePreview);
    if (isActiveCheckbox) isActiveCheckbox.addEventListener('change', updateLivePreview);
    
    // Initial preview
    updateLivePreview();
});
</script>
@endpush
@endsection