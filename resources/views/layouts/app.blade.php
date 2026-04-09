@php
    require resource_path('views/layouts/partials/system-tour-steps.php');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? (($appBrandName ?? config('app.name')) . ' Admin') }}</title>

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
                        body.classList.remove('bg-gray-50');
                        body.style.backgroundColor = darkBg;
                        body.style.colorScheme = 'dark';
                    }
                } else {
                    html.classList.remove('dark');
                    html.style.backgroundColor = lightBg;
                    html.style.colorScheme = 'light';
                    if (body) {
                        body.classList.remove('dark', 'bg-gray-900');
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
        window.erpTourSteps = @json($tourSteps);
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    this.theme = window.erpTheme.resolveTheme();
                    this.updateTheme();
                },
                theme: 'dark',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    window.erpTheme.setStoredTheme(this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    window.erpTheme.applyTheme(this.theme);
                },
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,

                notifyChange() {
                    window.dispatchEvent(new CustomEvent('app:sidebar-changed', {
                        detail: {
                            expanded: this.isExpanded,
                            mobileOpen: this.isMobileOpen,
                        }
                    }));
                },

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                    this.notifyChange();
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    this.notifyChange();
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                    this.notifyChange();
                },
            });

            Alpine.store('loader', {
                show: false,
                init() {
                    window.dispatchEvent(new CustomEvent('app:content-visible'));
                },
                hide() {
                    this.show = false;
                }
            });

        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        @media print {
            @page {
                margin: 12mm;
            }

            body {
                background: #ffffff !important;
            }

            .print-hidden {
                display: none !important;
            }

            [data-tour="page-content"] {
                max-width: none !important;
                padding: 0 !important;
            }

            .min-h-screen > .flex-1 {
                margin-left: 0 !important;
            }

            .shadow-sm,
            .shadow-md,
            .shadow-lg,
            .shadow-xl,
            .shadow-2xl,
            .shadow-theme-xs,
            .shadow-theme-sm,
            .shadow-theme-md,
            .shadow-theme-lg {
                box-shadow: none !important;
            }

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Loader animations */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .loader-fade-leave {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            pointer-events: none;
        }

        .tour-target-active {
            position: relative;
            z-index: 100003 !important;
            border-radius: 18px;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2), 0 18px 50px rgba(15, 23, 42, 0.28);
        }
    </style>
</head>

<body x-data
      class="theme-text-scope overflow-x-clip"
      x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
        const checkMobile = () => {
            if (window.innerWidth < 1280) {
                $store.sidebar.setMobileOpen(false);
                $store.sidebar.isExpanded = false;
            } else {
                $store.sidebar.isMobileOpen = false;
                $store.sidebar.isExpanded = true;
            }
        };
        window.addEventListener('resize', checkMobile);">

    <div class="min-h-screen overflow-x-clip xl:flex">
        
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="print-main min-w-0 w-full overflow-x-clip flex-1 transition-all duration-300 ease-in-out"
             :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded,
                'xl:ml-[90px]': !$store.sidebar.isExpanded,
                'ml-0': $store.sidebar.isMobileOpen
             }"
             :style="window.innerWidth >= 1280
                ? { width: $store.sidebar.isExpanded ? 'calc(100% - 290px)' : 'calc(100% - 90px)' }
                : { width: '100%' }">
            @include('layouts.app-header')

            <div class="mx-auto w-full min-w-0 max-w-(--breakpoint-2xl) p-4 md:p-6 print:!mx-0 print:!w-full print:!max-w-none print:!p-0" data-tour="page-content">
                @if(session('status'))
                    <div x-data="{ open: true }"
                         x-init="setTimeout(() => open = false, 2600)"
                         x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="fixed right-5 top-20 z-[1000] w-full max-w-sm rounded-2xl border border-brand-200 bg-white p-4 shadow-2xl dark:border-brand-500/30 dark:bg-gray-900">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-brand-700 dark:text-brand-300">Success</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ session('status') }}</div>
                            </div>
                            <button type="button"
                                    @click="open = false"
                                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ open: true }"
                         x-init="setTimeout(() => open = false, 3200)"
                         x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="fixed right-5 top-20 z-[1000] w-full max-w-sm rounded-2xl border border-error-200 bg-white p-4 shadow-2xl dark:border-error-500/30 dark:bg-gray-900">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-error-700 dark:text-error-300">Failed</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ session('error') }}</div>
                            </div>
                            <button type="button"
                                    @click="open = false"
                                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if(($errors ?? null) && $errors->any())
                    <div class="mb-4">
                        <div class="rounded-lg border border-error-100 bg-error-50 px-4 py-3 text-sm text-error-700">
                            <strong class="font-semibold">Something went wrong.</strong>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <div class="fixed bottom-5 right-5 z-[1001] print-hidden" x-data>
        <div class="flex flex-col items-end gap-3">
            <div x-show="$store.tour.launcherOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="w-[22rem] rounded-3xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Understand the logistics flow</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Follow the real business flow: masters, inbound, operations, orders, billing, and reporting.
                        </p>
                    </div>
                    <button type="button"
                            @click="$store.tour.closeLauncher()"
                            class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 rounded-2xl bg-gray-50 px-4 py-3 text-xs text-gray-600 dark:bg-gray-800/80 dark:text-gray-300">
                    <div class="font-semibold text-gray-800 dark:text-white">Tour path</div>
                    <div class="mt-1 leading-5">Masters setup → Purchase orders → Inbound receipts → Operations → Orders → Client invoices → Reconciliation → Reports</div>
                </div>

                <div class="mt-4 space-y-2">
                    <button type="button"
                            @click="$store.tour.start()"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
                        <span x-text="$store.tour.hasSavedProgress ? 'Restart system tour' : 'Start full system tour'"></span>
                    </button>
                    <button type="button"
                            x-show="$store.tour.hasSavedProgress"
                            x-cloak
                            @click="$store.tour.resumeIfNeeded(); $store.tour.closeLauncher()"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        Resume saved step
                    </button>
                    <a href="{{ route('admin.products.index') }}"
                       class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        Start with catalog
                    </a>
                    @if(Route::has('admin.client-guide'))
                        <a href="{{ route('admin.client-guide') }}"
                           class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                            Open full manual
                        </a>
                    @endif
                </div>
            </div>

            <button type="button"
                    data-tour="help-launcher"
                    @click="$store.tour.toggleLauncher()"
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-500 text-xl font-semibold text-white shadow-2xl transition hover:bg-brand-600">
                ?
            </button>
        </div>
    </div>

    <div x-show="$store.tour.isActive"
         x-cloak
         class="fixed inset-0 z-[100002]">
        <div x-show="$store.tour.spotlight.width > 0 && $store.tour.spotlight.height > 0" class="absolute inset-0 bg-gray-950/55"></div>

        <div x-show="$store.tour.spotlight.width > 0 && $store.tour.spotlight.height > 0"
             class="pointer-events-none absolute rounded-[28px] border-2 border-white/80 shadow-[0_0_0_9999px_rgba(3,7,18,0.55)] transition-all duration-200"
             :style="`top:${$store.tour.spotlight.top}px;left:${$store.tour.spotlight.left}px;width:${$store.tour.spotlight.width}px;height:${$store.tour.spotlight.height}px;`">
        </div>

        <div data-tour-tooltip
             class="pointer-events-auto absolute z-[100004] w-[24rem] rounded-3xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900"
             :style="`top:${$store.tour.tooltip.top};left:${$store.tour.tooltip.left};right:${$store.tour.tooltip.right};bottom:${$store.tour.tooltip.bottom};`">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-500">
                        Step <span x-text="$store.tour.activeIndex + 1"></span>
                        of <span x-text="$store.tour.steps.length"></span>
                    </div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-[0.16em] text-gray-400"
                         x-text="$store.tour.currentStep()?.section ?? ''">
                    </div>
                    <h3 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white"
                        x-text="$store.tour.currentStep()?.title ?? ''">
                    </h3>
                </div>
                <button type="button"
                        @click="$store.tour.end()"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 space-y-4 text-[15px] leading-7 text-gray-600 dark:text-gray-300">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-400">Why this matters</div>
                    <p class="mt-1" x-text="$store.tour.currentStep()?.purpose ?? ''"></p>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-400">What to do here</div>
                    <p class="mt-1" x-text="$store.tour.currentStep()?.action ?? ''"></p>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3">
                <button type="button"
                        @click="$store.tour.previous()"
                        :disabled="$store.tour.activeIndex === 0"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                    Previous
                </button>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="$store.tour.end()"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        Skip
                    </button>
                    <button type="button"
                            @click="$store.tour.next()"
                            class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
                        <span x-text="$store.tour.activeIndex === ($store.tour.steps.length - 1) ? 'Finish' : 'Next'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')

</body>
</html>
