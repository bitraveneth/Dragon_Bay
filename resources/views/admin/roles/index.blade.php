@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showCreateRole: {{ $errors->has('key') || $errors->has('label') || $errors->has('role_create') ? 'true' : 'false' }} }">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Primary Role Manager
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Review assigned role sets and update each user's primary role from one place. Use User Access for extra roles and per-user overrides.
            </p>
        </div>
        @if(auth()->user()?->hasRole('super_admin'))
            <button
                type="button"
                @click="showCreateRole = true"
                class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
            >
                New role
            </button>
        @endif
    </div>

    {{-- Status --}}

    @if($errors->has('role_create'))
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300">
            {{ $errors->first('role_create') }}
        </div>
    @endif

    {{-- Summary --}}
    @php
        $totalUsers = $users instanceof \Illuminate\Pagination\LengthAwarePaginator ? $users->total() : $users->count();
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total users</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalUsers }}</p>
        </div>
        @foreach($roles as $value => $label)
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $roleCounts[$value] ?? 0 }}
                </p>
            </div>
        @endforeach
    </div>
    {{-- Create role modal --}}
    @if(auth()->user()?->hasRole('super_admin'))
        <div
            x-show="showCreateRole"
            x-cloak
            @keydown.escape.window="showCreateRole = false"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/60 p-4"
        >
            <div
                x-show="showCreateRole"
                x-transition
                class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                        Create new role
                    </h2>
                    <button
                        type="button"
                        @click="showCreateRole = false"
                        class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Choose a short key (used in code) and a readable label. You can map permissions to this role from the Permission manager.
                </p>

                <form method="POST" action="{{ route('admin.roles.create') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                            Role key
                        </label>
                        <input
                            type="text"
                            name="key"
                            value="{{ old('key') }}"
                            placeholder="e.g. finance_manager"
                            required
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        @error('key')
                            <p class="mt-1 text-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                            Label
                        </label>
                        <input
                            type="text"
                            name="label"
                            value="{{ old('label') }}"
                            placeholder="Visible name, e.g. Finance manager"
                            required
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        @error('label')
                            <p class="mt-1 text-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="showCreateRole = false"
                            class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                        >
                            Create role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="relative">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search by name or email..."
                    class="w-64 rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                />
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                </span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <select
                name="role"
                class="rounded-lg border border-gray-200 bg-white py-2 px-3 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
                <option value="">All roles</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected($filterRole === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button
                type="submit"
                class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
            >
                Apply
            </button>
        </div>
    </form>

    {{-- Users table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-4 py-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
            This screen changes only the primary role stored in `users.role`. Additional roles stay in User Access.
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-2 text-left">User</th>
                    <th class="px-4 py-2 text-left">Assigned roles</th>
                    <th class="px-4 py-2 text-right">Primary role</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 align-top">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $user->name ?? '—' }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $user->email }}
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            @php
                                $roleKeys = $user->roleKeys();
                            @endphp
                            <div class="flex flex-wrap gap-1">
                                @forelse($roleKeys as $roleKey)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $roles[$roleKey] ?? ucfirst(str_replace('_', ' ', $roleKey)) }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">Unknown</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top text-right">
                            @php
                                $canEditUserPrimaryRole = auth()->user()?->hasRole('super_admin') || ! in_array('super_admin', $roleKeys, true);
                            @endphp
                            <div class="flex flex-col items-end gap-2 sm:flex-row sm:justify-end">
                                <a
                                    href="{{ route('admin.permissions.index', ['role' => $user->role ?? ($roleKeys[0] ?? null)]) }}"
                                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                                >
                                    View primary permissions
                                </a>
                                @if($canEditUserPrimaryRole)
                                    <form method="POST" action="{{ route('admin.roles.update', $user) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select
                                            name="role"
                                            class="rounded-lg border border-gray-200 bg-white py-1.5 px-2 text-xs text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        >
                                            @foreach($roles as $value => $label)
                                                <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                                        >
                                            Save primary role
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-[11px] font-medium text-brand-700 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-300">
                                        Super admin role is protected
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No users found for this filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if(method_exists($users, 'links'))
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
