@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Navigation Manager</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Edit the sidebar in three simple layers: groups, top-level items, and child links.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            Back to dashboard
        </a>
    </div>


    @if($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[320px,minmax(0,1fr)]">
        <div x-data="{ showCreateGroupModal: false }" @keydown.escape.window="showCreateGroupModal = false" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    Step 1
                </div>
                <h2 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Sidebar groups</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Rename groups, change order, or remove them entirely.</p>
            </div>

            <div class="mb-4 flex justify-end">
                <button type="button" @click="showCreateGroupModal = true" class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                    Add group
                </button>
            </div>

            @if($groups->isEmpty())
                <p class="text-xs text-gray-500 dark:text-gray-400">No groups yet. Use the form above to add one.</p>
            @else
                <div class="space-y-3">
                    @foreach($groups as $group)
                        <div x-data="{ showGroupModal: false }" @keydown.escape.window="showGroupModal = false" class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/60">
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $group->title }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                        <span class="rounded-full bg-gray-200 px-2 py-0.5 dark:bg-gray-800">
                                            {{ $group->items->count() }} top-level item{{ $group->items->count() === 1 ? '' : 's' }}
                                        </span>
                                        <span class="rounded-full bg-gray-200 px-2 py-0.5 dark:bg-gray-800">
                                            Key: {{ $group->key }}
                                        </span>
                                        <span @class([
                                            'rounded-full px-2 py-0.5 font-semibold',
                                            'bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $group->is_active,
                                            'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $group->is_active,
                                        ])>
                                            {{ $group->is_active ? 'Visible' : 'Hidden' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-end gap-1">
                                <form method="POST" action="{{ route('admin.menu.groups.toggle', $group) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class([
                                        'rounded border px-2.5 py-1.5 text-[11px] font-medium',
                                        'border-warning-200 bg-warning-50 text-warning-700 hover:bg-warning-100 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-300' => $group->is_active,
                                        'border-success-200 bg-success-50 text-success-700 hover:bg-success-100 dark:border-success-500/40 dark:bg-success-500/10 dark:text-success-300' => ! $group->is_active,
                                    ])>
                                        {{ $group->is_active ? 'Hide' : 'Show' }}
                                    </button>
                                </form>
                                <button type="button" @click="showGroupModal = true" class="rounded border border-gray-200 bg-white px-2.5 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.menu.groups.move', $group) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="rounded border border-gray-200 bg-white px-2 py-1.5 text-[11px] text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Up
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.menu.groups.move', $group) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="rounded border border-gray-200 bg-white px-2 py-1.5 text-[11px] text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Down
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.menu.groups.delete', $group) }}" onsubmit="return confirm('Delete group {{ $group->title }} and all its items?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-error-200 bg-error-50 px-2 py-1.5 text-[11px] font-medium text-error-600 hover:bg-error-100 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300">
                                        Delete
                                    </button>
                                </form>
                            </div>

                            <div x-show="showGroupModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-900/60 px-4 py-6">
                                <div @click.outside="showGroupModal = false" class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Update group</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rename this sidebar group without changing its items.</p>
                                        </div>
                                        <button type="button" @click="showGroupModal = false" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                            Close
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.menu.groups.update', $group) }}" class="space-y-4 px-5 py-5">
                                        @csrf
                                        @method('PATCH')
                                        <div>
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Group name</label>
                                            <input
                                                type="text"
                                                name="title"
                                                value="{{ $group->title }}"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                            />
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                                            <span class="rounded-full bg-gray-100 px-3 py-1 dark:bg-gray-800">
                                                {{ $group->items->count() }} top-level item{{ $group->items->count() === 1 ? '' : 's' }}
                                            </span>
                                            <span class="rounded-full bg-gray-100 px-3 py-1 dark:bg-gray-800">
                                                Key: {{ $group->key }}
                                            </span>
                                            <span class="rounded-full bg-gray-100 px-3 py-1 dark:bg-gray-800">
                                                Position: {{ $group->position }}
                                            </span>
                                            <span @class([
                                                'rounded-full px-3 py-1 font-semibold',
                                                'bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $group->is_active,
                                                'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $group->is_active,
                                            ])>
                                                {{ $group->is_active ? 'Visible in navigation' : 'Hidden from navigation' }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="showGroupModal = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                                Cancel
                                            </button>
                                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                                                Save group
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div x-show="showCreateGroupModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-900/60 px-4 py-6">
                <div @click.outside="showCreateGroupModal = false" class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Add new group</h3>
                        <button type="button" @click="showCreateGroupModal = false" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Close
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.menu.groups.store') }}" class="space-y-4 px-5 py-5">
                        @csrf
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Group name</label>
                            <input type="text" name="title" placeholder="e.g. Inventory" required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>
                        <div class="rounded-xl bg-gray-50 px-4 py-3 text-xs text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                            Keep group names short and clear. These become the main sidebar section headers users scan first.
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="showCreateGroupModal = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                                Create group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    Step 2 and 3
                </div>
                <h2 class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Menu items by group</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Inside each group, first manage top-level items, then add child links under them.</p>
            </div>

            @if($groups->isEmpty())
                <p class="text-xs text-gray-500 dark:text-gray-400">Create a group first to start adding items.</p>
            @else
                <div class="space-y-4">
                    @foreach($groups as $group)
                        <section x-data="{ showCreateItemModal: false, openGroup: false }" @keydown.escape.window="showCreateItemModal = false" class="rounded-xl border border-gray-200 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-900/60">
                            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                                <button type="button" @click="openGroup = !openGroup" class="min-w-0 flex-1 text-left">
                                    <div class="flex items-center gap-3">
                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $group->title }}</h3>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Group key: {{ $group->key }}</p>
                                        </div>
                                    </div>
                                </button>
                                <div class="flex items-center gap-2">
                                    <span @class([
                                        'rounded-full px-2.5 py-1 text-[11px] font-medium',
                                        'bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $group->is_active,
                                        'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $group->is_active,
                                    ])>
                                        {{ $group->is_active ? 'Visible' : 'Hidden' }}
                                    </span>
                                    <span class="rounded-full bg-gray-200 px-2.5 py-1 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $group->items->count() }} top-level item{{ $group->items->count() === 1 ? '' : 's' }}
                                    </span>
                                    <button type="button" @click="showCreateItemModal = true" class="inline-flex items-center rounded-lg bg-brand-500 px-3 py-2 text-[11px] font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                                        Add item
                                    </button>
                                    <button type="button" @click="openGroup = !openGroup" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                        <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openGroup }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div x-show="openGroup" x-transition style="display: none;" class="space-y-3 p-4">
                                @forelse($group->items as $item)
                                    <div x-data="{ openItem: false, showEditItemModal: false, showCreateChildModal: false }" @keydown.escape.window="showEditItemModal = false; showCreateChildModal = false" class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                                        <div class="flex items-center gap-3 px-4 py-3">
                                            <button type="button" @click="openItem = !openItem" class="min-w-0 flex-1 text-left">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $item->name }}</div>
                                                        <div class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $item->path }}</div>
                                                    </div>
                                                    <span @class([
                                                        'shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium',
                                                        'bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $item->is_active,
                                                        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => ! $item->is_active,
                                                    ])>
                                                        {{ $item->is_active ? 'Visible' : 'Hidden' }}
                                                    </span>
                                                </div>
                                            </button>
                                            <button type="button" @click="openItem = !openItem" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': openItem }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div x-show="openItem" x-transition style="display: none;" class="border-t border-gray-200 p-3 dark:border-gray-700">
                                        <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <form method="POST" action="{{ route('admin.menu.items.toggle', $item) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" @class([
                                                        'rounded-lg border px-3 py-2 text-xs font-semibold',
                                                        'border-warning-200 bg-warning-50 text-warning-700 hover:bg-warning-100 dark:border-warning-500/40 dark:bg-warning-500/10 dark:text-warning-300' => $item->is_active,
                                                        'border-success-200 bg-success-50 text-success-700 hover:bg-success-100 dark:border-success-500/40 dark:bg-success-500/10 dark:text-success-300' => ! $item->is_active,
                                                    ])>
                                                        {{ $item->is_active ? 'Hide item' : 'Show item' }}
                                                    </button>
                                                </form>
                                                <button type="button" @click="showEditItemModal = true" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                                    Edit item
                                                </button>
                                                <button type="button" @click="showCreateChildModal = true" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                                    Add child link
                                                </button>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-1 xl:justify-end">
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                    {{ $item->icon ? 'Icon: ' . $item->icon : 'No icon' }}
                                                </span>
                                                <form method="POST" action="{{ route('admin.menu.items.move', $item) }}">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="rounded border border-gray-200 bg-white px-2 py-1 text-[11px] text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        Up
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.menu.items.move', $item) }}">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="rounded border border-gray-200 bg-white px-2 py-1 text-[11px] text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        Down
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.menu.items.move-group', $item) }}" class="inline-flex items-center gap-1">
                                                    @csrf
                                                    <select name="menu_group_id" class="rounded border border-gray-200 bg-white px-2 py-1 text-[11px] text-gray-700 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        @foreach($groups as $targetGroup)
                                                            <option value="{{ $targetGroup->id }}" @selected($targetGroup->id === $group->id)>{{ $targetGroup->title }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="rounded border border-gray-200 bg-white px-2 py-1 text-[11px] text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                        Move
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.menu.items.delete', $item) }}" onsubmit="return confirm('Delete item {{ $item->name }} and its sub-items?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded border border-error-200 bg-error-50 px-2 py-1 text-[11px] font-medium text-error-600 hover:bg-error-100 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        @if($item->children->isNotEmpty())
                                            <div class="mt-3 space-y-2 border-t border-dashed border-gray-200 pt-3 dark:border-gray-700">
                                                <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Child links</div>
                                                @foreach($item->children as $child)
                                                    <div x-data="{ showEditChildModal: false }" @keydown.escape.window="showEditChildModal = false" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800/60">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="min-w-0">
                                                                <div class="text-xs font-semibold text-gray-900 dark:text-white">{{ $child->name }}</div>
                                                                <div class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">{{ $child->path }}</div>
                                                            </div>
                                                            <div class="flex items-center gap-3">
                                                                <span @class([
                                                                    'rounded-full px-2 py-0.5 text-[10px] font-medium',
                                                                    'bg-success-100 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $child->is_active,
                                                                    'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => ! $child->is_active,
                                                                ])>
                                                                    {{ $child->is_active ? 'Visible' : 'Hidden' }}
                                                                </span>
                                                                <form method="POST" action="{{ route('admin.menu.items.toggle', $child) }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="text-[11px] font-medium text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">
                                                                        {{ $child->is_active ? 'Hide' : 'Show' }}
                                                                    </button>
                                                                </form>
                                                                <button type="button" @click="showEditChildModal = true" class="text-[11px] font-medium text-gray-700 hover:text-brand-600 dark:text-gray-300 dark:hover:text-brand-300">
                                                                    Edit
                                                                </button>
                                                                <form method="POST" action="{{ route('admin.menu.items.delete', $child) }}" onsubmit="return confirm('Delete link {{ $child->name }}?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-[11px] font-medium text-error-600 hover:text-error-700 dark:text-error-300">
                                                                        Remove
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        <div x-show="showEditChildModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-[1001] flex items-center justify-center bg-gray-900/60 px-4 py-6">
                                                            <div @click.outside="showEditChildModal = false" class="w-full max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                                                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                                                                    <div>
                                                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Edit child link</h4>
                                                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $child->name }}</p>
                                                                    </div>
                                                                    <button type="button" @click="showEditChildModal = false" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                                        Close
                                                                    </button>
                                                                </div>
                                                                <form method="POST" action="{{ route('admin.menu.items.update', $child) }}" class="space-y-4 px-5 py-5">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <div class="grid gap-4 md:grid-cols-2">
                                                                        <div>
                                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Child label</label>
                                                                            <input
                                                                                type="text"
                                                                                name="name"
                                                                                value="{{ $child->name }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                                            />
                                                                        </div>
                                                                        <div>
                                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Path</label>
                                                                            <input
                                                                                type="text"
                                                                                name="path"
                                                                                value="{{ $child->path }}"
                                                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permission key</label>
                                                                        @if(isset($permissions) && $permissions->isNotEmpty())
                                                                            <select name="permission" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                                                <option value="">No permission key</option>
                                                                                @foreach($permissions as $perm)
                                                                                    <option value="{{ $perm->name }}" @selected($child->permission === $perm->name)>{{ $perm->name }} — {{ $perm->label }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        @else
                                                                            <input type="hidden" name="permission" value="">
                                                                            <div class="rounded-lg border border-dashed border-gray-300 px-3 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                                                No permission keys available for this child link.
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex items-center justify-end gap-2">
                                                                        <button type="button" @click="showEditChildModal = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                                                            Cancel
                                                                        </button>
                                                                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                                                                            Save child link
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div x-show="showEditItemModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-[1000] flex items-center justify-center bg-gray-900/60 px-4 py-6">
                                            <div @click.outside="showEditItemModal = false" class="w-full max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                                                    <div>
                                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Edit item</h4>
                                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->name }}</p>
                                                    </div>
                                                    <button type="button" @click="showEditItemModal = false" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                        Close
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.menu.items.update', $item) }}" class="space-y-4 px-5 py-5">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="grid gap-4 md:grid-cols-2">
                                                        <div>
                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Item label</label>
                                                            <input
                                                                type="text"
                                                                name="name"
                                                                value="{{ $item->name }}"
                                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                            />
                                                        </div>
                                                        <div>
                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Path</label>
                                                            <input
                                                                type="text"
                                                                name="path"
                                                                value="{{ $item->path }}"
                                                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permission key</label>
                                                        @if(isset($permissions) && $permissions->isNotEmpty())
                                                            <select name="permission" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                                <option value="">No permission key</option>
                                                                @foreach($permissions as $perm)
                                                                    <option value="{{ $perm->name }}" @selected($item->permission === $perm->name)>{{ $perm->name }} — {{ $perm->label }}</option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <input type="hidden" name="permission" value="">
                                                            <div class="rounded-lg border border-dashed border-gray-300 px-3 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                                No permission keys available for this item.
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="button" @click="showEditItemModal = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                                                            Save item
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div x-show="showCreateChildModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-[1000] flex items-center justify-center bg-gray-900/60 px-4 py-6">
                                            <div @click.outside="showCreateChildModal = false" class="w-full max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                                                    <div>
                                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Add child link</h4>
                                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Parent item: {{ $item->name }}</p>
                                                    </div>
                                                    <button type="button" @click="showCreateChildModal = false" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                                        Close
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.menu.items.store') }}" class="space-y-4 px-5 py-5">
                                                    @csrf
                                                    <input type="hidden" name="menu_group_id" value="{{ $group->id }}">
                                                    <input type="hidden" name="parent_id" value="{{ $item->id }}">
                                                    <div class="grid gap-4 md:grid-cols-2">
                                                        <div>
                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Child label</label>
                                                            <input type="text" name="name" placeholder="New child link label" required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                                        </div>
                                                        <div>
                                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Path</label>
                                                            <input type="text" name="path" placeholder="/admin/..." class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permission key</label>
                                                        @if(isset($permissions) && $permissions->isNotEmpty())
                                                            <select name="permission" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                                <option value="">No permission key</option>
                                                                @foreach($permissions as $perm)
                                                                    <option value="{{ $perm->name }}">{{ $perm->name }} — {{ $perm->label }}</option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <input type="hidden" name="permission" value="">
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="button" @click="showCreateChildModal = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                                            Cancel
                                                        </button>
                                                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">
                                                            Add child link
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        No items in this group yet. Use the <span class="font-semibold">Add item</span> button above to create the first top-level entry.
                                    </p>
                                @endforelse
                            </div>

                            <div x-show="showCreateItemModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-[999] flex items-center justify-center bg-gray-900/60 px-4 py-6">
                                <div @click.outside="showCreateItemModal = false" class="w-full max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                                        <div>
                                            <h4 class="text-base font-semibold text-gray-900 dark:text-white">Add top-level item</h4>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                This creates the main row inside <span class="font-semibold text-gray-900 dark:text-white">{{ $group->title }}</span>.
                                            </p>
                                        </div>
                                        <button type="button" @click="showCreateItemModal = false" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                            Close
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.menu.items.store') }}" class="space-y-4 px-5 py-5">
                                        @csrf
                                        <input type="hidden" name="menu_group_id" value="{{ $group->id }}">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Item label</label>
                                                <input type="text" name="name" placeholder="e.g. Sales" required class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                            </div>
                                            <div>
                                                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Path</label>
                                                <input type="text" name="path" placeholder="# for dropdown or /admin/..." class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permission key</label>
                                            @if(isset($permissions) && $permissions->isNotEmpty())
                                                <select name="permission" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                    <option value="">No permission key</option>
                                                    @foreach($permissions as $perm)
                                                        <option value="{{ $perm->name }}">{{ $perm->name }} — {{ $perm->label }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="hidden" name="permission" value="">
                                                <div class="rounded-lg border border-dashed border-gray-300 px-3 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                                    No permission keys available. This item will be visible without a permission mapping.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="rounded-xl bg-gray-50 px-4 py-3 text-xs text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                                            Use <span class="font-semibold">#</span> if this item should only open a submenu. Use <span class="font-semibold">/admin/...</span> if it should open a page directly.
                                        </div>
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="showCreateItemModal = false" class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                                Cancel
                                            </button>
                                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                                                Add item
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
