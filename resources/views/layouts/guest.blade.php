{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $appBrandName ?? config('app.name'))</title>

    <script>
        window.erpDefaultThemeMode = @json($defaultThemeMode ?? 'dark');
        window.erpTheme = {
            getStoredTheme() {
                try {
                    return localStorage.getItem('theme');
                } catch (error) {
                    return null;
                }
            },
            setStoredTheme(theme) {
                try {
                    localStorage.setItem('theme', theme);
                } catch (error) {
                    // Ignore storage failures and keep the in-memory theme.
                }
            },
            resolveTheme() {
                const savedTheme = this.getStoredTheme();
                const configuredTheme = window.erpDefaultThemeMode || 'dark';
                const fallbackTheme = configuredTheme === 'system'
                    ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    : configuredTheme;

                return savedTheme || fallbackTheme;
            },
            applyTheme(theme) {
                const html = document.documentElement;
                const body = document.body;
                const lightBg = '#F9FAFB';
                const darkBg = '#101828';

                if (theme === 'dark') {
                    html.classList.add('dark');
                    html.style.backgroundColor = darkBg;
                    html.style.colorScheme = 'dark';
                    if (body) {
                        body.classList.add('dark', 'bg-gray-900');
                        body.classList.remove('bg-gray-50', 'bg-gray-950');
                        body.style.backgroundColor = darkBg;
                        body.style.colorScheme = 'dark';
                    }
                } else {
                    html.classList.remove('dark');
                    html.style.backgroundColor = lightBg;
                    html.style.colorScheme = 'light';
                    if (body) {
                        body.classList.remove('dark', 'bg-gray-900', 'bg-gray-950');
                        body.classList.add('bg-gray-50');
                        body.style.backgroundColor = lightBg;
                        body.style.colorScheme = 'light';
                    }
                }
            }
        };

        (function () {
            window.erpTheme.applyTheme(window.erpTheme.resolveTheme());
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(!empty($brandThemeVariables))
        <style>
            :root {
                @foreach($brandThemeVariables as $variable => $value)
                    {{ $variable }}: {{ $value }};
                @endforeach
            }
        </style>
    @endif

    @stack('styles')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('loader', {
                show: true,
                fallbackTimer: null,
                init() {
                    const hideSoon = () => {
                        window.setTimeout(() => this.hide(), 150);
                    };

                    if (document.readyState === 'complete') {
                        hideSoon();
                    } else {
                        window.addEventListener('load', hideSoon, { once: true });
                    }

                    this.fallbackTimer = window.setTimeout(() => {
                        this.hide();
                    }, 2000);
                },
                hide() {
                    if (this.fallbackTimer) {
                        window.clearTimeout(this.fallbackTimer);
                        this.fallbackTimer = null;
                    }

                    this.show = false;
                }
            });
        });
    </script>
</head>
<body class="theme-text-scope font-outfit bg-gray-50 antialiased dark:bg-gray-900" x-data>
    {{-- Simple guest layout with loader --}}
    {{-- Page Loader --}}
    <div x-show="$store.loader.show"
         x-transition:leave="loader-fade-leave"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[99999] flex flex-col items-center justify-center bg-white dark:bg-gray-900"
         style="will-change: opacity;">
        <div class="mb-6 animate-pulse">
            @if(!empty($appLogoUrl))
                <img src="{{ $appLogoUrl }}" alt="{{ $appBrandName }}" class="h-20 w-32 rounded-2xl bg-white object-contain px-2 shadow-theme-md dark:bg-gray-900" />
            @else
                <div class="app-brand-logo-lg flex items-center justify-center rounded-2xl bg-brand-50 shadow-theme-md dark:bg-brand-500/10">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-brand-600 dark:text-brand-400">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            @endif
        </div>
        @if(empty($appLogoUrl))
            <h1 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ $appBrandName }}
            </h1>
        @endif
        <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-brand-500 animate-bounce" style="animation-delay:0ms;"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-brand-500/80 animate-bounce" style="animation-delay:150ms;"></span>
            <span class="h-2.5 w-2.5 rounded-full bg-brand-500/60 animate-bounce" style="animation-delay:300ms;"></span>
        </div>
        <p class="mt-4 text-theme-sm text-gray-600 dark:text-gray-400 animate-pulse">
            Preparing sign-in screen...
        </p>
    </div>

    <div class="flex min-h-screen flex-col">
        {{-- Optional minimal header for guest pages --}}
        <header class="absolute left-0 right-0 top-0 z-50">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                {{-- Brand --}}
                <a href="{{ route('login') }}" class="text-title-sm font-semibold text-gray-900 dark:text-white">
                    @if(!empty($appLogoUrl))
                        <span class="flex items-center gap-2">
                            <img src="{{ $appLogoUrl }}" alt="{{ $appBrandName }}" class="h-12 w-20 rounded-lg bg-white object-contain px-1.5 dark:bg-gray-900" />
                        </span>
                    @else
                        {{ $appBrandName }}
                    @endif
                </a>

                {{-- Right side actions --}}
                <div class="flex items-center gap-3">
                    {{-- Dark/Light mode toggle --}}
                    <button id="theme-toggle" 
                            type="button" 
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-300"
                            aria-label="Toggle dark mode">
                        {{-- Sun icon (light mode) --}}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="block dark:hidden">
                            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 2V4M12 20V22M4 12H2M6.34315 6.34315L4.92893 4.92893M17.6569 6.34315L19.0711 4.92893M6.34315 17.6569L4.92893 19.0711M17.6569 17.6569L19.0711 19.0711M22 12H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        {{-- Moon icon (dark mode) --}}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="hidden dark:block">
                            <path d="M12 3C10.6868 3 9.38642 3.25866 8.17317 3.7612C6.95991 4.26375 5.85752 5.00035 4.92893 5.92893C3.05357 7.8043 2 10.3478 2 13C2 15.6522 3.05357 18.1957 4.92893 20.0711C5.85752 20.9997 6.95991 21.7362 8.17317 22.2388C9.38642 22.7413 10.6868 23 12 23C14.6522 23 17.1957 21.9464 19.0711 20.0711C20.9464 18.1957 22 15.6522 22 13C22 12.6868 21.985 12.3742 21.955 12.064C20.9898 12.7882 19.8256 13.1787 18.6307 13.1787C15.5359 13.1787 13.0225 10.6653 13.0225 7.57052C13.0225 6.10801 13.6212 4.70255 14.649 3.68696C13.8402 3.24472 12.9384 3 12 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                            Create account
                        </a>
                    @endif
                </div>
            </div>
        </header>

        {{-- Main content --}}
        <main class="flex flex-1 items-center justify-center px-4 py-24 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        {{-- Simple footer for guest pages --}}
        <footer class="py-6 text-center">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} {{ $legalCompanyName }}. All rights reserved.
            </p>
        </footer>
    </div>

    {{-- Theme toggle script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                    window.erpTheme.applyTheme(nextTheme);
                    window.erpTheme.setStoredTheme(nextTheme);
                });
            }
        });
    </script>

    {{-- Stack for page-specific scripts --}}
    @stack('scripts')
</body>
</html>
