@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header with gradient -->
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        <div>
            <div class="flex items-start gap-5">
                <!-- Employee Avatar/Photo -->
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full blur opacity-20"></div>
                    <div class="relative">
                        @if($employee->photo_path)
                            <img src="{{ asset('storage/'.$employee->photo_path) }}" 
                                 alt="{{ $employee->name }}" 
                                 class="h-20 w-20 rounded-full object-cover border-4 border-white shadow-xl dark:border-gray-800">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-2xl font-bold text-white shadow-xl">
                                {{ substr($employee->name, 0, 1) }}{{ substr($employee->name, strpos($employee->name, ' ') + 1, 1) ?? '' }}
                            </div>
                        @endif
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            {{ $employee->name }}
                        </h1>
                        
                        @php
                            $primaryBadge = $employee->badges && $employee->badges->isNotEmpty()
                                ? $employee->badges->first()
                                : null;
                            $badgeColor = $primaryBadge?->color ?: '#1d4ed8';
                        @endphp
                        
                        @if($primaryBadge)
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium shadow-sm"
                                  style="background: {{ $badgeColor }}10; color: {{ $badgeColor }}; border: 1px solid {{ $badgeColor }}30;">
                                <span class="h-2 w-2 rounded-full" style="background: {{ $badgeColor }};"></span>
                                {{ $primaryBadge->name }}
                            </span>
                        @endif
                        
                        @if($employee->user)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                Active Account
                            </span>
                        @endif
                    </div>
                    
                    <div class="mt-2 flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm font-medium">{{ $employee->job_position ?? 'Employee' }}</span>
                        @if($employee->department)
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span class="text-sm">{{ $employee->department }}</span>
                        @endif
                        @if($employee->work_zone)
                            <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                            <span class="text-sm">{{ $employee->work_zone }}</span>
                        @endif
                    </div>
                    
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>Joined {{ $employee->created_at?->format('d M Y') }}</span>
                        <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                        <span>Updated {{ $employee->updated_at?->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees.edit', $employee) }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Profile
            </a>
            <a href="{{ route('admin.employees.index') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-5 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Employees
            </a>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Main Profile Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Contact Information</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Work Email</p>
                            @if($employee->work_email)
                                <a href="mailto:{{ $employee->work_email }}" class="mt-1 text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                    {{ $employee->work_email }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Work Phone</p>
                            @if($employee->work_phone)
                                <a href="tel:{{ $employee->work_phone }}" class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $employee->work_phone }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Work Mobile</p>
                            @if($employee->work_mobile)
                                <a href="tel:{{ $employee->work_mobile }}" class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $employee->work_mobile }}
                                </a>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">—</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role & Organisation -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Role & Organisation</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->department ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Work Zone</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $employee->work_zone ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h.008v.008H6V6z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tags</h3>
                    </div>
                </div>
                <div class="p-6">
                    @if(!empty($employee->tags) && is_array($employee->tags) && count($employee->tags) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($employee->tags as $tag)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No tags assigned.</p>
                    @endif
                </div>
            </div>

            <!-- Recent Allowances -->
            @if($recentAllowances->isNotEmpty())
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                    <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Allowances</h3>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Latest TA/DA/bonus slips</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Reference</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach($recentAllowances as $allowance)
                                    @php
                                        $statusColors = [
                                            'submitted' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                            'approved' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                                            'paid' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                                            'rejected' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                                        ];
                                        $statusColor = $statusColors[$allowance->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ $allowance->date?->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $allowance->type }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $allowance->reference ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                BDT {{ number_format($allowance->amount, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                                                {{ ucfirst($allowance->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
            <!-- Login Account Card -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Login Account</h3>
                    </div>
                </div>
                <div class="p-6">
                    @if($employee->user)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Email</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $employee->user->email }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Role</span>
                                <span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                                    {{ ucfirst($employee->user->role) }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                This employee can sign in using the above email.
                            </p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No login account linked</p>
                            <a href="{{ route('admin.employees.user.create', $employee) }}" 
                               class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:from-brand-600 hover:to-brand-700 transition-all">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Login Account
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Contract Card -->
            @if($currentContract)
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Current Contract</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Reference</span>
                                <span class="text-sm font-mono font-medium text-gray-900 dark:text-white">{{ $currentContract->reference }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Period</span>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ optional($currentContract->start_date)->format('d M Y') }} –
                                    {{ optional($currentContract->end_date)->format('d M Y') ?? 'Open-ended' }}
                                </span>
                            </div>
                            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 mt-1">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Salary</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">BDT {{ number_format($currentContract->salary_amount ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">TA</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">BDT {{ number_format($currentContract->travel_allowance ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">DA</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">BDT {{ number_format($currentContract->dearness_allowance ?? 0, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Bonus</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">BDT {{ number_format($currentContract->bonus ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="{{ route('admin.employees.contracts.index', $employee) }}" 
                                   class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                                    View All Contracts
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Links Card -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 dark:bg-brand-900/30">
                            <svg class="h-4 w-4 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Links</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                        Jump into detailed views for this employee.
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.employees.contracts.index', $employee) }}" 
                           class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 p-3 text-center hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/50 dark:hover:bg-gray-800 transition-all">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM8 7h8M8 11h6M8 15h4" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Contracts</span>
                        </a>
                        <a href="{{ route('admin.employees.allowances.index', $employee) }}" 
                           class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 p-3 text-center hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/50 dark:hover:bg-gray-800 transition-all">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Allowances</span>
                        </a>
                        <a href="{{ route('admin.employees.equipment.index', $employee) }}" 
                           class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 p-3 text-center hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/50 dark:hover:bg-gray-800 transition-all">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Equipment</span>
                        </a>
                        <a href="{{ route('admin.employees.leaves.index', $employee) }}" 
                           class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 p-3 text-center hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/50 dark:hover:bg-gray-800 transition-all">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Leave</span>
                        </a>
                        <a href="{{ route('admin.employees.locations.index', $employee) }}" 
                           class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 p-3 text-center hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/50 dark:hover:bg-gray-800 transition-all">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Locations</span>
                        </a>
                        <a href="{{ route('admin.employees.badges.grant-form', $employee) }}" 
                           class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 p-3 text-center hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800/50 dark:hover:bg-gray-800 transition-all">
                            <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Grant Badge</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
