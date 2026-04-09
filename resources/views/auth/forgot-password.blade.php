@extends('layouts.guest')

@section('title', 'Forgot password | ' . ($appBrandName ?? config('app.name')))

@section('content')
<div class="w-full max-w-md">
    <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
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
            <h1 class="text-title-md font-semibold text-gray-900 dark:text-white">Forgot password</h1>
            <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-400">Enter your email and we will send you a password reset link.</p>
        </header>

        @if (session('status'))
            <div class="mb-5 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-theme-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

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

            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:bg-brand-500 dark:hover:bg-brand-600">
                Email reset link
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                    Back to sign in
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
