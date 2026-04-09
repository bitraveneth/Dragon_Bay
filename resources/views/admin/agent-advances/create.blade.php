@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Record Agent Advance</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Capture prepayments that will auto-apply against the next invoices for the same agent.</p>
    </div>

    <form action="{{ route('admin.agent-advances.store') }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="agent_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Agent</label>
                <select id="agent_id" name="agent_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Select agent</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ old('agent_id', $selectedAgent) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
                @error('agent_id')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                @error('amount')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="advanced_at" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Advance Date</label>
                <input id="advanced_at" name="advanced_at" type="date" value="{{ old('advanced_at', now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                @error('advanced_at')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="payment_method" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                <select id="payment_method" name="payment_method" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Select method</option>
                    @foreach(['cash' => 'Cash', 'bkash' => 'bKash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque'] as $value => $label)
                        <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reference" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Reference</label>
                <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                @error('reference')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.agent-advances.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">Cancel</a>
            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-sm hover:bg-brand-600">Record Advance</button>
        </div>
    </form>
</div>
@endsection
