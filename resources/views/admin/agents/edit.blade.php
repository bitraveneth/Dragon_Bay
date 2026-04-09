@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Edit Agent
            </h1>
            <div class="mt-2 flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $agent->name }}</span>
                @if($agent->code)
                    <span class="inline-flex h-1 w-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Code: {{ $agent->code }}</span>
                @endif
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Update contact, area, and banking details.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.agents.show', $agent) }}" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View Details
            </a>
            <a href="{{ route('admin.agents.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Agents
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    <span class="text-sm font-semibold text-brand-700 dark:text-brand-400">
                        {{ substr($agent->name, 0, 1) }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Agent Information</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Agent since {{ $agent->created_at->format('M d, Y') }} · 
                        Last updated {{ $agent->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.agents.update', $agent) }}" method="POST" class="p-6" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <!-- Contact & Zone Section -->
            <div class="space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contact & Zone</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Basic contact information and territory assignment.</p>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Name -->
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name <span class="text-error-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $agent->name) }}" required
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., Md. Rahim Uddin">
                        @error('name')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $agent->email) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="rahim@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Phone
                        </label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $agent->phone) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="+880 1XXX-XXXXXX">
                        @error('phone')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Area -->
                    <div>
                        <label for="area" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Area
                        </label>
                        <input type="text" id="area" name="area" value="{{ old('area', $agent->area) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., Mirpur">
                        @error('area')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Zone -->
                    <div>
                        <label for="zone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Zone
                        </label>
                        <input type="text" id="zone" name="zone" value="{{ old('zone', $agent->zone) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., Dhaka North">
                        @error('zone')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Location Code -->
                    <div>
                        <label for="location_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Location Code
                        </label>
                        <input type="text" id="location_code" name="location_code" value="{{ old('location_code', $agent->location_code) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., DHC-001">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Special area/dealer code for this agent.
                        </p>
                        @error('location_code')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Special Code -->
                    <div>
                        <label for="special_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Special Code
                        </label>
                        <input type="text" id="special_code" name="special_code" value="{{ old('special_code', $agent->special_code) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., SR-2024-001">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Internal SR/dealer code if needed.
                        </p>
                        @error('special_code')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Financial/KYC Section -->
            <div class="mt-8 space-y-4">
                <div class="border-b border-gray-100 pb-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Financial & KYC</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Banking details, credit limits, and compliance documents.</p>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Withholding Tax Rate -->
                    <div>
                        <label for="withholding_rate" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Withholding Tax Rate (%)
                        </label>
                        <input type="number" id="withholding_rate" name="withholding_rate" 
                               step="0.01" min="0" max="100" value="{{ old('withholding_rate', $agent->withholding_rate) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="e.g., 3">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Example: 3 means 3% withholding tax will be deducted from this agent's invoices.
                        </p>
                        @error('withholding_rate')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Credit Limit -->
                    <div>
                        <label for="credit_limit" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Credit Limit (BDT)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
                            <input type="number" id="credit_limit" name="credit_limit" 
                                   step="0.01" value="{{ old('credit_limit', $agent->credit_limit) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                   placeholder="0.00">
                        </div>
                        @error('credit_limit')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Parent Agent -->
                    <div>
                        <label for="parent_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Parent Agent
                        </label>
                        <select id="parent_id" name="parent_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">None (Top-level agent)</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"{{ old('parent_id', $agent->parent_id) == $parent->id ? ' selected' : '' }}>
                                    {{ $parent->name }} @if($parent->area) ({{ $parent->area }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bank Details (Full Width) -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="bank_details" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Bank Details
                        </label>
                        <textarea id="bank_details" name="bank_details" rows="3"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                  placeholder="Account holder name, bank name, branch, account number, routing number, etc.">{{ old('bank_details', $agent->bank_details) }}</textarea>
                        @error('bank_details')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- KYC Documents (Comma-separated) -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="kyc_documents" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            KYC Documents (Comma-separated)
                        </label>
                        @php
                            $kyc = is_array($agent->kyc_documents) ? implode(', ', $agent->kyc_documents) : ($agent->kyc_documents ?? '');
                        @endphp
                        <input type="text" id="kyc_documents" name="kyc_documents[]" value="{{ old('kyc_documents.0', $kyc) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                               placeholder="Trade License, TIN Certificate, NID, Passport, etc.">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Add multiple documents separated by commas.
                        </p>
                        @error('kyc_documents')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- KYC Files Upload -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="kyc_files" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Upload Additional KYC Documents
                        </label>
                        <input type="file" id="kyc_files" name="kyc_files[]" multiple
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:file:bg-gray-700 dark:file:text-gray-300">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Optional: Upload additional documents. New files will be added alongside existing KYC records.
                        </p>
                        @error('kyc_files')
                            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Active Status -->
            <div class="mt-8">
                <input type="hidden" name="is_active" value="0">
                <label for="is_active" class="inline-flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" 
                           {{ old('is_active', $agent->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 bg-white text-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Inactive agents cannot place orders or access the system.
                </p>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <a href="{{ route('admin.agents.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-theme-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="rounded-2xl border border-error-200 bg-white shadow-theme-sm dark:border-error-800/30 dark:bg-gray-900">
        <div class="border-b border-error-100 px-6 py-4 dark:border-error-800/20">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-medium text-error-700 dark:text-error-400">Danger Zone</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Delete this agent</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Once deleted, this agent and all associated data cannot be recovered.
                        This action cannot be undone.
                    </p>
                </div>
                <form action="{{ route('admin.agents.destroy', $agent) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete {{ $agent->name }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-semibold text-white shadow-theme-xs hover:bg-error-600 focus:outline-none focus:ring-2 focus:ring-error-500/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Agent
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection