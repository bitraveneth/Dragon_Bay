@extends('layouts.app')

@section('content')
<div class="dashboard-shell">
    <section class="panel rounded-xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <header class="panel-header mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-title-sm font-semibold text-gray-900 dark:text-white">Edit salary distribution</h1>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Update salary, allowances, payment method, or remarks.</p>
            </div>
            <a href="{{ route('admin.salary-distributions.index') }}" 
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.8333 10H4.16667M4.16667 10L8.33333 14.1667M4.16667 10L8.33333 5.83333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to distributions
            </a>
        </header>

        @if(session('error'))
            <div class="mb-6 rounded-lg bg-error-50 p-4 dark:bg-error-500/10">
                <div class="flex items-center gap-3">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-error-600 dark:text-error-400">
                        <path d="M10 6.66667V10M10 13.3333H10.0083M18.3333 10C18.3333 14.6024 14.6024 18.3333 10 18.3333C5.39763 18.3333 1.66667 14.6024 1.66667 10C1.66667 5.39763 5.39763 1.66667 10 1.66667C14.6024 1.66667 18.3333 5.39763 18.3333 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <p class="text-theme-sm font-medium text-error-700 dark:text-error-400">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-error-200 bg-error-50 p-4 dark:border-error-800 dark:bg-error-500/10">
                <div class="flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-error-600 dark:text-error-400">
                        <path d="M10 6.66667V10M10 13.3333H10.0083M18.3333 10C18.3333 14.6024 14.6024 18.3333 10 18.3333C5.39763 18.3333 1.66667 14.6024 1.66667 10C1.66667 5.39763 5.39763 1.66667 10 1.66667C14.6024 1.66667 18.3333 5.39763 18.3333 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <p class="text-theme-sm font-medium text-error-700 dark:text-error-400">
                        Please correct the {{ $errors->count() }} {{ Str::plural('error', $errors->count()) }} below.
                    </p>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.salary-distributions.update', $distribution) }}" method="POST" 
              class="space-y-6" enctype="multipart/form-data" id="salary-distribution-form">
            @csrf
            @method('PATCH')
            @include('admin.finance.salary_distributions.partials.form', ['distribution' => $distribution])
            
            <div class="form-actions flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.salary-distributions.index') }}" 
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                   id="cancel-btn">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20 disabled:pointer-events-none disabled:opacity-50 dark:bg-brand-500 dark:hover:bg-brand-600"
                        id="submit-btn">
                    <span class="inline-flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="submit-icon">
                            <path d="M14.1667 5.83333L17.5 9.16667M17.5 9.16667L14.1667 12.5M17.5 9.16667H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="submit-text">Save changes</span>
                        <svg class="submit-spinner hidden h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('salary-distribution-form');
        const submitBtn = document.getElementById('submit-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const submitText = submitBtn.querySelector('.submit-text');
        const submitIcon = submitBtn.querySelector('.submit-icon');
        const submitSpinner = submitBtn.querySelector('.submit-spinner');
        
        let formDirty = false;
        let originalFormData = new FormData(form);

        // Track form changes
        function checkFormDirty() {
            const currentFormData = new FormData(form);
            let isDirty = false;
            
            // Compare each field (skip token, method, and file inputs for comparison)
            for (let [key, value] of currentFormData.entries()) {
                if (key === '_token' || key === '_method') continue;
                
                // Handle file inputs differently
                if (value instanceof File) {
                    // Only mark as dirty if a new file is selected (has name and size)
                    if (value.name && value.size > 0) {
                        isDirty = true;
                        break;
                    }
                } else if (value !== originalFormData.get(key)) {
                    isDirty = true;
                    break;
                }
            }
            
            formDirty = isDirty;
        }

        // Listen for all form changes
        form.addEventListener('input', checkFormDirty);
        form.addEventListener('change', checkFormDirty);

        // Handle form submission with loading state
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitText.textContent = 'Saving...';
            submitIcon.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
            
            // Store that form was submitted
            sessionStorage.setItem('formSubmitted', 'true');
        });

        // Handle cancel with dirty check
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                if (formDirty) {
                    e.preventDefault();
                    
                    // Create custom confirm dialog
                    const confirmDialog = document.createElement('div');
                    confirmDialog.className = 'fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/50 p-4 dark:bg-gray-950/80';
                    confirmDialog.innerHTML = `
                        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-theme-xl dark:bg-gray-900">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/20">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning-600 dark:text-warning-400">
                                    <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h3 class="mb-2 text-title-sm font-semibold text-gray-900 dark:text-white">Unsaved changes</h3>
                            <p class="mb-6 text-theme-sm text-gray-600 dark:text-gray-400">You have unsaved changes. Are you sure you want to leave? Your changes will be lost.</p>
                            <div class="flex items-center justify-end gap-3">
                                <button class="cancel-no inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                                    Stay
                                </button>
                                <a href="{{ route('admin.salary-distributions.index') }}" class="cancel-yes inline-flex items-center justify-center rounded-lg bg-error-600 px-4 py-2.5 text-theme-sm font-medium text-white transition hover:bg-error-700">
                                    Leave anyway
                                </a>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(confirmDialog);
                    
                    // Handle dialog buttons
                    confirmDialog.querySelector('.cancel-no').addEventListener('click', function() {
                        confirmDialog.remove();
                    });
                    
                    confirmDialog.querySelector('.cancel-yes').addEventListener('click', function() {
                        confirmDialog.remove();
                        window.location.href = cancelBtn.href;
                    });
                }
            });
        }

        // Check for successful form submission from session
        if (sessionStorage.getItem('formSubmitted')) {
            sessionStorage.removeItem('formSubmitted');
            // Optional: Show success toast/notification
        }

        // Warn about unsaved changes when leaving page
        window.addEventListener('beforeunload', function(e) {
            if (formDirty && !submitBtn.disabled) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Restore form state if coming back via back button
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                submitBtn.disabled = false;
                submitText.textContent = 'Save changes';
                submitIcon.classList.remove('hidden');
                submitSpinner.classList.add('hidden');
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
@endpush