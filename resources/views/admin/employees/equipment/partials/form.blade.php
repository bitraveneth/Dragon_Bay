<div class="form-section">
    <div class="section-header">
        <h3>Equipment details</h3>
    </div>
    <div class="form-grid">
        <div class="full-width">
            <p class="metric-label">Employee</p>
            <p>{{ $employee->name }}</p>
        </div>
        <div>
            <label for="effective_date">Effective date</label>
            <input id="effective_date" name="effective_date" type="date" value="{{ old('effective_date', optional($item->effective_date)->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="product_name">Product name</label>
            <input id="product_name" name="product_name" value="{{ old('product_name', $item->product_name ?? '') }}" required>
            <small class="text-muted">Example: Tablet, Phone, Laptop, Device.</small>
        </div>
        <div>
            <label for="device_identifier">Device ID / IMEI</label>
            <input id="device_identifier" name="device_identifier" value="{{ old('device_identifier', $item->device_identifier ?? '') }}">
        </div>
        <div>
            <label for="status">Status</label>
            @php
                $currentStatus = old('status', $item->status ?? 'active');
            @endphp
            <select id="status" name="status">
                <option value="active"{{ $currentStatus === 'active' ? ' selected' : '' }}>Active</option>
                <option value="returned"{{ $currentStatus === 'returned' ? ' selected' : '' }}>Returned</option>
                <option value="lost"{{ $currentStatus === 'lost' ? ' selected' : '' }}>Lost</option>
            </select>
        </div>
        <div class="full-width">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ old('notes', $item->notes ?? '') }}</textarea>
        </div>
    </div>
</div>
