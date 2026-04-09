<header class="admin-header">
    <div class="brand">
        <span>{{ $appBrandName ?? config('app.name', 'ERP') }}</span>
        <small>Admin panel</small>
    </div>
    <nav class="header-links">
        @guest
            <a href="{{ route('login') }}">Sign in</a>
        @endguest
        @auth
            @php
                $alertCollection = isset($headerAlerts) ? collect($headerAlerts)->values() : collect();
                $alertCount = isset($headerAlertCount) ? (int) $headerAlertCount : $alertCollection->count();
                $trayAlerts = $alertCollection->take(10);
                $user = auth()->user();
                $labelSource = $user->name ?: $user->email;
                $initials = strtoupper(mb_substr($labelSource, 0, 2));
            @endphp
            <div class="header-alert">
                <button type="button"
                        class="header-notify header-alert-toggle {{ $alertCount ? '' : 'header-notify-empty' }}"
                        title="View notifications">
                    <span class="header-notify-icon" aria-hidden="true">🔔</span>
                    <span class="header-notify-label">Notifications</span>
                    <span class="header-notify-count">{{ $alertCount }}</span>
                </button>
                <div class="header-alert-menu">
                    <div class="header-alert-title">Notifications</div>
                    @if($alertCount)
                        <ul class="header-alert-list">
                            @foreach($trayAlerts as $alert)
                                @php
                                    $message = is_array($alert) ? ($alert['message'] ?? '') : $alert;
                                @endphp
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="header-alert-empty">No current alerts.</p>
                    @endif
                    <a href="{{ route('admin.notifications.index') }}" class="header-alert-link">
                        View all notifications
                    </a>
                </div>
            </div>
            <div class="header-user">
                <button type="button" class="header-user-toggle">
                    <span class="header-user-initials">{{ $initials }}</span>
                </button>
                <div class="header-user-menu">
                    <div class="header-user-name">{{ $user->name ?? $user->email }}</div>
                    <div class="header-user-email">{{ $user->email }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="header-user-logout">Sign out</button>
                    </form>
                </div>
            </div>
        @endauth
    </nav>
</header>
