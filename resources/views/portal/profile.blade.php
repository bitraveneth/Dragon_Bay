@extends('layouts.app')

@section('title', 'Client Profile')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Profile</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Client account, contact details, and portal access information.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
            <h2 class="text-lg font-semibold">Client Details</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 text-sm">
                <div><div class="text-xs uppercase text-gray-500">Name</div><div class="mt-1">{{ $client->name }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Email</div><div class="mt-1">{{ $client->email ?: $user->email }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Phone</div><div class="mt-1">{{ $client->phone ?: '-' }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Credit Limit</div><div class="mt-1">BDT {{ number_format((float) $client->credit_limit, 2) }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Area</div><div class="mt-1">{{ $client->area ?: '-' }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Zone</div><div class="mt-1">{{ $client->zone ?: '-' }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Portal Login</div><div class="mt-1">{{ $user->email }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Status</div><div class="mt-1">{{ $client->is_active ? 'Active' : 'Inactive' }}</div></div>
            </div>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold">Activity</h2>
            <div class="mt-4 space-y-4 text-sm">
                <div><div class="text-xs uppercase text-gray-500">Orders</div><div class="mt-1 text-xl font-semibold">{{ number_format($stats['orders']) }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Shipments</div><div class="mt-1 text-xl font-semibold">{{ number_format($stats['shipments']) }}</div></div>
                <div><div class="text-xs uppercase text-gray-500">Invoices</div><div class="mt-1 text-xl font-semibold">{{ number_format($stats['invoices']) }}</div></div>
            </div>
        </section>
    </div>
</div>
@endsection
