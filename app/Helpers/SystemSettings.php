<?php

namespace App\Helpers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SystemSettings
{
    protected static ?array $cache = null;

    protected static string $defaultBrandPrimary = '#465fff';

    protected static string $defaultBrandSecondary = '#3641f5';

    protected static string $defaultTextColorLight = '#101828';

    protected static string $defaultTextColorDark = '#F9FAFB';

    protected static array $encryptedKeys = [
        'smtp_password',
        'sms_api_key',
        'sms_api_secret',
    ];

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            if (! Schema::hasTable('system_settings')) {
                return self::$cache = [];
            }
        } catch (Throwable $e) {
            return self::$cache = [];
        }

        $settings = SystemSetting::query()
            ->pluck('value', 'key')
            ->mapWithKeys(fn ($value, $key) => [$key => self::decodeValue($key, $value)])
            ->all();

        return self::$cache = $settings;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = self::all();

        if (! array_key_exists($key, $settings)) {
            return $default;
        }

        $value = $settings[$key];

        return $value === null || $value === '' ? $default : $value;
    }

    public static function putMany(array $values): void
    {
        if (empty($values)) {
            return;
        }

        $timestamp = now();
        $payload = [];

        foreach ($values as $key => $value) {
            $payload[] = [
                'key' => $key,
                'value' => self::encodeValue($key, $value),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        SystemSetting::query()->upsert($payload, ['key'], ['value', 'updated_at']);
        self::flush();
    }

    public static function forget(array $keys): void
    {
        if (empty($keys)) {
            return;
        }

        SystemSetting::query()->whereIn('key', $keys)->delete();
        self::flush();
    }

    public static function flush(): void
    {
        self::$cache = null;
    }

    public static function logoUrl(): ?string
    {
        $path = self::get('company_logo_path');

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public static function defaultBrandPrimary(): string
    {
        return strtoupper(self::$defaultBrandPrimary);
    }

    public static function defaultBrandSecondary(): string
    {
        return strtoupper(self::$defaultBrandSecondary);
    }

    public static function defaultTextColorLight(): string
    {
        return strtoupper(self::$defaultTextColorLight);
    }

    public static function defaultTextColorDark(): string
    {
        return strtoupper(self::$defaultTextColorDark);
    }

    public static function defaultThemeMode(): string
    {
        $themeMode = self::get('default_theme_mode', 'dark');

        return in_array($themeMode, ['dark', 'light', 'system'], true) ? $themeMode : 'dark';
    }

    public static function appName(): string
    {
        $name = config('app.name', 'ERP');

        return is_string($name) && trim($name) !== '' ? trim($name) : 'ERP';
    }

    public static function brandName(?string $fallback = null): string
    {
        $fallback = is_string($fallback) && trim($fallback) !== ''
            ? trim($fallback)
            : self::appName();

        $brandName = self::get('brand_name');

        if (is_string($brandName) && trim($brandName) !== '') {
            return trim($brandName);
        }

        $companyName = self::get('company_name');

        if (is_string($companyName) && trim($companyName) !== '') {
            return trim($companyName);
        }

        return $fallback;
    }

    public static function legalCompanyName(?string $fallback = null): string
    {
        $fallback = is_string($fallback) && trim($fallback) !== ''
            ? trim($fallback)
            : self::appName();

        $companyName = self::get('company_name');

        if (is_string($companyName) && trim($companyName) !== '') {
            return trim($companyName);
        }

        return self::brandName($fallback);
    }

    public static function initials(?string $label = null, int $limit = 2): string
    {
        $label = is_string($label) && trim($label) !== ''
            ? trim($label)
            : self::brandName();

        $parts = preg_split('/[^A-Za-z0-9]+/', $label, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $seed = collect($parts)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
        $source = $seed !== '' ? $seed : $label;

        return strtoupper(mb_substr($source, 0, max(1, $limit)));
    }

    public static function brandThemeVariables(): array
    {
        $primary = self::sanitizeHexColor(self::get('brand_primary_color'))
            ?? self::$defaultBrandPrimary;
        $secondary = self::sanitizeHexColor(self::get('brand_secondary_color'))
            ?? self::$defaultBrandSecondary;
        $textLight = self::sanitizeHexColor(self::get('text_color_light'))
            ?? self::$defaultTextColorLight;
        $textDark = self::sanitizeHexColor(self::get('text_color_dark'))
            ?? self::$defaultTextColorDark;

        return [
            '--color-brand-25' => self::mixWithWhite($primary, 0.95),
            '--color-brand-50' => self::mixWithWhite($primary, 0.92),
            '--color-brand-100' => self::mixWithWhite($primary, 0.84),
            '--color-brand-200' => self::mixWithWhite($primary, 0.72),
            '--color-brand-300' => self::mixWithWhite($primary, 0.52),
            '--color-brand-400' => self::mixWithWhite($primary, 0.28),
            '--color-brand-500' => $primary,
            '--color-brand-600' => $secondary,
            '--color-brand-700' => self::mixWithBlack($secondary, 0.12),
            '--color-brand-800' => self::mixWithBlack($secondary, 0.28),
            '--color-brand-900' => self::mixWithBlack($secondary, 0.44),
            '--color-brand-950' => self::mixWithBlack($secondary, 0.70),
            '--color-app-text-light' => $textLight,
            '--color-app-text-dark' => $textDark,
            '--shadow-focus-ring' => '0px 0px 0px 4px ' . self::rgba($primary, 0.12),
        ];
    }

    public static function sanitizeHexColor(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (! preg_match('/^#?[0-9a-fA-F]{6}$/', $value)) {
            return null;
        }

        return '#' . ltrim(strtoupper($value), '#');
    }

    protected static function encodeValue(string $key, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (string) $value;

        if (in_array($key, self::$encryptedKeys, true)) {
            return Crypt::encryptString($value);
        }

        return $value;
    }

    protected static function decodeValue(string $key, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! in_array($key, self::$encryptedKeys, true)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable $e) {
            return $value;
        }
    }

    protected static function mixWithWhite(string $hex, float $ratio): string
    {
        return self::mixColors($hex, '#FFFFFF', $ratio);
    }

    protected static function mixWithBlack(string $hex, float $ratio): string
    {
        return self::mixColors($hex, '#000000', $ratio);
    }

    protected static function mixColors(string $first, string $second, float $ratio): string
    {
        $ratio = max(0, min(1, $ratio));

        [$r1, $g1, $b1] = self::hexToRgb($first);
        [$r2, $g2, $b2] = self::hexToRgb($second);

        $red = (int) round($r1 + (($r2 - $r1) * $ratio));
        $green = (int) round($g1 + (($g2 - $g1) * $ratio));
        $blue = (int) round($b1 + (($b2 - $b1) * $ratio));

        return sprintf('#%02X%02X%02X', $red, $green, $blue);
    }

    protected static function rgba(string $hex, float $alpha): string
    {
        [$red, $green, $blue] = self::hexToRgb($hex);
        $alpha = max(0, min(1, $alpha));

        return sprintf('rgba(%d, %d, %d, %.2f)', $red, $green, $blue, $alpha);
    }

    protected static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
