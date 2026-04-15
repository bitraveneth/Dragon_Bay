<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Client Portal') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased dark:bg-gray-950 dark:text-white">
<div class="min-h-screen flex flex-col">

    {{-- Top navigation --}}
    <header class="sticky top-0 z-30 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">

            {{-- Brand + client name --}}
            <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3 min-w-0">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white">
                    {{ strtoupper(substr($client->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $client->name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Client Portal</div>
                </div>
            </a>

            {{-- Nav links (desktop) --}}
            <nav class="hidden items-center gap-1 md:flex">
                @foreach([
                    'portal.dashboard'       => ['Dashboard',  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    'portal.shipments.index' => ['Shipments',  'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    'portal.orders.index'    => ['Orders',     'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'portal.invoices.index'  => ['Invoices',   'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                    'portal.deliveries.index'=> ['Deliveries', 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                    'portal.statement'       => ['Statement',  'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ] as $route => [$label, $icon])
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                              {{ request()->routeIs($route)
                                 ? 'bg-brand-600 text-white'
                                 : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                        </svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            {{-- User + logout --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.profile') }}"
                   class="hidden items-center gap-2 text-right sm:flex">
                    <div>
                        <div class="text-xs font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 hover:border-red-300 hover:text-red-600 dark:border-gray-700 dark:text-gray-400 dark:hover:border-red-700 dark:hover:text-red-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- Mobile nav --}}
        <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-800 md:hidden">
            <nav class="flex gap-1 px-4 py-2">
                @foreach([
                    'portal.dashboard'        => 'Dashboard',
                    'portal.shipments.index'  => 'Shipments',
                    'portal.orders.index'     => 'Orders',
                    'portal.invoices.index'   => 'Invoices',
                    'portal.deliveries.index' => 'Deliveries',
                    'portal.statement'        => 'Statement',
                ] as $route => $label)
                    <a href="{{ route($route) }}"
                       class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-medium
                              {{ request()->routeIs($route)
                                 ? 'bg-brand-600 text-white'
                                 : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    {{-- Main content --}}
    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8">
        @if(session('status'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/40 dark:bg-green-950/30 dark:text-green-300">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white py-4 dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 text-center text-xs text-gray-400 dark:text-gray-600">
            &copy; {{ date('Y') }} {{ $client->company_name ?? $client->name }} — Powered by Dragon Bay ERP
        </div>
    </footer>

</div>
</body>
</html>
