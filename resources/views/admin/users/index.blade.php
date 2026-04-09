@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showCreateUser: false }">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User manager</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage login accounts and see which roles are assigned. Use Role manager to batch‑update roles.
            </p>
        </div>
        <button
            type="button"
            @click="showCreateUser = true"
            class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
        >
            New user
        </button>
    </div>

    {{-- Status --}}

    {{-- Summary cards --}}
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

    {{-- Filters --}}
    <form method="GET" class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 text-sm shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Search</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Name or email"
                    class="w-full rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            </div>
            <div class="w-full sm:w-60">
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Role</label>
                <select name="role" class="w-full rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All roles</option>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected($filterRole === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                Apply
            </button>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                Reset
            </a>
        </div>
    </form>

    {{-- User table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Role</th>
                    <th class="px-4 py-2 text-left">Access summary</th>
                    <th class="px-4 py-2 text-left">Linked employee</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($users as $user)
                    @php
                        $roleKeys = $user->roleKeys();
                        $roleCount = count($roleKeys);
                        $scopeCount = (int) ($user->warehouse_scopes_count ?? 0);
                        $overrideCount = (int) ($user->permission_overrides_count ?? 0);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $user->name }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                            <div class="flex flex-wrap gap-1">
                                @forelse($roleKeys as $roleKey)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ $roles[$roleKey] ?? ucfirst(str_replace('_', ' ', $roleKey)) }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                            <div class="flex flex-wrap gap-1">
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                                    Roles {{ $roleCount }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    Scope {{ $hasWarehouseScopesTable ? $scopeCount : '—' }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    Override {{ $hasUserPermissionsTable ? $overrideCount : '—' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                            @if($user->employee)
                                {{ $user->employee->name }}
                            @else
                                <span class="text-xs text-gray-400">Not linked</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right text-xs">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.users.access.edit', $user) }}" class="inline-flex items-center rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100 dark:border-brand-800 dark:bg-brand-900/30 dark:text-brand-300 dark:hover:bg-brand-900/50">
                                    Access
                                </a>
                                <a href="{{ route('admin.roles.index', ['q' => $user->email]) }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                                    Role
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
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

    {{-- Create user modal --}}
    <div
        x-show="showCreateUser"
        x-cloak
        @keydown.escape.window="showCreateUser = false"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/60 p-4"
    >
        <div
            x-show="showCreateUser"
            x-transition
            class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Create user</h2>
                <button type="button" @click="showCreateUser = false" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Name</label>
                    <input type="text" name="name" required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Email</label>
                    <input type="email" name="email" required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Role</label>
                    <select name="role" required
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-data="{ password: '', visible: false }">
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Password</label>
                    <div class="flex gap-2">
                        <input
                            :type="visible ? 'text' : 'password'"
                            x-model="password"
                            name="password"
                            placeholder="Leave blank to auto-generate"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        <button
                            type="button"
                            @click="
                                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#$%';
                                const bytes = new Uint8Array(14);
                                window.crypto.getRandomValues(bytes);
                                password = Array.from(bytes, b => chars[b % chars.length]).join('');
                                visible = true;
                            "
                            class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Generate
                        </button>
                        <button
                            type="button"
                            @click="visible = !visible"
                            class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <span x-show="!visible">Show</span>
                            <span x-show="visible">Hide</span>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        If left empty, a strong password will be generated automatically on the server.
                        When using <span class="font-medium">Generate</span>, the value above is what will be saved.
                    </p>
                    <template x-if="password">
                        <p class="mt-1 text-xs text-brand-600 dark:text-brand-300">
                            Generated password:
                            <span class="font-mono" x-text="password"></span>
                        </p>
                    </template>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2">
                    <button type="button" @click="showCreateUser = false"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                        Create user
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
