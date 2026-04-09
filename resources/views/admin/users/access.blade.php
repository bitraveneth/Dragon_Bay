@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User access settings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $user->name }} ({{ $user->email }}) - configure role set, warehouse scope, and permission overrides.
            </p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
            Back to users
        </a>
    </div>


    @if($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300">
            <p class="font-semibold">Please fix the following:</p>
            <ul class="mt-2 list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.access.update', $user) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Primary role</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This remains in `users.role` for compatibility and is the default role used across the app.</p>
                <div class="mt-4">
                    <select name="primary_role"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('primary_role', $primaryRole) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Additional roles</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional extra roles stored in `user_roles`. The primary role above is not repeated here.</p>

                @if(! $hasUserRolesTable)
                    <p class="mt-4 rounded-lg border border-warning-200 bg-warning-50 px-3 py-2 text-xs text-warning-700 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-300">
                        Run migration first to enable multi-role assignments.
                    </p>
                @else
                    @php
                        $currentPrimaryRole = (string) old('primary_role', $primaryRole);
                        $currentRoles = collect(old('extra_roles', array_diff($selectedRoleKeys, [$primaryRole])))
                            ->map(fn($v) => (string) $v)
                            ->reject(fn($v) => $v === $currentPrimaryRole)
                            ->values()
                            ->all();
                    @endphp
                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($roles as $value => $label)
                            @if($value !== $currentPrimaryRole)
                                <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="extra_roles[]" value="{{ $value }}"
                                        @checked(in_array($value, $currentRoles, true))
                                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800">
                                    <span>{{ $label }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Warehouse scope</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                If no warehouse is selected, user can access all warehouses.
            </p>

            @if(! $hasWarehouseScopesTable)
                <p class="mt-4 rounded-lg border border-warning-200 bg-warning-50 px-3 py-2 text-xs text-warning-700 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-300">
                    Run migration first to enable warehouse-scoped access.
                </p>
            @else
                @php
                    $currentWarehouseIds = collect(old('warehouse_ids', $selectedWarehouseIds))->map(fn($v) => (int) $v)->all();
                @endphp
                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($warehouses as $warehouse)
                        <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="warehouse_ids[]" value="{{ $warehouse->id }}"
                                @checked(in_array((int) $warehouse->id, $currentWarehouseIds, true))
                                class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-800">
                            <span>{{ $warehouse->name }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No warehouses found.</p>
                    @endforelse
                </div>
            @endif
        </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Permission overrides</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Use override only for exceptional cases. Inherit means the effective access comes from the assigned role set.
                </p>

            @if(! $hasUserPermissionsTable)
                <p class="mt-4 rounded-lg border border-warning-200 bg-warning-50 px-3 py-2 text-xs text-warning-700 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-300">
                    Run migration first to enable per-user permission override.
                </p>
            @else
                <div class="mt-4 space-y-5">
                    @forelse($permissionsGrouped as $group => $permissions)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ $group }}
                            </div>
                            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($permissions as $permission)
                                    @php
                                        $current = old('overrides.' . $permission->name);
                                        if ($current === null) {
                                            if (array_key_exists($permission->name, $permissionOverrides)) {
                                                $current = $permissionOverrides[$permission->name] ? 'allow' : 'deny';
                                            } else {
                                                $current = 'inherit';
                                            }
                                        }
                                    @endphp
                                    <div class="flex flex-col gap-3 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $permission->label }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $permission->name }}</p>
                                        </div>
                                        <div class="inline-flex items-center rounded-lg border border-gray-200 p-1 text-xs dark:border-gray-700">
                                            <label class="inline-flex items-center gap-1 rounded-md px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                                <input type="radio" name="overrides[{{ $permission->name }}]" value="inherit" @checked($current === 'inherit')>
                                                <span>Inherit</span>
                                            </label>
                                            <label class="inline-flex items-center gap-1 rounded-md px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                                <input type="radio" name="overrides[{{ $permission->name }}]" value="allow" @checked($current === 'allow')>
                                                <span>Allow</span>
                                            </label>
                                            <label class="inline-flex items-center gap-1 rounded-md px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                                                <input type="radio" name="overrides[{{ $permission->name }}]" value="deny" @checked($current === 'deny')>
                                                <span>Deny</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No permission definitions found.</p>
                    @endforelse
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.users.index') }}"
                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                Cancel
            </a>
            <button type="submit"
                class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                Save access settings
            </button>
        </div>
    </form>
</div>
@endsection
