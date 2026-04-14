<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Client Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-white">
    <div class="min-h-screen">
        <header class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ route('portal.dashboard') }}" class="text-xl font-semibold">Client Portal</a>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $client->name }}</p>
                </div>
                <nav class="flex flex-wrap gap-2 text-sm">
                    @foreach([
                        'portal.dashboard' => 'Dashboard',
                        'portal.shipments.index' => 'Shipments',
                        'portal.orders.index' => 'Orders',
                        'portal.invoices.index' => 'Invoices',
                        'portal.deliveries.index' => 'Deliveries',
                        'portal.statement' => 'Statement',
                        'portal.profile' => 'Profile',
                    ] as $route => $label)
                        <a href="{{ route($route) }}"
                           class="rounded-lg px-3 py-2 {{ request()->routeIs($route) ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'border border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-sm font-medium">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8">
            @if(session('status'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-950/40 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
