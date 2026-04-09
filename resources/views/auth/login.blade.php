{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.guest')

@section('title', 'Sign in | ' . ($appBrandName ?? config('app.name')))

@section('content')
<div class="w-full max-w-md">
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
        {{-- Brand Header --}}
        <header class="mb-8 text-center">
            <div class="mb-4 inline-flex items-center justify-center">
                @if(!empty($appLogoUrl))
                    <img src="{{ $appLogoUrl }}" alt="{{ $appBrandName }}" class="h-14 w-24 rounded-xl bg-white object-contain px-1.5 shadow-theme-xs dark:bg-gray-900" />
                @else
                    <div class="app-brand-logo flex items-center justify-center rounded-xl bg-brand-50 text-sm font-semibold text-brand-600 shadow-theme-xs dark:bg-brand-500/10 dark:text-brand-400">
                        {{ $appBrandInitials ?? \App\Helpers\SystemSettings::initials($appBrandName ?? config('app.name')) }}
                    </div>
                @endif
            </div>
            <h1 class="text-title-md font-semibold text-gray-900 dark:text-white">Welcome back</h1>
            <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-400">Sign in to continue to your control panel.</p>
        </header>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email Field --}}
            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    Email address
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-500 dark:text-gray-400">
                            <path d="M2.5 5.83333L10 10.8333L17.5 5.83333M4.16667 15.8333H15.8333C16.7538 15.8333 17.5 15.0871 17.5 14.1667V5.83333C17.5 4.91286 16.7538 4.16667 15.8333 4.16667H4.16667C3.24619 4.16667 2.5 4.91286 2.5 5.83333V14.1667C2.5 15.0871 3.24619 15.8333 4.16667 15.8333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="email"
                           placeholder="email"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-10 pr-4 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
                </div>
                @error('email')
                    <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Field --}}
            <div class="flex flex-col gap-1.5">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-theme-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-gray-500 dark:text-gray-400">
                            <path d="M4.16667 9.16667H15.8333C16.7538 9.16667 17.5 9.91286 17.5 10.8333V15.8333C17.5 16.7538 16.7538 17.5 15.8333 17.5H4.16667C3.24619 17.5 2.5 16.7538 2.5 15.8333V10.8333C2.5 9.91286 3.24619 9.16667 4.16667 9.16667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M6.66667 9.16667V5.83333C6.66667 3.99238 8.15905 2.5 10 2.5C11.8409 2.5 13.3333 3.99238 13.3333 5.83333V9.16667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           placeholder="••••••••"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-10 pr-12 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
                    <button type="button" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                            aria-label="Show password"
                            onclick="(function(btn){var input=document.getElementById('password');if(!input)return;var isPass=input.type==='password';input.type=isPass?'text':'password';btn.setAttribute('aria-label',isPass?'Hide password':'Show password');})(this)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 5C7 5 3.1 8.1 1.5 12c1.6 3.9 5.5 7 10.5 7s8.9-3.1 10.5-7C20.9 8.1 17 5 12 5z" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" 
                       name="remember" 
                       id="remember" 
                       class="h-4 w-4 rounded border-gray-300 text-brand-600 shadow-theme-xs focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:checked:bg-brand-500 dark:focus:ring-brand-500/30"
                       {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="text-theme-sm text-gray-600 dark:text-gray-400">
                    Remember me
                </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit" 
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:bg-brand-500 dark:hover:bg-brand-600">
                Sign in
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.16667 10H15.8333M15.8333 10L12.5 6.66667M15.8333 10L12.5 13.3333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            {{-- Demo Credentials (Local Only) --}}
            @if(app()->environment('local'))
                <div class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
                    <p class="text-center text-theme-xs text-gray-600 dark:text-gray-400 mb-2">
                        <span class="font-medium">Quick demo logins:</span> click to fill email &amp; password.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-2 text-theme-xs">
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="super@saferpv.local"
                                data-password="password">
                            super@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="admin@saferpv.local"
                                data-password="password">
                            admin@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="warehouse@saferpv.local"
                                data-password="password">
                            warehouse@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="production@saferpv.local"
                                data-password="password">
                            production@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="employee@saferpv.local"
                                data-password="password">
                            employee@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="sales.manager@saferpv.local"
                                data-password="password">
                            sales.manager@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="purchase@saferpv.local"
                                data-password="password">
                            purchase@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="accounts@saferpv.local"
                                data-password="password">
                            accounts@saferpv.local
                        </button>
                        <button type="button"
                                class="inline-flex items-center rounded-full bg-gray-200 px-3 py-1 font-medium text-gray-800 hover:bg-brand-100 hover:text-brand-700 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-500/20 dark:hover:text-brand-300"
                                data-demo-login
                                data-email="qc@saferpv.local"
                                data-password="password">
                            qc@saferpv.local
                        </button>
                    </div>
                    <p class="mt-2 text-center text-theme-xs text-gray-500 dark:text-gray-400">
                        Default password: <span class="font-mono">password</span>
                    </p>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const emailInput = document.getElementById('email');

        // Demo login buttons (local)
        if (emailInput && passwordInput) {
            document.querySelectorAll('[data-demo-login]').forEach(function(button) {
                button.addEventListener('click', function () {
                    const email = this.getAttribute('data-email') || '';
                    const password = this.getAttribute('data-password') || '';
                    emailInput.value = email;
                    passwordInput.value = password;
                    emailInput.focus();
                });
            });
        }

        // Remove autofocus on mobile to prevent keyboard popup
        if (window.innerWidth < 768 && emailInput) {
            emailInput.removeAttribute('autofocus');
        }
    });
</script>
@endpush
