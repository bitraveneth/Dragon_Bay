@extends('layouts.app')

@section('content')
@php
    $totalPermissions = $permissions->count();
    $totalGroups = $groups->count();
    $totalRoles = count($roles);
    $selectedGrantedCount = !empty($selectedRoleKey) ? count($selectedPermissionNames ?? []) : 0;
@endphp

<div class="space-y-6" x-data="{ showCreatePermission: false, showAdvancedMatrix: false, editorSearch: '', matrixSearch: '' }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Permission Manager</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Set what each role can access. Start by choosing a role, then enable only required permissions.
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                `super_admin` always has full access. `admin` follows these mappings once explicit admin permissions are saved.
            </p>
        </div>
        <button
            type="button"
            @click="showCreatePermission = true"
            class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
        >
            Add Permission
        </button>
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

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Permissions</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalPermissions }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Permission Groups</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalGroups }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Roles</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalRoles }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Selected Role Access</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $selectedGrantedCount }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Role Access Editor</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pick one role and edit its access list.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.permissions.index') }}" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                        <select name="role" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:w-64">
                            <option value="">Choose role...</option>
                            @foreach($roles as $roleKey => $roleLabel)
                                <option value="{{ $roleKey }}" @selected(($selectedRoleKey ?? '') === $roleKey)>{{ $roleLabel }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                            Load
                        </button>
                        @if(!empty($selectedRoleKey))
                            <a href="{{ route('admin.permissions.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if(!empty($selectedRoleKey) && $selectedRoleKey !== 'super_admin')
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Editing: {{ $selectedRoleLabel ?? $selectedRoleKey }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Use search to quickly find a permission key. These permissions are role-level defaults.</p>
                        </div>
                        <input
                            type="text"
                            x-model="editorSearch"
                            placeholder="Search permission..."
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:w-72"
                        >
                    </div>

                    <form method="POST" action="{{ route('admin.permissions.roles.update-single', $selectedRoleKey) }}" class="mt-4 space-y-4">
                        @csrf
                        @foreach($groupedPermissions as $groupLabel => $perms)
                            @php
                                $groupSlug = \Illuminate\Support\Str::slug($groupLabel ?: 'other');
                            @endphp
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $groupLabel }}</p>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $perms->count() }} permissions</p>
                                    </div>
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" onclick="window.PermissionUi.toggleGroup('{{ $groupSlug }}', true)" class="rounded-md border border-gray-200 bg-white px-2 py-1 text-[10px] font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">All</button>
                                        <button type="button" onclick="window.PermissionUi.toggleGroup('{{ $groupSlug }}', false)" class="rounded-md border border-gray-200 bg-white px-2 py-1 text-[10px] font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">None</button>
                                    </div>
                                </div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($perms as $perm)
                                        @php $searchHaystack = strtolower($perm->label . ' ' . $perm->name); @endphp
                                        <label
                                            x-show="editorSearch === '' || @js($searchHaystack).includes(editorSearch.toLowerCase())"
                                            class="flex items-center justify-between gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800/40"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $perm->name }}"
                                                    data-perm-group="{{ $groupSlug }}"
                                                    @checked(in_array($perm->name, $selectedPermissionNames ?? []))
                                                    class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                                />
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $perm->label }}</span>
                                            </span>
                                            <span class="font-mono text-[11px] text-gray-500 dark:text-gray-400">{{ $perm->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="flex items-center justify-end">
                            <button type="submit" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                                Save {{ $selectedRoleLabel ?? $selectedRoleKey }} Permissions
                            </button>
                        </div>
                    </form>
                </div>
            @elseif(!empty($selectedRoleKey) && $selectedRoleKey === 'super_admin')
                <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm text-brand-900 shadow-theme-sm dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-100">
                    <p class="font-semibold">Super Admin has full access by default.</p>
                    <p class="mt-1 text-xs">No checklist needed. Configure other roles from the editor.</p>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                    Select a role to start editing permissions.
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Module Summary</h3>
                <div class="mt-3 space-y-2">
                    @forelse($groups as $group)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 text-xs dark:border-gray-700">
                            <span class="text-gray-700 dark:text-gray-200">{{ $group->label }}</span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $group->total }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 dark:text-gray-400">No groups found.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Permission Catalog</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Review keys and remove unused permissions.</p>
                <div class="mt-3 max-h-64 space-y-2 overflow-y-auto pr-1">
                    @forelse($permissions as $permission)
                        <div class="rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $permission->label }}</p>
                                    <p class="font-mono text-[11px] text-gray-500 dark:text-gray-400">{{ $permission->name }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" onsubmit="return confirm('Delete permission {{ $permission->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded-md border border-error-200 bg-error-50 px-2 py-1 text-[10px] font-semibold text-error-700 hover:bg-error-100 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300 dark:hover:bg-error-500/20">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 dark:text-gray-400">No permission keys found.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Advanced Matrix (All Roles)</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Open a full-screen matrix and edit role-level access in one place.</p>
                    </div>
                    <button
                        type="button"
                        @click="showAdvancedMatrix = true"
                        class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                    >
                        Open Matrix
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div
        x-show="showAdvancedMatrix"
        x-cloak
        @keydown.escape.window="showAdvancedMatrix = false"
        @click.self="showAdvancedMatrix = false"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/70 p-2 sm:p-4"
    >
        <div
            x-show="showAdvancedMatrix"
            x-transition
            class="flex h-[92vh] w-full max-w-[calc(100vw-1rem)] flex-col rounded-lg border border-gray-200 bg-white shadow-theme-lg dark:border-gray-700 dark:bg-gray-900 sm:max-w-[calc(100vw-2rem)] xl:max-w-[96vw]"
        >
            <div class="flex items-start justify-between gap-3 border-b border-gray-200 px-3 py-3 dark:border-gray-700 sm:px-5 sm:py-4">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Advanced Matrix (All Roles)</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Toggle role-level permissions for all roles from one popup.</p>
                </div>
                <button type="button" @click="showAdvancedMatrix = false" class="shrink-0 rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex min-h-0 flex-1 flex-col overflow-hidden p-3 sm:p-5">
                <div class="mb-3 shrink-0">
                    <input
                        type="text"
                        x-model="matrixSearch"
                        placeholder="Filter matrix by label or key..."
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <form method="POST" action="{{ route('admin.permissions.roles.update') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @foreach($roles as $roleKey => $roleLabel)
                        @if($roleKey !== 'super_admin')
                            <input type="hidden" name="submitted_roles[]" value="{{ $roleKey }}">
                        @endif
                    @endforeach
                    <div class="min-h-0 flex-1 overflow-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-[980px] text-xs">
                            <thead class="sticky top-0 bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-3 py-2 text-left">Permission</th>
                                    <th class="px-3 py-2 text-left">Key</th>
                                    @foreach($roles as $roleKey => $roleLabel)
                                        <th class="px-2 py-2 text-center whitespace-nowrap">{{ $roleLabel }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($permissions as $permission)
                                    @php $matrixHaystack = strtolower($permission->label . ' ' . $permission->name); @endphp
                                    <tr x-show="matrixSearch === '' || @js($matrixHaystack).includes(matrixSearch.toLowerCase())">
                                        <td class="w-56 px-3 py-2 text-gray-900 dark:text-white">{{ $permission->label }}</td>
                                        <td class="w-48 px-3 py-2 font-mono text-[11px] text-gray-500 dark:text-gray-400">{{ $permission->name }}</td>
                                        @foreach($roles as $roleKey => $roleLabel)
                                            @php $allowed = in_array($permission->name, $rolePermissions[$roleKey] ?? []); @endphp
                                            <td class="px-2 py-2 text-center">
                                                @if($roleKey === 'super_admin')
                                                    <span class="inline-flex items-center justify-center rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-medium text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">All</span>
                                                @else
                                                    <input
                                                        type="checkbox"
                                                        name="role_permissions[{{ $roleKey }}][]"
                                                        value="{{ $permission->name }}"
                                                        @checked($allowed)
                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                                                    />
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 flex shrink-0 items-center justify-end gap-2 border-t border-gray-200 bg-white pt-3 dark:border-gray-700 dark:bg-gray-900">
                        <button
                            type="button"
                            @click="showAdvancedMatrix = false"
                            class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            Close
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                        >
                            Save Matrix
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $groupLabels = collect($groups)->pluck('label')->filter()->values()->all();
        $oldGroup = old('group', '');
        $oldGroupIsKnown = in_array($oldGroup, $groupLabels, true);
        $initialGroupChoice = $oldGroup === '' ? '' : ($oldGroupIsKnown ? $oldGroup : '__custom');
        $initialCustomGroup = $oldGroupIsKnown ? '' : $oldGroup;
    @endphp

    <div
        x-show="showCreatePermission"
        x-cloak
        @keydown.escape.window="showCreatePermission = false"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/60 p-4"
    >
        <div
            x-show="showCreatePermission"
            x-transition
            x-data="{ groupChoice: @js($initialGroupChoice), customGroup: @js($initialCustomGroup) }"
            class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Add Permission</h2>
                <button type="button" @click="showCreatePermission = false" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Permission key</label>
                    <input
                        type="text"
                        name="name"
                        placeholder="e.g. finance.invoice.issue"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        required
                    />
                    <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Use lowercase dot notation: module.action</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label</label>
                    <input
                        type="text"
                        name="label"
                        placeholder="Human readable label"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Group</label>
                    <select
                        x-model="groupChoice"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Select module group (optional)</option>
                        @foreach($groupLabels as $groupLabel)
                            <option value="{{ $groupLabel }}">{{ $groupLabel }}</option>
                        @endforeach
                        <option value="__custom">+ Create new group</option>
                    </select>
                    <input
                        x-show="groupChoice === '__custom'"
                        x-cloak
                        x-model="customGroup"
                        type="text"
                        placeholder="Enter new group name"
                        class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <input type="hidden" name="group" :value="groupChoice === '__custom' ? customGroup : groupChoice">
                </div>
                <div class="mt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showCreatePermission = false"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
                    >
                        Save Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.PermissionUi = {
    toggleGroup(groupSlug, checked) {
        document
            .querySelectorAll('input[data-perm-group="' + groupSlug + '"]')
            .forEach(function (el) {
                el.checked = checked;
            });
    }
};
</script>
@endpush
