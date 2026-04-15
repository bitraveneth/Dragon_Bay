@php
    use App\Helpers\MenuHelper;
    use App\Helpers\Permission;
    $__authUser = auth()->user();
    $__menuGroups = collect(MenuHelper::getMenuGroups())
        ->map(function ($group) use ($__authUser) {
            $items = collect($group['items'] ?? [])
                ->map(function ($item) use ($__authUser) {
                    $itemPermission = $item['permission'] ?? null;
                    $canSeeItem = empty($itemPermission) || Permission::can($__authUser, $itemPermission);

                    if (!$canSeeItem) {
                        return null;
                    }

                    if (! MenuHelper::isValidMenuPath($item['path'] ?? null)) {
                        return null;
                    }

                    if (isset($item['subItems']) && is_array($item['subItems'])) {
                        $item['subItems'] = collect($item['subItems'])
                            ->filter(function ($subItem) use ($__authUser) {
                                $permission = $subItem['permission'] ?? null;
                                $hasPermission = empty($permission) || Permission::can($__authUser, $permission);
                                if (!$hasPermission) {
                                    return false;
                                }

                                if (! MenuHelper::isValidMenuPath($subItem['path'] ?? null)) {
                                    return false;
                                }

                                return true;
                            })
                            ->values()
                            ->all();
                    }

                    return $item;
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
        ->values();

    $__menuSearchItems = $__menuGroups
        ->flatMap(function ($group) {
            return collect($group['items'])->flatMap(function ($item) use ($group) {
                $items = [];

                if (! empty($item['path']) && $item['path'] !== '#') {
                    $items[] = [
                        'label' => $item['name'],
                        'path' => $item['path'],
                        'group' => $group['title'],
                    ];
                }

                foreach ($item['subItems'] ?? [] as $sub) {
                    if (! empty($sub['path']) && $sub['path'] !== '#') {
                        $items[] = [
                            'label' => $sub['name'],
                            'path' => $sub['path'],
                            'group' => $group['title'],
                        ];
                    }
                }

                return $items;
            });
        })
        ->values();
@endphp

<header
    class="print-hidden sticky top-0 z-30 flex w-full border-b border-gray-200 bg-white/80 backdrop-blur-sm shadow-sm dark:border-gray-800 dark:bg-gray-900/80 xl:border-b"
    x-data="{
        isApplicationMenuOpen: false,
        toggleApplicationMenu() {
            this.isApplicationMenuOpen = !this.isApplicationMenuOpen;
        }
    }">
    <div class="flex min-w-0 grow flex-col items-center justify-between xl:flex-row xl:px-6">
        <div
            class="flex w-full min-w-0 items-center justify-between gap-2 border-b border-gray-200 px-3 py-2 dark:border-gray-800 sm:gap-3 xl:flex-1 xl:justify-normal xl:border-b-0 xl:px-0 lg:py-2.5">

            {{-- Desktop sidebar toggle --}}
            <button
                class="header-toggle-btn hidden h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 dark:border-gray-800 dark:text-gray-400 lg:h-10 lg:w-10 xl:flex"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded }"
                @click="$store.sidebar.toggleExpanded()" aria-label="Toggle sidebar">
                <svg x-show="!$store.sidebar.isMobileOpen" width="16" height="12" viewBox="0 0 16 12" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                        fill="currentColor" />
                </svg>
                <svg x-show="$store.sidebar.isMobileOpen" class="fill-current" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill="" />
                </svg>
            </button>

            {{-- Mobile sidebar toggle --}}
            <button
                class="header-toggle-btn flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 lg:h-10 lg:w-10 xl:hidden"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': $store.sidebar.isMobileOpen }"
                @click="$store.sidebar.toggleMobileOpen()" aria-label="Toggle mobile menu">
                <svg x-show="!$store.sidebar.isMobileOpen" width="16" height="12" viewBox="0 0 16 12" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                        fill="currentColor" />
                </svg>
                <svg x-show="$store.sidebar.isMobileOpen" class="fill-current" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill="" />
                </svg>
            </button>

            {{-- Logo (mobile) --}}
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 xl:hidden">
                @if(!empty($appLogoUrl))
                    <img src="{{ $appLogoUrl }}" alt="{{ $appBrandName }}" class="app-brand-logo rounded-xl border border-gray-200 object-cover dark:border-gray-700" />
                @else
                    <span class="app-brand-logo flex items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white">
                        {{ $appBrandInitials }}
                    </span>
                @endif
                @if(empty($appLogoUrl))
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $appBrandName }}</span>
                @endif
            </a>

            {{-- Application menu toggle (mobile) --}}
            <button @click="toggleApplicationMenu()"
                class="z-99999 flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 xl:hidden">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M5.99902 10.4951C6.82745 10.4951 7.49902 11.1667 7.49902 11.9951V12.0051C7.49902 12.8335 6.82745 13.5051 5.99902 13.5051C5.1706 13.5051 4.49902 12.8335 4.49902 12.0051V11.9951C4.49902 11.1667 5.1706 10.4951 5.99902 10.4951ZM17.999 10.4951C18.8275 10.4951 19.499 11.1667 19.499 11.9951V12.0051C19.499 12.8335 18.8275 13.5051 17.999 13.5051C17.1706 13.5051 16.499 12.8335 16.499 12.0051V11.9951C16.499 11.1667 17.1706 10.4951 17.999 10.4951ZM13.499 11.9951C13.499 11.1667 12.8275 10.4951 11.999 10.4951C11.1706 10.4951 10.499 11.1667 10.499 11.9951V12.0051C10.499 12.8335 11.1706 13.5051 11.999 13.5051C12.8275 13.5051 13.499 12.8335 13.499 12.0051V11.9951Z"
                        fill="currentColor" />
                </svg>
            </button>

            {{-- Search (desktop) --}}
            <div class="hidden xl:block">
                <form onsubmit="return false;">
                    <div class="relative" data-command-container>
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"
                                    fill="" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            placeholder="Search or type command..."
                            data-tour="command-search"
                            data-command-search
                            data-search-index='@json($__menuSearchItems)'
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-14 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-800 dark:bg-white/3 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[430px]" />
                        <button
                            type="button"
                            data-command-shortcut
                            class="absolute right-2.5 top-1/2 inline-flex -translate-y-1/2 items-center gap-0.5 rounded-lg border border-gray-200 bg-gray-50 px-[7px] py-[4.5px] text-xs -tracking-[0.2px] text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                            <span data-shortcut-mod>⌘</span>
                            <span> K </span>
                        </button>

                        {{-- Command palette results --}}
                        <div
                            data-command-results
                            class="absolute left-0 right-0 z-40 mt-2 hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-md dark:border-gray-800 dark:bg-gray-900">
                            {{-- Filled by resources/js/app.js --}}
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Application menu (mobile) + right-side actions (desktop) --}}
        <div :class="isApplicationMenuOpen ? 'flex' : 'hidden'"
            class="w-full min-w-0 items-center justify-between gap-3 px-4 py-3 shadow-theme-md xl:w-auto xl:shrink-0 xl:flex xl:justify-end xl:px-0 xl:py-0 xl:shadow-none">
            <div class="flex items-center gap-2 2xsm:gap-3">
                {{-- Clock --}}
                <div
                    class="relative"
                    x-data="{
                        nowDate: '',
                        nowTime: '',
                        dateFmt: null,
                        timeFmt: null,
                        updateClock() {
                            const d = new Date();
                            this.nowDate = this.dateFmt.format(d);
                            this.nowTime = this.timeFmt.format(d);
                        },
                        init() {
                            this.dateFmt = new Intl.DateTimeFormat(undefined, {
                                weekday: 'short',
                                day: '2-digit',
                                month: 'short',
                            });
                            this.timeFmt = new Intl.DateTimeFormat(undefined, {
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit',
                            });
                            this.updateClock();
                            setInterval(() => this.updateClock(), 1000);
                        }
                    }"
                >
                    <div
                        class="flex items-center gap-3 rounded-xl border border-gray-200/90 bg-gradient-to-r from-white to-gray-50 px-3 py-2 shadow-theme-xs dark:border-gray-800 dark:from-gray-900 dark:to-gray-800/70"
                        title="Current time"
                    >
                        <div class="leading-tight text-left">
                            <div class="text-[10px] font-medium uppercase tracking-wide text-[var(--color-app-text-light)]/70 dark:text-[var(--color-app-text-dark)]/70" x-text="nowDate"></div>
                            <div class="font-semibold text-[var(--color-app-text-light)] dark:text-[var(--color-app-text-dark)]" x-text="nowTime"></div>
                        </div>
                        <span class="h-2 w-2 rounded-full bg-success-500"></span>
                    </div>
                </div>
                {{-- Theme toggle --}}
                <button
                    class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-theme-xs transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                    @click="$store.theme.toggle()">
                    <svg class="hidden dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z"
                            fill="currentColor" />
                    </svg>
                    <svg class="dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z"
                            fill="currentColor" />
                    </svg>
                </button>

                {{-- Alerts dropdown (behaviour handled by resources/js/app.js via .header-alert / .header-alert-toggle) --}}
                @auth @if(auth()->user()->role !== 'client')
                    @php
                        $alertCollection = isset($headerAlerts)
                            ? collect($headerAlerts)
                                ->values()
                                ->map(function ($alert) {
                                    return is_array($alert)
                                        ? $alert
                                        : ['message' => (string) $alert, 'variant' => 'error'];
                                })
                            : collect();
                        $alertCount = isset($headerAlertCount) ? (int) $headerAlertCount : $alertCollection->count();
                        $trayAlerts = $alertCollection->take(10);
                    @endphp
                    <div class="header-alert js-header-alert-root relative" data-fetch-url="{{ route('admin.notifications.header-data') }}">
                        {{-- Trigger button --}}
                        <button type="button"
                            data-tour="header-alerts"
                            class="header-alert-toggle relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-theme-xs transition-colors hover:border-brand-300 hover:bg-brand-50 hover:text-brand-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-brand-700 dark:hover:bg-brand-500/10 dark:hover:text-brand-300"
                            title="View notifications">
                            {{-- Bell icon --}}
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <path d="M12 3.5C9.51472 3.5 7.5 5.51472 7.5 8V10.2344C7.5 11.0897 7.21486 11.9207 6.68945 12.5957L5.73047 13.8291C5.20006 14.5111 5.68643 15.5 6.55078 15.5H17.4492C18.3136 15.5 18.7999 14.5111 18.2695 13.8291L17.3105 12.5957C16.7851 11.9207 16.5 11.0897 16.5 10.2344V8C16.5 5.51472 14.4853 3.5 12 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.5 17.5C9.80616 18.3734 10.6383 19 11.625 19H12.375C13.3617 19 14.1938 18.3734 14.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>

                            {{-- Counter badge --}}
                            <span
                                class="js-header-alert-count absolute -right-0.5 -top-0.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-error-500 px-1 text-[10px] font-semibold text-white {{ $alertCount ? '' : 'hidden' }}">
                                {{ $alertCount }}
                            </span>
                        </button>

                        {{-- Dropdown tray --}}
                        <div
                            class="header-alert-menu fixed inset-x-4 top-20 z-40 flex max-h-[70vh] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white text-sm shadow-theme-lg dark:border-gray-800 dark:bg-gray-900
                                   xl:absolute xl:inset-x-auto xl:right-0 xl:top-full xl:mt-3 xl:w-[27rem] xl:max-h-none">
                            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Notifications
                                    </h3>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400"><span class="js-header-alert-active-count">{{ $alertCount }}</span> active</p>
                                </div>
                                <button type="button"
                                        class="header-alert-menu-close flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                                        aria-label="Close alerts">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 6L14 14M14 6L6 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>

                            @if ($alertCount)
                                <ul class="js-header-alert-list max-h-[430px] space-y-2 overflow-y-auto p-4 text-[13px] text-gray-700 dark:text-gray-300">
                                    @foreach ($trayAlerts as $alert)
                                        @php
                                            $message = $alert['message'] ?? '';
                                            $variant = $alert['variant'] ?? 'error';
                                            $alertStyles = [
                                                'error' => [
                                                    'ring' => 'border-error-300/70 bg-error-50/70 dark:border-error-700/60 dark:bg-error-500/10',
                                                    'dot' => 'bg-error-500',
                                                    'label' => 'Error',
                                                    'labelClass' => 'text-error-700 dark:text-error-300',
                                                ],
                                                'warning' => [
                                                    'ring' => 'border-warning-300/70 bg-warning-50/70 dark:border-warning-700/60 dark:bg-warning-500/10',
                                                    'dot' => 'bg-warning-500',
                                                    'label' => 'Warning',
                                                    'labelClass' => 'text-warning-700 dark:text-warning-300',
                                                ],
                                                'success' => [
                                                    'ring' => 'border-success-300/70 bg-success-50/70 dark:border-success-700/60 dark:bg-success-500/10',
                                                    'dot' => 'bg-success-500',
                                                    'label' => 'Success',
                                                    'labelClass' => 'text-success-700 dark:text-success-300',
                                                ],
                                                'info' => [
                                                    'ring' => 'border-brand-300/70 bg-brand-50/70 dark:border-brand-700/60 dark:bg-brand-500/10',
                                                    'dot' => 'bg-brand-500',
                                                    'label' => 'Info',
                                                    'labelClass' => 'text-brand-700 dark:text-brand-300',
                                                ],
                                            ][$variant] ?? [
                                                'ring' => 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50',
                                                'dot' => 'bg-gray-400',
                                                'label' => 'Notice',
                                                'labelClass' => 'text-gray-600 dark:text-gray-300',
                                            ];
                                            $sourceLabel = $alert['source'] ?? $alertStyles['label'];
                                            $notificationId = $alert['id'] ?? null;
                                        @endphp
                                        <li class="js-header-alert-item">
                                            <div class="rounded-xl border px-3 py-2.5 {{ $alertStyles['ring'] }}">
                                                <div class="mb-1 flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-2 w-2 rounded-full {{ $alertStyles['dot'] }}"></span>
                                                        <span class="text-[11px] font-semibold uppercase tracking-wide {{ $alertStyles['labelClass'] }}">{{ $sourceLabel }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Now</span>
                                                        @if($notificationId && Route::has('admin.notifications.mark-read'))
                                                            <form action="{{ route('admin.notifications.mark-read', $notificationId) }}" method="POST" class="header-mark-read-form">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="inline-flex items-center rounded-md border border-gray-300/70 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                                                                    Read
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                                <p class="leading-5 text-gray-800 dark:text-gray-100">{{ $message }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="js-header-alert-empty p-4">
                                    <p class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-[13px] text-gray-500 dark:border-gray-700 dark:bg-white/5 dark:text-gray-400">
                                        No current alerts.
                                    </p>
                                </div>
                            @endif

                            <div class="border-t border-gray-100 p-3 dark:border-gray-800">
                                <a href="{{ route('admin.notifications.index') }}"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>
                @endif @endauth
            </div>

            {{-- User dropdown --}}
            @auth
                @php
                    $user = auth()->user();
                    $user->loadMissing('employee');
                    $labelSource = $user->name ?: $user->email;
                    $initials = strtoupper(mb_substr($labelSource, 0, 2));
                    $role = $user->role ?? 'employee';
                    $roleLabels = [
                        'super_admin' => 'SUPER ADMIN',
                        'admin' => 'ADMIN',
                        'client' => 'CLIENT',
                        'purchase_executive' => 'PURCHASE EXECUTIVE',
                        'warehouse_officer' => 'WAREHOUSE OFFICER',
                        'production_officer' => 'PRODUCTION OFFICER',
                        'sales_officer' => 'SALES OFFICER',
                        'delivery_coordinator' => 'DELIVERY COORDINATOR',
                        'accounts_officer' => 'ACCOUNTS OFFICER',
                        'qc_officer' => 'QC OFFICER',
                    ];
                    $avatarUrl = $user->employee && $user->employee->photo_path
                        ? asset('storage/' . $user->employee->photo_path)
                        : null;
                @endphp
                <div class="header-user relative">
                    {{-- Trigger button (behaviour handled by setupDropdown in app.js) --}}
                    <button type="button"
                        data-tour="header-user"
                        class="header-user-toggle flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2.5 py-1.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white overflow-hidden">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="Profile photo" class="h-full w-full object-cover">
                            @else
                                {{ $initials }}
                            @endif
                        </span>
                        <span class="hidden text-left xl:block">
                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $user->name ?? $user->email }}
                            </span>
                        </span>
                        <svg class="ml-1 h-4 w-4 text-gray-400 xl:block hidden" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 8L10 12L14 8" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    {{-- Dropdown tray --}}
                    <div
                        class="header-user-menu absolute right-0 top-full mt-3 w-64 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-3 flex items-center gap-3 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white overflow-hidden">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Profile photo" class="h-full w-full object-cover">
                                @else
                                    {{ $initials }}
                                @endif
                            </span>
                            <div class="space-y-0.5">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $user->name ?? $user->email }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->email }}
                                </div>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $roleLabels[$role] ?? strtoupper(str_replace('_', ' ', $role)) }}
                                </span>
                            </div>
                        </div>

                        <ul class="mb-2 space-y-1 text-[13px] text-gray-700 dark:text-gray-300">
                            <li>
                                @if(auth()->user()->role === 'client')
                                    <a href="{{ route('portal.profile') }}"
                                        class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5">
                                        <span>My profile</span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.profile.edit') }}"
                                        class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5">
                                        <span>Edit profile</span>
                                    </a>
                                @endif
                            </li>
                        </ul>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center justify-center rounded-lg bg-error-50 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-100 dark:bg-error-500/10 dark:text-error-300 dark:hover:bg-error-500/20">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</header>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function variantStyles(variant) {
        const map = {
            error: {
                ring: 'border-error-300/70 bg-error-50/70 dark:border-error-700/60 dark:bg-error-500/10',
                dot: 'bg-error-500',
                label: 'text-error-700 dark:text-error-300',
            },
            warning: {
                ring: 'border-warning-300/70 bg-warning-50/70 dark:border-warning-700/60 dark:bg-warning-500/10',
                dot: 'bg-warning-500',
                label: 'text-warning-700 dark:text-warning-300',
            },
            success: {
                ring: 'border-success-300/70 bg-success-50/70 dark:border-success-700/60 dark:bg-success-500/10',
                dot: 'bg-success-500',
                label: 'text-success-700 dark:text-success-300',
            },
            info: {
                ring: 'border-brand-300/70 bg-brand-50/70 dark:border-brand-700/60 dark:bg-brand-500/10',
                dot: 'bg-brand-500',
                label: 'text-brand-700 dark:text-brand-300',
            },
        };

        return map[variant] || {
            ring: 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/50',
            dot: 'bg-gray-400',
            label: 'text-gray-600 dark:text-gray-300',
        };
    }

    function updateHeaderCounts(unreadCount) {
        document.querySelectorAll('.js-header-alert-count').forEach((badge) => {
            badge.textContent = String(unreadCount);
            badge.classList.toggle('hidden', unreadCount === 0);
        });

        document.querySelectorAll('.js-header-alert-active-count').forEach((text) => {
            text.textContent = String(unreadCount);
        });
    }

    function renderHeaderAlerts(root, alerts, unreadCount) {
        const menu = root.querySelector('.header-alert-menu');
        if (!menu) {
            return;
        }

        updateHeaderCounts(unreadCount);

        const footer = menu.querySelector('.border-t');
        let list = menu.querySelector('.js-header-alert-list');
        let empty = menu.querySelector('.js-header-alert-empty');

        if (alerts.length > 0) {
            if (!list) {
                list = document.createElement('ul');
                list.className = 'js-header-alert-list max-h-[430px] space-y-2 overflow-y-auto p-4 text-[13px] text-gray-700 dark:text-gray-300';
                if (footer) {
                    menu.insertBefore(list, footer);
                } else {
                    menu.appendChild(list);
                }
            }

            list.innerHTML = alerts.map((alert) => {
                const styles = variantStyles(alert.variant || 'info');
                const source = escapeHtml(alert.source || 'System');
                const message = escapeHtml(alert.message || '');
                const timeLabel = escapeHtml(alert.time_label || 'Now');
                const readUrl = escapeHtml(alert.read_url || '#');

                return `
                    <li class="js-header-alert-item">
                        <div class="rounded-xl border px-3 py-2.5 ${styles.ring}">
                            <div class="mb-1 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full ${styles.dot}"></span>
                                    <span class="text-[11px] font-semibold uppercase tracking-wide ${styles.label}">${source}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">${timeLabel}</span>
                                    <form action="${readUrl}" method="POST" class="header-mark-read-form">
                                        <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                                        <button type="submit"
                                            class="inline-flex items-center rounded-md border border-gray-300/70 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                                            Read
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="leading-5 text-gray-800 dark:text-gray-100">${message}</p>
                        </div>
                    </li>
                `;
            }).join('');

            if (empty) {
                empty.remove();
            }
            return;
        }

        if (list) {
            list.remove();
        }

        if (!empty) {
            empty = document.createElement('div');
            empty.className = 'js-header-alert-empty p-4';
            empty.innerHTML = '<p class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-[13px] text-gray-500 dark:border-gray-700 dark:bg-white/5 dark:text-gray-400">No current alerts.</p>';
            if (footer) {
                menu.insertBefore(empty, footer);
            } else {
                menu.appendChild(empty);
            }
        }
    }

    function loadHeaderAlerts(root) {
        const url = root.dataset.fetchUrl;
        if (!url || root.dataset.loadingAjax === '1') {
            return Promise.resolve();
        }

        root.dataset.loadingAjax = '1';
        return fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data || data.success !== true) {
                    return;
                }
                const alerts = Array.isArray(data.alerts) ? data.alerts : [];
                const unreadCount = Number.isInteger(data.unread_count) ? data.unread_count : alerts.length;
                renderHeaderAlerts(root, alerts, unreadCount);
            })
            .catch(() => {})
            .finally(() => {
                root.dataset.loadingAjax = '0';
            });
    }

    document.querySelectorAll('.js-header-alert-root').forEach((root) => {
        loadHeaderAlerts(root);

        const toggle = root.querySelector('.header-alert-toggle');
        if (toggle) {
            toggle.addEventListener('click', () => {
                loadHeaderAlerts(root);
            });
        }

        const menu = root.querySelector('.header-alert-menu');
        if (menu) {
            menu.addEventListener('submit', (event) => {
                const form = event.target.closest('.header-mark-read-form');
                if (!form) {
                    return;
                }
                event.preventDefault();

                const formData = new FormData(form);
                fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (!data || data.success !== true) {
                            return;
                        }
                        const unreadCount = Number.isInteger(data.unread_count) ? data.unread_count : 0;
                        updateHeaderCounts(unreadCount);
                        loadHeaderAlerts(root);
                    })
                    .catch(() => {
                        form.submit();
                    });
            });
        }

        setInterval(() => {
            if (document.hidden) {
                return;
            }
            loadHeaderAlerts(root);
        }, 60000);
    });
});
</script>
@endpush
