@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit profile</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Update your account details used to sign in to ERP.
        </p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/40 dark:bg-error-500/10 dark:text-error-300">
            <p class="font-medium">There were some problems with your input.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
        @php
            $employee = $user->employee;
            $avatarUrl = $employee && $employee->photo_path ? asset('storage/'.$employee->photo_path) : null;
            $initials = strtoupper(mb_substr($user->name ?? $user->email, 0, 2));
        @endphp

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PATCH')

            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-brand-500 text-base font-semibold text-white overflow-hidden">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Profile photo" class="h-full w-full object-cover">
                    @else
                        <span>{{ $initials }}</span>
                    @endif
                </div>
                @if($employee)
                    <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <p class="font-medium text-gray-700 dark:text-gray-200">Profile photo</p>
                        <input
                            type="file"
                            name="avatar"
                            accept="image/*"
                            class="block w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:text-gray-300 dark:file:bg-gray-800 dark:file:text-gray-200 dark:hover:file:bg-gray-700"
                        >
                        <p>JPG or PNG, max 2MB. This updates the photo used across HR screens.</p>
                    </div>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Profile photos are linked to employee records. Link this user to an employee to enable avatar uploads.
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 pt-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        Name
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        Email
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        Current password
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            :type="showCurrent ? 'text' : 'password'"
                            name="current_password"
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="Required only when changing password"
                        >
                        <button type="button"
                            @click="showCurrent = !showCurrent"
                            class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <span x-show="!showCurrent">Show</span>
                            <span x-show="showCurrent">Hide</span>
                        </button>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        New password
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            :type="showNew ? 'text' : 'password'"
                            name="password"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="Leave blank to keep current password"
                        >
                        <button type="button"
                            @click="showNew = !showNew"
                            class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <span x-show="!showNew">Show</span>
                            <span x-show="showNew">Hide</span>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Minimum 8 characters. To change your password, enter your current password above and a new password here.
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        Confirm new password
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            :type="showConfirm ? 'text' : 'password'"
                            name="password_confirmation"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-200 bg-transparent px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                        <button type="button"
                            @click="showConfirm = !showConfirm"
                            class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <span x-show="!showConfirm">Show</span>
                            <span x-show="showConfirm">Hide</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/40">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
