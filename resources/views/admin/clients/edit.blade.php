@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Client — {{ $client->name }}</h1>
        <a href="{{ route('admin.clients.show', $client) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.clients.update', $client) }}"
          class="rounded-xl border border-gray-200 bg-white p-8 space-y-6 dark:border-gray-800 dark:bg-gray-900">
        @csrf
        @method('PATCH')

        @if($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 dark:bg-red-900/20 dark:border-red-800">
                <ul class="list-disc pl-4 text-sm text-red-700 dark:text-red-400 space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Identity --}}
        <div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Identity</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                    <input name="name" value="{{ old('name', $client->name) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name</label>
                    <input name="company_name" value="{{ old('company_name', $client->company_name) }}"
                           class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input name="email" type="email" value="{{ old('email', $client->email) }}"
                           class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input name="phone" value="{{ old('phone', $client->phone) }}"
                           class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea name="address" rows="2"
                              class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('address', $client->address) }}</textarea>
                </div>
            </div>
        </div>

        <hr class="border-gray-100 dark:border-gray-800">

        {{-- Finance --}}
        <div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Finance</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                    <select name="currency" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        @foreach(['BDT','USD','EUR','CNY','GBP'] as $cur)
                            <option value="{{ $cur }}" @selected(old('currency', $client->currency) === $cur)>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credit Limit</label>
                    <input name="credit_limit" type="number" min="0" step="0.01" value="{{ old('credit_limit', $client->credit_limit) }}"
                           class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Withholding Rate (%)</label>
                    <input name="withholding_rate" type="number" min="0" max="100" step="0.01" value="{{ old('withholding_rate', $client->withholding_rate) }}"
                           class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
        </div>

        <hr class="border-gray-100 dark:border-gray-800">

        {{-- Assignment --}}
        <div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Assignment</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned Agent (Salesperson)</label>
                    <select name="agent_id" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">None</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" @selected(old('agent_id', $client->agent_id) == $agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $client->is_active))
                               class="rounded border-gray-300 text-brand-600 dark:border-gray-700">
                        Active
                    </label>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea name="notes" rows="3"
                      class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('notes', $client->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Delete this client? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline dark:text-red-400">Delete client</button>
            </form>
            <div class="flex gap-3">
                <a href="{{ route('admin.clients.show', $client) }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</a>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-medium text-white hover:bg-brand-700">Save Changes</button>
            </div>
        </div>
    </form>

</div>
@endsection
