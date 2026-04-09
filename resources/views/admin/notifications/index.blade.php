@extends('layouts.app')

@section('content')
@php
    // Normalise variables early so header counts are always defined.
    $alerts = $alerts ?? [];
    $userNotifications = $userNotifications ?? collect();
    $systemAlertCount = count($alerts);
    $hasAlerts = $systemAlertCount > 0;
    $hasNotifications = $userNotifications->isNotEmpty();
    $totalUnread = $userNotifications->whereNull('read_at')->count();
    $totalRead = $userNotifications->whereNotNull('read_at')->count();
    $unreadSystemAlertCount = collect($alerts)
        ->filter(function ($alert) {
            if (!is_array($alert)) {
                return false;
            }
            return !($alert['is_read'] ?? false);
        })
        ->count();
    $groupOrder = ['Today' => 0, 'Yesterday' => 1, 'Earlier' => 2];
    $resolveGroupLabel = function ($dateValue) {
        if (!$dateValue) {
            return 'Earlier';
        }
        $date = \Carbon\Carbon::parse($dateValue);
        if ($date->isToday()) {
            return 'Today';
        }
        if ($date->isYesterday()) {
            return 'Yesterday';
        }
        return 'Earlier';
    };
    $groupAndSort = function ($collection, $dateResolver) use ($resolveGroupLabel, $groupOrder) {
        return collect($collection)
            ->groupBy(function ($item) use ($dateResolver, $resolveGroupLabel) {
                return $resolveGroupLabel($dateResolver($item));
            })
            ->sortBy(function ($items, $label) use ($groupOrder) {
                return $groupOrder[$label] ?? 99;
            });
    };
    $groupedSystemAlerts = $groupAndSort($alerts, function ($alert) {
        return is_array($alert) ? ($alert['created_at'] ?? null) : null;
    });
    $groupedUserNotifications = $groupAndSort($userNotifications, function ($notification) {
        return $notification->created_at ?? null;
    });
    $serverUnreadCount = isset($totalUnreadCount)
        ? (int) $totalUnreadCount
        : ($unreadSystemAlertCount + $totalUnread);
@endphp
<div id="notifications-page-root" data-server-unread-count="{{ $serverUnreadCount }}" class="max-w-6xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Notifications
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            System alerts and updates across {{ $appBrandName }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($userNotifications) && $userNotifications->isNotEmpty())
        <div class="flex flex-wrap items-center justify-start gap-2 sm:justify-end">
            <button type="button" class="js-notifications-refresh inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white/80 text-gray-700 shadow-xs transition-all duration-200 hover:bg-white hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900"
                    aria-label="Refresh notifications"
                    title="Refresh notifications">
                <svg class="h-4 w-4 js-refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            
        </div>
        @endif
    </div>

    @if(!$hasAlerts && !$hasNotifications)
        <!-- Empty State - Modern All Clear -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm px-6 py-10 text-center shadow-xl dark:border-gray-800 dark:bg-gray-900/50 sm:p-12 lg:p-16">
            <!-- Decorative background -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-success-100 to-success-50 opacity-20 dark:from-success-900 dark:to-success-800 blur-3xl"></div>
            
            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-success-100 to-success-50 dark:from-success-900/30 dark:to-success-800/30">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">All Clear</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Your notification center is quiet. No system alerts or notifications require your attention at this moment.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <button type="button" class="js-notifications-refresh inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            aria-label="Refresh notifications"
                            title="Refresh notifications">
                        <svg class="h-4 w-4 js-refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="space-y-10">
            <!-- System Alerts -->
            @if($hasAlerts)
                <div class="rounded-2xl border border-gray-200/80 bg-white/90 p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900/80 sm:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0018 9.75v-.7V9a6 6 0 10-12 0v.05-.05v.7a8.967 8.967 0 00-2.311 6.022 23.848 23.848 0 005.454 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">System Alerts</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><span id="system-unread-section-count">{{ $unreadSystemAlertCount }}</span> unread</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        @foreach($groupedSystemAlerts as $groupLabel => $groupAlerts)
                            <div class="space-y-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $groupLabel }}</p>
                                <div class="grid gap-3">
                                    @foreach($groupAlerts as $index => $alert)
                                        @php
                                            $message = is_array($alert) ? ($alert['message'] ?? '') : $alert;
                                            $variant = is_array($alert) ? ($alert['variant'] ?? 'error') : 'error';
                                            $alertKey = is_array($alert) ? ($alert['key'] ?? null) : null;
                                            $alertStyles = [
                                    'error' => [
                                        'ring' => 'border-error-300/60 bg-error-500/10 dark:border-error-700/60 dark:bg-error-500/12',
                                        'dot' => 'bg-error-500',
                                        'label' => 'Error',
                                        'labelClass' => 'text-error-700 dark:text-error-300',
                                    ],
                                    'warning' => [
                                        'ring' => 'border-warning-300/60 bg-warning-500/10 dark:border-warning-700/60 dark:bg-warning-500/12',
                                        'dot' => 'bg-warning-500',
                                        'label' => 'Warning',
                                        'labelClass' => 'text-warning-700 dark:text-warning-300',
                                    ],
                                    'success' => [
                                        'ring' => 'border-success-300/60 bg-success-500/10 dark:border-success-700/60 dark:bg-success-500/12',
                                        'dot' => 'bg-success-500',
                                        'label' => 'Success',
                                        'labelClass' => 'text-success-700 dark:text-success-300',
                                    ],
                                    'info' => [
                                        'ring' => 'border-brand-300/60 bg-brand-500/10 dark:border-brand-700/60 dark:bg-brand-500/12',
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
                                $sourceLabel = is_array($alert)
                                    ? ($alert['source'] ?? $alertStyles['label'])
                                    : $alertStyles['label'];
                                $isUnreadSystemAlert = !((is_array($alert) && ($alert['is_read'] ?? false)));
                                $systemAlertCardClass = $isUnreadSystemAlert
                                    ? $alertStyles['ring']
                                    : 'border-gray-200 bg-white/60 dark:border-gray-700 dark:bg-gray-900/40';
                                $alertCreatedAt = is_array($alert) ? ($alert['created_at'] ?? null) : null;
                                $alertTime = $alertCreatedAt
                                    ? \Carbon\Carbon::parse($alertCreatedAt)->format('d M Y, H:i')
                                    : now()->format('d M Y, H:i');
                                $notificationId = is_array($alert) ? ($alert['id'] ?? null) : null;
                                        @endphp
                                        <div class="system-alert-card rounded-xl border px-4 py-3 {{ $systemAlertCardClass }}"
                                            data-read="{{ $isUnreadSystemAlert ? 'false' : 'true' }}"
                                            data-unread-class="{{ $alertStyles['ring'] }}"
                                            data-read-class="border-gray-200 bg-white/60 dark:border-gray-700 dark:bg-gray-900/40"
                                            data-unread-dot="{{ $alertStyles['dot'] }}"
                                            data-unread-label-class="{{ $alertStyles['labelClass'] }}">
                                            <div class="mb-1.5 flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="system-alert-dot h-2 w-2 rounded-full {{ $isUnreadSystemAlert ? $alertStyles['dot'] : 'bg-gray-400' }}"></span>
                                                    <span class="system-alert-label text-[11px] font-semibold uppercase tracking-wide {{ $isUnreadSystemAlert ? $alertStyles['labelClass'] : 'text-gray-500 dark:text-gray-400' }}">{{ $sourceLabel }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $alertTime }}</span>
                                                    @if($notificationId && Route::has('admin.notifications.mark-read'))
                                                        <form action="{{ route('admin.notifications.mark-read', $notificationId) }}" method="POST" class="system-mark-read-form {{ $isUnreadSystemAlert ? '' : 'hidden' }}">
                                                            @csrf
                                                            <button type="submit"
                                                                class="system-mark-read-btn inline-flex items-center rounded-md border border-gray-300/70 px-2 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                                                                title="Mark as read">
                                                                Mark as read
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="system-alert-message leading-5 {{ $isUnreadSystemAlert ? 'text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-300' }}">{{ $message }}</p>
                                        </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- User Notifications Section - Modern Redesign with Read/Unread -->
            @if($hasNotifications)
                <div class="space-y-6">
                    <div class="flex justify-start md:justify-end">
                        <div class="flex flex-wrap items-center gap-2 rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
                            <button type="button" 
                                    id="show-all-btn"
                                    class="px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 shadow-sm transition-all">
                                All
                            </button>
                            <button type="button" 
                                    id="show-unread-btn"
                                    class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all">
                                Unread
                            </button>
                            <button type="button" 
                                    id="show-read-btn"
                                    class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all">
                                Read
                            </button>
                        </div>
                    </div>

                    <div id="notifications-container" class="space-y-5">
                        @foreach($groupedUserNotifications as $groupLabel => $groupNotifications)
                            <div class="space-y-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $groupLabel }}</p>
                                <div class="grid gap-4">
                        @foreach($groupNotifications as $notification)
                            @php
                                $data = $notification->data;
                                $title = $data['sender_name']
                                    ?? $data['source']
                                    ?? (($data['title'] ?? '') === 'System alert' ? 'System' : ($data['title'] ?? 'New Notification'));
                                $message = $data['message'] ?? '';
                                $type = $data['type'] ?? 'info';
                                $link = $data['link'] ?? null;
                                $isRead = !is_null($notification->read_at);
                                
                                $typeConfig = [
                                    'success' => [
                                        'gradient' => 'from-success-500 to-success-600',
                                        'light' => 'bg-success-50 dark:bg-success-950/30',
                                        'border' => 'border-success-200 dark:border-success-900',
                                        'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'badge' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300'
                                    ],
                                    'warning' => [
                                        'gradient' => 'from-orange-500 to-orange-600',
                                        'light' => 'bg-orange-50 dark:bg-orange-950/30',
                                        'border' => 'border-orange-200 dark:border-orange-900',
                                        'icon' => 'M12 9v3.75m-1.5-2.25h3M12 15.75h.007v.008H12v-.008z',
                                        'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300'
                                    ],
                                    'error' => [
                                        'gradient' => 'from-error-500 to-error-600',
                                        'light' => 'bg-error-50 dark:bg-error-950/30',
                                        'border' => 'border-error-200 dark:border-error-900',
                                        'icon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'badge' => 'bg-error-100 text-error-700 dark:bg-error-900 dark:text-error-300'
                                    ],
                                    'info' => [
                                        'gradient' => 'from-brand-500 to-brand-600',
                                        'light' => 'bg-brand-50 dark:bg-brand-950/30',
                                        'border' => 'border-brand-200 dark:border-brand-900',
                                        'icon' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
                                        'badge' => 'bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300'
                                    ],
                                ];
                                
                                $config = $typeConfig[$type] ?? $typeConfig['info'];
                                $typeIcon = $config['icon'];
                                $typeGradient = $config['gradient'];
                                $typeBorder = $config['border'];
                                $typeBadge = $config['badge'];
                                
                                $readClass = $isRead ? 'opacity-75' : '';
                                $readBadge = $isRead 
                                    ? '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Read</span>'
                                    : '<span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900 dark:text-brand-300">Unread</span>';
                            @endphp
                            
                            <div class="notification-item group relative overflow-hidden rounded-2xl border {{ $typeBorder }} bg-white p-6 shadow-sm transition-all hover:shadow-lg dark:bg-gray-900 {{ $readClass }}" 
                                 data-read="{{ $isRead ? 'true' : 'false' }}">
                                <div class="notification-unread-glow pointer-events-none absolute -inset-3 rounded-3xl bg-brand-500/20 blur-2xl {{ $isRead ? 'hidden' : '' }}"></div>
                                <div class="flex flex-col sm:flex-row sm:items-start gap-4 pl-3">
                                    <!-- Icon with gradient background -->
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $typeGradient }} text-white shadow-md">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeIcon }}" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-3 flex-wrap">
                                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                                                    <span class="notification-read-badge">{!! $readBadge !!}</span>
                                                </div>
                                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $message }}</p>
                                            </div>
                                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5 rounded-full">
                                                {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                        
                                        @if($link)
                                            <div class="mt-4 flex items-center gap-4">
                                                <a href="{{ $link }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                                    View Details
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex flex-shrink-0 items-start gap-2">
                                        @if(Route::has('admin.notifications.mark-read'))
                                        <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST" class="mark-read-form {{ $isRead ? 'hidden' : '' }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm p-2.5 text-gray-500 shadow-xs hover:bg-white hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-brand-400 transition-all group"
                                                    title="Mark as read">
                                                <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if(Route::has('admin.notifications.mark-unread'))
                                        <form action="{{ route('admin.notifications.mark-unread', $notification->id) }}" method="POST" class="mark-unread-form {{ $isRead ? '' : 'hidden' }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm p-2.5 text-gray-500 shadow-xs hover:bg-white hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-all group"
                                                    title="Mark as unread">
                                                <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
function initNotificationsPage() {
    const pageRoot = document.getElementById('notifications-page-root');
    let serverUnreadCountState = Number.parseInt(pageRoot?.dataset?.serverUnreadCount ?? '', 10);
    if (!Number.isInteger(serverUnreadCountState)) {
        serverUnreadCountState = null;
    }

    const allBtn = document.getElementById('show-all-btn');
    const unreadBtn = document.getElementById('show-unread-btn');
    const readBtn = document.getElementById('show-read-btn');
    let activeFilter = 'all';

    function getNotificationItems() {
        return Array.from(document.querySelectorAll('.notification-item'));
    }

    function setActiveFilter(activeBtn) {
        [allBtn, unreadBtn, readBtn].forEach(btn => {
            if (!btn) {
                return;
            }
            btn.classList.remove('bg-white', 'dark:bg-gray-900', 'shadow-sm', 'text-gray-700', 'dark:text-gray-300');
            btn.classList.add('text-gray-600', 'dark:text-gray-400');
        });
        if (activeBtn) {
            activeBtn.classList.add('bg-white', 'dark:bg-gray-900', 'shadow-sm', 'text-gray-700', 'dark:text-gray-300');
            activeBtn.classList.remove('text-gray-600', 'dark:text-gray-400');
        }
    }

    function filterNotifications(filter) {
        activeFilter = filter;
        getNotificationItems().forEach(item => {
            const isRead = item.dataset.read === 'true';
            if (filter === 'all') {
                item.style.display = '';
            } else if (filter === 'unread') {
                item.style.display = isRead ? 'none' : '';
            } else {
                item.style.display = isRead ? '' : 'none';
            }
        });
    }

    function setSystemAlertState(card, isRead) {
        if (!card) {
            return;
        }
        card.dataset.read = isRead ? 'true' : 'false';

        const unreadClass = card.dataset.unreadClass || '';
        const readClass = card.dataset.readClass || '';
        unreadClass.split(' ').filter(Boolean).forEach(cls => card.classList.remove(cls));
        readClass.split(' ').filter(Boolean).forEach(cls => card.classList.remove(cls));
        (isRead ? readClass : unreadClass).split(' ').filter(Boolean).forEach(cls => card.classList.add(cls));

        const dot = card.querySelector('.system-alert-dot');
        const label = card.querySelector('.system-alert-label');
        const message = card.querySelector('.system-alert-message');
        const readForm = card.querySelector('.system-mark-read-form');
        const unreadDot = card.dataset.unreadDot || '';
        const unreadLabelClass = card.dataset.unreadLabelClass || '';

        if (dot) {
            dot.classList.remove('bg-gray-400');
            unreadDot.split(' ').filter(Boolean).forEach(cls => dot.classList.remove(cls));
            if (isRead) {
                dot.classList.add('bg-gray-400');
            } else {
                unreadDot.split(' ').filter(Boolean).forEach(cls => dot.classList.add(cls));
            }
        }
        if (label) {
            label.classList.remove('text-gray-500', 'dark:text-gray-400');
            unreadLabelClass.split(' ').filter(Boolean).forEach(cls => label.classList.remove(cls));
            if (isRead) {
                label.classList.add('text-gray-500', 'dark:text-gray-400');
            } else {
                unreadLabelClass.split(' ').filter(Boolean).forEach(cls => label.classList.add(cls));
            }
        }
        if (message) {
            message.classList.toggle('text-gray-800', !isRead);
            message.classList.toggle('dark:text-gray-100', !isRead);
            message.classList.toggle('text-gray-600', isRead);
            message.classList.toggle('dark:text-gray-300', isRead);
        }
        if (readForm) {
            readForm.classList.toggle('hidden', isRead);
        }
    }

    function setUserNotificationState(item, isRead) {
        if (!item) {
            return;
        }
        item.dataset.read = isRead ? 'true' : 'false';
        item.classList.toggle('opacity-75', isRead);

        const unreadGlow = item.querySelector('.notification-unread-glow');
        if (unreadGlow) {
            unreadGlow.classList.toggle('hidden', isRead);
        }

        const markReadForm = item.querySelector('.mark-read-form');
        const markUnreadForm = item.querySelector('.mark-unread-form');
        if (markReadForm) {
            markReadForm.classList.toggle('hidden', isRead);
        }
        if (markUnreadForm) {
            markUnreadForm.classList.toggle('hidden', !isRead);
        }

        const badgeWrap = item.querySelector('.notification-read-badge');
        if (badgeWrap) {
            badgeWrap.innerHTML = isRead
                ? '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Read</span>'
                : '<span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900 dark:text-brand-300">Unread</span>';
        }
    }

    function refreshCounts(serverUnreadCount = null) {
        const unreadUser = getNotificationItems().filter(item => item.dataset.read !== 'true').length;
        const systemCards = Array.from(document.querySelectorAll('.system-alert-card'));
        const unreadSystem = systemCards.filter(card => card.dataset.read !== 'true').length;
        const computedTotalUnread = unreadUser + unreadSystem;
        if (Number.isInteger(serverUnreadCount)) {
            serverUnreadCountState = serverUnreadCount;
            if (pageRoot) {
                pageRoot.dataset.serverUnreadCount = String(serverUnreadCountState);
            }
        }
        const finalHeaderUnread = Number.isInteger(serverUnreadCountState) ? serverUnreadCountState : computedTotalUnread;

        const systemSectionCount = document.getElementById('system-unread-section-count');
        if (systemCards.length > 0) {
            if (systemSectionCount) {
                systemSectionCount.textContent = String(unreadSystem);
            }
        }

        document.querySelectorAll('.js-header-alert-count').forEach(badge => {
            badge.textContent = String(finalHeaderUnread);
            badge.classList.toggle('hidden', finalHeaderUnread === 0);
        });

        document.querySelectorAll('.js-header-alert-active-count').forEach(el => {
            el.textContent = String(finalHeaderUnread);
        });
    }

    function handleFormSubmit(form, successCallback) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(this.action, {
                method: this.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successCallback(this);
                    refreshCounts(Number.isInteger(data.unread_count) ? data.unread_count : null);
                    filterNotifications(activeFilter);
                }
            })
            .catch(() => {
                this.submit();
            });
        });
    }

    if (allBtn) {
        allBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveFilter(this);
            filterNotifications('all');
        });
    }
    if (unreadBtn) {
        unreadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveFilter(this);
            filterNotifications('unread');
        });
    }
    if (readBtn) {
        readBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveFilter(this);
            filterNotifications('read');
        });
    }

    document.querySelectorAll('.mark-read-form').forEach(form => {
        handleFormSubmit(form, function(currentForm) {
            setUserNotificationState(currentForm.closest('.notification-item'), true);
        });
    });

    document.querySelectorAll('.mark-unread-form').forEach(form => {
        handleFormSubmit(form, function(currentForm) {
            setUserNotificationState(currentForm.closest('.notification-item'), false);
        });
    });

    document.querySelectorAll('.system-mark-read-form').forEach(form => {
        handleFormSubmit(form, function(currentForm) {
            setSystemAlertState(currentForm.closest('.system-alert-card'), true);
        });
    });

    document.querySelectorAll('.js-notifications-refresh').forEach(button => {
        if (button.dataset.boundRefresh === '1') {
            return;
        }
        button.dataset.boundRefresh = '1';

        button.addEventListener('click', function (e) {
            e.preventDefault();
            const icon = button.querySelector('.js-refresh-icon');
            button.disabled = true;
            if (icon) {
                icon.classList.add('animate-spin');
            }
            fetch(window.location.href, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const currentRoot = document.getElementById('notifications-page-root');
                const nextRoot = doc.getElementById('notifications-page-root');
                if (currentRoot && nextRoot) {
                    if (nextRoot.dataset.serverUnreadCount !== undefined) {
                        currentRoot.dataset.serverUnreadCount = nextRoot.dataset.serverUnreadCount;
                    }
                    currentRoot.innerHTML = nextRoot.innerHTML;
                    initNotificationsPage();
                }
            })
            .catch(() => {
                window.location.reload();
            })
            .finally(() => {
                button.disabled = false;
                if (icon) {
                    icon.classList.remove('animate-spin');
                }
            });
        });
    });

    refreshCounts();
}

document.addEventListener('DOMContentLoaded', initNotificationsPage);
</script>
@endpush
@endsection
