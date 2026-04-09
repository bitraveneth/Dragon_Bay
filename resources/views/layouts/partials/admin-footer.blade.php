<footer class="admin-footer">
    <small>
        &copy; {{ date('Y') }} {{ $legalCompanyName ?? config('app.name', 'ERP') }} admin.
        <span class="ml-2 text-gray-500">v{{ config('app.version', 'dev') }}</span>
    </small>
</footer>
