@php
    use App\Helpers\MenuHelper;
    use App\Helpers\Permission;
    $authUser = auth()->user();
    $menuGroups = ($authUser?->role === 'client')
        ? MenuHelper::getClientMenu()
        : MenuHelper::getMenuGroups();
    $isValidPath = fn (?string $path): bool => MenuHelper::isValidMenuPath($path);

    $menuGroups = collect($menuGroups)
        ->map(function ($group) use ($authUser, $isValidPath) {
            $items = collect($group['items'] ?? [])
                ->map(function ($item) use ($authUser, $isValidPath) {
                    $itemPermission = $item['permission'] ?? null;
                    $canSeeItem = empty($itemPermission) || Permission::can($authUser, $itemPermission);

                    if (!empty($item['subItems']) && is_array($item['subItems'])) {
                        // Enforce parent-level permission for dropdown sections.
                        if (!$canSeeItem) {
                            return null;
                        }

                        $filteredSubItems = collect($item['subItems'])
                            ->filter(function ($subItem) use ($authUser, $isValidPath) {
                                $permission = $subItem['permission'] ?? null;
                                $hasPermission = empty($permission) || Permission::can($authUser, $permission);
                                $path = $subItem['path'] ?? null;
                                return $hasPermission && $isValidPath($path);
                            })
                            ->values()
                            ->all();

                        if (empty($filteredSubItems)) {
                            return null;
                        }

                        $item['subItems'] = $filteredSubItems;

                        return $item;
                    }

                    $path = $item['path'] ?? null;
                    return ($canSeeItem && $isValidPath($path)) ? $item : null;
                })
                ->filter()
                ->values()
                ->all();

            return [
                'title' => $group['title'] ?? '',
                'items' => $items,
            ];
        })
        ->filter(fn ($group) => !empty($group['items']))
        ->values()
        ->all();

    $currentPath = '/' . trim(request()->path(), '/');
    $currentPath = rtrim($currentPath, '/') ?: '/';
    $matchesCurrent = fn (?string $path): bool => MenuHelper::matchesCurrentPath($path, $currentPath);
@endphp

<aside id="sidebar"
    class="print-hidden fixed flex flex-col mt-0 top-0 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-[99999] border-r border-gray-200"
    x-data="{
        isSidebarVisible() {
            return $store.sidebar.isExpanded || $store.sidebar.isMobileOpen;
        }
    }"
    :class="{
        'w-[290px]': isSidebarVisible(),
        'w-[90px]': !isSidebarVisible(),
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }">
    <!-- Logo Section (circle initials) -->
    <div class="px-5 pt-5 pb-4 flex"
         :class="!isSidebarVisible()
            ? 'xl:justify-center'
            : 'justify-start'">
        <a href="{{ route('admin.dashboard') }}" data-tour="sidebar-brand" class="flex items-center gap-4">
            @if(!empty($appLogoUrl))
                <img src="{{ $appLogoUrl }}" alt="{{ $appBrandName }}"
                    class="h-12 rounded-xl bg-white object-contain dark:bg-gray-900"
                    :class="isSidebarVisible() ? 'w-20 px-1.5' : 'w-12 px-1'" />
            @else
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-500 text-base font-semibold text-white">
                    {{ $appBrandInitials }}
                </div>
            @endif
            @if(empty($appLogoUrl))
                <div class="flex flex-col"
                     x-show="isSidebarVisible()">
                    <span class="text-[2rem] font-bold leading-none tracking-tight text-gray-900 dark:text-white">{{ $appBrandName }}</span>
                </div>
            @endif
        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 min-h-0 flex flex-col overflow-y-auto duration-300 ease-linear custom-scrollbar">
        <nav class="mb-6 px-5">
            <div class="flex flex-col gap-4">
                @foreach ($menuGroups as $groupIndex => $menuGroup)
                    <div class="{{ $groupIndex > 0 ? 'pt-4 mt-2 border-t border-gray-100 dark:border-gray-800' : '' }}">
                        <!-- Menu Group Title -->
                        <h2 class="mb-4 text-xs uppercase flex leading-[20px] text-gray-400"
                            :class="!isSidebarVisible() ?
                            'lg:justify-center' : 'justify-start'">
                            <template
                                x-if="isSidebarVisible()">
                                <span>{{ $menuGroup['title'] }}</span>
                            </template>
                            <template x-if="!isSidebarVisible()">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z" fill="currentColor"/>
                                </svg>
                            </template>
                        </h2>

                        <!-- Menu Items -->
                        <ul class="flex flex-col gap-1">
                            @foreach ($menuGroup['items'] as $itemIndex => $item)
                                @php
                                    $itemPath = $item['path'] ?? null;
                                    $subItems = $item['subItems'] ?? [];
                                    $hasSubItems = !empty($subItems);
                                    $tourKey = null;
                                    if ($itemPath === '/admin') {
                                        $tourKey = 'sidebar-dashboard';
                                    } elseif ($itemPath === '/admin/products' || (($subItems[0]['path'] ?? null) === '/admin/products')) {
                                        $tourKey = 'sidebar-products';
                                    }
                                    $activeSubItemPath = collect($subItems)
                                        ->filter(fn ($subItem) => $matchesCurrent($subItem['path'] ?? null))
                                        ->sortByDesc(fn ($subItem) => strlen((string) ($subItem['path'] ?? '')))
                                        ->pluck('path')
                                        ->first();
                                    $subtreeIsActive = !empty($activeSubItemPath);
                                    $itemIsActive = !$subtreeIsActive && $matchesCurrent($itemPath);
                                @endphp
                                <li @if($hasSubItems) x-data="{ open: {{ ($itemIsActive || $subtreeIsActive) ? 'true' : 'false' }} }" @endif>
                                    @if ($hasSubItems)
                                        <!-- Menu Item with Submenu -->
                                        @php
                                            $parentTarget = (!empty($item['path']) && $item['path'] !== '#')
                                                ? $item['path']
                                                : ($item['subItems'][0]['path'] ?? '#');
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $parentTarget }}"
                                                @if($tourKey) data-tour="{{ $tourKey }}" @endif
                                                @class([
                                                    'menu-item group min-w-0 flex-1',
                                                    'menu-item-active' => $itemIsActive || $subtreeIsActive,
                                                    'menu-item-inactive' => !$itemIsActive && !$subtreeIsActive,
                                                ])
                                                :class="!isSidebarVisible() ? 'xl:justify-center' : 'xl:justify-start'">

                                                <!-- Icon -->
                                                <span @class([
                                                    'menu-item-icon',
                                                    'menu-item-icon-active' => $itemIsActive || $subtreeIsActive,
                                                    'menu-item-icon-inactive' => !$itemIsActive && !$subtreeIsActive,
                                                ])>
                                                    {!! MenuHelper::getIconSvg($item['icon']) !!}
                                                </span>

                                                <!-- Text -->
                                                <span
                                                    x-show="isSidebarVisible()"
                                                    class="menu-item-text flex items-center gap-2">
                                                    {{ $item['name'] }}
                                                    @if (!empty($item['new']))
                                                        <span class="ml-2 inline-flex items-center rounded-full bg-success-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.08em] text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                                            new
                                                        </span>
                                                    @endif
                                                </span>
                                            </a>

                                            <button type="button"
                                                x-show="isSidebarVisible()"
                                                @click.prevent.stop="open = !open"
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                                                :class="{
                                                    'bg-brand-50 text-brand-500 dark:bg-brand-500/[0.14] dark:text-brand-300': open
                                                }"
                                                aria-label="Toggle {{ $item['name'] }} submenu">
                                                <svg class="h-5 w-5 transition-transform duration-200"
                                                    :class="{
                                                        'rotate-180': open
                                                    }"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Submenu -->
                                        <div x-show="open && isSidebarVisible()"
                                             x-transition:enter="transition-all ease-out duration-200"
                                             x-transition:enter-start="opacity-0 max-h-0 -translate-y-1"
                                             x-transition:enter-end="opacity-100 max-h-64 translate-y-0"
                                             x-transition:leave="transition-all ease-in duration-150"
                                             x-transition:leave-start="opacity-100 max-h-64 translate-y-0"
                                             x-transition:leave-end="opacity-0 max-h-0 -translate-y-1"
                                                class="overflow-hidden">
                                            <ul class="mt-2 space-y-1 ml-9">
                                                @foreach ($item['subItems'] as $subItem)
                                                    @php $subItemIsActive = ($subItem['path'] ?? null) === $activeSubItemPath; @endphp
                                                    <li>
                                                        <a href="{{ $subItem['path'] }}"
                                                            @class([
                                                                'menu-dropdown-item',
                                                                'menu-dropdown-item-active' => $subItemIsActive,
                                                                'menu-dropdown-item-inactive' => !$subItemIsActive,
                                                            ])>
                                                            {{ $subItem['name'] }}
                                                            <span class="flex items-center gap-1 ml-auto">
                                                                @if (!empty($subItem['new']))
                                                                    <span
                                                                        @class([
                                                                            'menu-dropdown-badge',
                                                                            'menu-dropdown-badge-active' => $subItemIsActive,
                                                                            'menu-dropdown-badge-inactive' => !$subItemIsActive,
                                                                        ])>
                                                                        new
                                                                    </span>
                                                                @endif
                                                                @if (!empty($subItem['pro']))
                                                                    <span
                                                                        @class([
                                                                            'menu-dropdown-badge-pro',
                                                                            'menu-dropdown-badge-pro-active' => $subItemIsActive,
                                                                            'menu-dropdown-badge-pro-inactive' => !$subItemIsActive,
                                                                        ])>
                                                                        pro
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <!-- Simple Menu Item -->
                                        <a href="{{ $item['path'] }}"
                                            @if($tourKey) data-tour="{{ $tourKey }}" @endif
                                            @class([
                                                'menu-item group',
                                                'menu-item-active' => $itemIsActive,
                                                'menu-item-inactive' => !$itemIsActive,
                                            ])
                                            :class="!isSidebarVisible() ? 'xl:justify-center' : 'justify-start'">

                                            <!-- Icon -->
                                            <span
                                                @class([
                                                    'menu-item-icon',
                                                    'menu-item-icon-active' => $itemIsActive,
                                                    'menu-item-icon-inactive' => !$itemIsActive,
                                                ])>
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <!-- Text -->
                                            <span
                                                x-show="isSidebarVisible()"
                                                class="menu-item-text flex items-center gap-2">
                                                {{ $item['name'] }}
                                                @if (!empty($item['new']))
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-brand-500 text-white">
                                                        new
                                                    </span>
                                                @endif
                                            </span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>

        {{-- Optional sidebar widget if present --}}
        @if (View::exists('layouts.sidebar-widget'))
            <div x-data x-show="isSidebarVisible()" x-transition class="mt-auto">
                @include('layouts.sidebar-widget')
            </div>
        @endif

    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen" @click="$store.sidebar.setMobileOpen(false)"
    class="fixed z-50 h-screen w-full bg-gray-900/50"></div>
