<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SystemSettings;
use App\Http\Controllers\Controller;
use App\Support\DatabaseBackupManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SystemSettingController extends Controller
{
    protected const CURRENCY_CODES = [
        'AED',
        'AUD',
        'BDT',
        'CAD',
        'CHF',
        'CNY',
        'EUR',
        'GBP',
        'HKD',
        'INR',
        'JPY',
        'KWD',
        'MYR',
        'NPR',
        'PKR',
        'QAR',
        'SAR',
        'SGD',
        'THB',
        'USD',
    ];

    public function index(DatabaseBackupManager $backupManager)
    {
        $baseCurrencyCode = strtoupper((string) SystemSettings::get('currency_code', config('app.currency', 'BDT')));

        return view('admin.settings.index', [
            'settings' => [
                'app_name' => config('app.name'),
                'brand_name' => SystemSettings::get('brand_name'),
                'company_name' => SystemSettings::get('company_name'),
                'company_email' => SystemSettings::get('company_email'),
                'company_phone' => SystemSettings::get('company_phone'),
                'company_address' => SystemSettings::get('company_address'),
                'currency_code' => $baseCurrencyCode,
                'currency_symbol' => SystemSettings::get('currency_symbol', '৳'),
                'exchange_rates' => $this->exchangeRateSettings($baseCurrencyCode),
                'brand_primary_color' => SystemSettings::sanitizeHexColor(SystemSettings::get('brand_primary_color')) ?? '#465FFF',
                'brand_secondary_color' => SystemSettings::sanitizeHexColor(SystemSettings::get('brand_secondary_color')) ?? '#3641F5',
                'text_color_light' => SystemSettings::sanitizeHexColor(SystemSettings::get('text_color_light')) ?? SystemSettings::defaultTextColorLight(),
                'text_color_dark' => SystemSettings::sanitizeHexColor(SystemSettings::get('text_color_dark')) ?? SystemSettings::defaultTextColorDark(),
                'default_theme_mode' => SystemSettings::defaultThemeMode(),
                'smtp_host' => SystemSettings::get('smtp_host', config('mail.mailers.smtp.host')),
                'smtp_port' => SystemSettings::get('smtp_port', config('mail.mailers.smtp.port')),
                'smtp_encryption' => SystemSettings::get('smtp_encryption', config('mail.mailers.smtp.encryption')),
                'smtp_username' => SystemSettings::get('smtp_username', config('mail.mailers.smtp.username')),
                'smtp_password' => SystemSettings::get('smtp_password', config('mail.mailers.smtp.password')),
                'mail_from_address' => SystemSettings::get('mail_from_address', config('mail.from.address')),
                'mail_from_name' => SystemSettings::get('mail_from_name', config('mail.from.name')),
                'sms_provider' => SystemSettings::get('sms_provider'),
                'sms_base_url' => SystemSettings::get('sms_base_url'),
                'sms_api_key' => SystemSettings::get('sms_api_key'),
                'sms_api_secret' => SystemSettings::get('sms_api_secret'),
                'sms_sender_id' => SystemSettings::get('sms_sender_id'),
            ],
            'logoUrl' => SystemSettings::logoUrl(),
            'brandThemeVariables' => SystemSettings::brandThemeVariables(),
            'defaultThemeColors' => [
                'primary' => SystemSettings::defaultBrandPrimary(),
                'secondary' => SystemSettings::defaultBrandSecondary(),
                'textLight' => SystemSettings::defaultTextColorLight(),
                'textDark' => SystemSettings::defaultTextColorDark(),
            ],
            'exchangeRateCurrencies' => self::CURRENCY_CODES,
            'backups' => $backupManager->list(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'brand_name' => 'nullable|string|max:120',
            'company_name' => 'nullable|string|max:120',
            'company_email' => 'nullable|email|max:120',
            'company_phone' => 'nullable|string|max:40',
            'company_address' => 'nullable|string|max:500',
            'currency_code' => 'required|string|max:10|in:' . implode(',', self::CURRENCY_CODES),
            'currency_symbol' => 'nullable|string|max:10',
            'exchange_rates' => 'nullable|array',
            'exchange_rates.*' => 'nullable|numeric|min:0.000001|max:999999999',
            'brand_primary_color' => ['required', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'brand_secondary_color' => ['required', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'text_color_light' => ['required', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'text_color_dark' => ['required', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'default_theme_mode' => 'required|in:dark,light,system',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:120',
            'mail_from_name' => 'nullable|string|max:120',
            'sms_provider' => 'nullable|string|max:120',
            'sms_base_url' => 'nullable|string|max:255',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_api_secret' => 'nullable|string|max:255',
            'sms_sender_id' => 'nullable|string|max:120',
            'company_logo' => 'nullable|image|max:2048',
            'remove_logo' => 'nullable|boolean',
        ]);

        $settings = [];

        foreach ([
            'brand_name',
            'company_name',
            'company_email',
            'company_phone',
            'company_address',
            'currency_code',
            'currency_symbol',
            'brand_primary_color',
            'brand_secondary_color',
            'text_color_light',
            'text_color_dark',
            'default_theme_mode',
            'smtp_host',
            'smtp_port',
            'smtp_encryption',
            'smtp_username',
            'smtp_password',
            'mail_from_address',
            'mail_from_name',
            'sms_provider',
            'sms_base_url',
            'sms_api_key',
            'sms_api_secret',
            'sms_sender_id',
        ] as $key) {
            $value = $data[$key] ?? null;
            $settings[$key] = is_string($value) ? trim($value) : $value;
        }

        foreach (['brand_primary_color', 'brand_secondary_color', 'text_color_light', 'text_color_dark'] as $colorKey) {
            $settings[$colorKey] = SystemSettings::sanitizeHexColor($settings[$colorKey] ?? null);
        }

        $baseCurrencyCode = strtoupper(trim((string) $settings['currency_code']));
        $settings['currency_code'] = $baseCurrencyCode;
        $exchangeRateKeysToForget = [];

        foreach (self::CURRENCY_CODES as $currencyCode) {
            if ($currencyCode === $baseCurrencyCode) {
                continue;
            }

            $key = $this->exchangeRateKey($currencyCode, $baseCurrencyCode);
            $value = $data['exchange_rates'][$currencyCode] ?? null;

            if ($value !== null && $value !== '') {
                $settings[$key] = round((float) $value, 6);
            } else {
                $exchangeRateKeysToForget[] = $key;
            }
        }

        SystemSettings::forget(['app_name']);

        $existingLogoPath = SystemSettings::get('company_logo_path');

        if ($request->boolean('remove_logo') && $existingLogoPath) {
            Storage::disk('public')->delete($existingLogoPath);
            SystemSettings::forget(['company_logo_path']);
        }

        if ($request->hasFile('company_logo')) {
            if ($existingLogoPath) {
                Storage::disk('public')->delete($existingLogoPath);
            }

            $settings['company_logo_path'] = $request->file('company_logo')->store('system-settings', 'public');
        }

        SystemSettings::forget($exchangeRateKeysToForget);
        SystemSettings::putMany($settings);

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'System settings updated.');
    }

    public function createBackup(DatabaseBackupManager $backupManager)
    {
        try {
            $filename = $backupManager->create();
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['backup' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('status', "Database backup created: {$filename}");
    }

    public function downloadBackup(string $filename, DatabaseBackupManager $backupManager)
    {
        $path = $backupManager->relativePath($filename);

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, basename($filename));
    }

    public function restoreBackup(Request $request, DatabaseBackupManager $backupManager)
    {
        $data = $request->validate([
            'filename' => 'required|string',
            'confirm_restore' => 'required|accepted',
        ]);

        $safetyBackup = null;

        try {
            $safetyBackup = $backupManager->create();
            $backupManager->restore($data['filename']);
        } catch (RuntimeException $exception) {
            if ($safetyBackup) {
                try {
                    $backupManager->restore($safetyBackup);
                } catch (RuntimeException $rollbackException) {
                    return redirect()
                        ->route('admin.settings.index')
                        ->withErrors([
                            'backup' => $exception->getMessage() . ' Automatic rollback also failed. Safety backup: ' . $safetyBackup,
                        ]);
                }
            }

            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['backup' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Database restored from backup. A pre-restore safety backup was created as ' . $safetyBackup . '.');
    }

    protected function exchangeRateSettings(string $baseCurrencyCode): array
    {
        $baseCurrencyCode = strtoupper($baseCurrencyCode);
        $rates = [];

        foreach (self::CURRENCY_CODES as $currencyCode) {
            if ($currencyCode === $baseCurrencyCode) {
                continue;
            }

            $rates[$currencyCode] = SystemSettings::get($this->exchangeRateKey($currencyCode, $baseCurrencyCode));
        }

        return $rates;
    }

    protected function exchangeRateKey(string $currencyCode, string $baseCurrencyCode): string
    {
        return 'exchange_rate_' . strtoupper($currencyCode) . '_' . strtoupper($baseCurrencyCode);
    }
}
