@extends('layouts.guest')

@section('title', 'Reset password | ' . ($appBrandName ?? config('app.name')))

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
            <h1 class="text-title-md font-semibold text-gray-900 dark:text-white">Reset password</h1>
            <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-400">Choose a new password for your account.</p>
        </header>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    Email address
                </label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email', $email) }}"
                       required
                       autocomplete="email"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
                @error('email')
                    <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    New password
                </label>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="new-password"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
                @error('password')
                    <p class="text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password_confirmation" class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    Confirm new password
                </label>
                <input id="password_confirmation"
                       type="password"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-700">
            </div>

            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:bg-brand-500 dark:hover:bg-brand-600">
                Reset password
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
