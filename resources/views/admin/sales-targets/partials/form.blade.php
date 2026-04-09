<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="agent_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Agent Target</label>
        <select id="agent_id" name="agent_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">Select agent</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" {{ old('agent_id', $salesTarget?->agent_id) == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
            @endforeach
        </select>
        @error('agent_id')
            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="employee_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Employee Target</label>
        <select id="employee_id" name="employee_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <option value="">Select employee</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" {{ old('employee_id', $salesTarget?->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->name }}{{ $employee->work_zone ? ' · '.$employee->work_zone : '' }}</option>
            @endforeach
        </select>
        @error('employee_id')
            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="period_start" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Period Start</label>
        <input id="period_start" name="period_start" type="date" value="{{ old('period_start', optional($salesTarget?->period_start)->format('Y-m-d') ?? now()->startOfMonth()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        @error('period_start')
            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="period_end" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Period End</label>
        <input id="period_end" name="period_end" type="date" value="{{ old('period_end', optional($salesTarget?->period_end)->format('Y-m-d') ?? now()->endOfMonth()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        @error('period_end')
            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="target_value" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Target Value</label>
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">BDT</span>
            <input id="target_value" name="target_value" type="number" step="0.01" min="0.01" value="{{ old('target_value', $salesTarget?->target_value) }}" class="w-full rounded-lg border border-gray-300 bg-white pl-12 pr-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Achievement is measured from invoiced net sales. Employee targets use the employee work zone when available.</p>
        @error('target_value')
            <p class="mt-1 text-sm text-error-600 dark:text-error-500">{{ $message }}</p>
        @enderror
    </div>
</div>
