<?php

namespace App\Support;

use App\Helpers\SystemSettings;
use Illuminate\Support\Facades\Schema;

class Currency
{
    public static function baseCode(): string
    {
        return self::normalizeCode(SystemSettings::get('currency_code', config('app.currency', 'BDT')));
    }

    public static function normalizeCode(?string $currency): string
    {
        $currency = strtoupper(trim((string) $currency));

        return $currency !== '' ? substr($currency, 0, 10) : 'BDT';
    }

    public static function exchangeRateFor(?string $currency, mixed $provided = null): float
    {
        $currency = self::normalizeCode($currency);
        $base = self::baseCode();

        if ($provided !== null && (float) $provided > 0) {
            return round((float) $provided, 6);
        }

        if ($currency === $base) {
            return 1.0;
        }

        $configuredRate = self::configuredExchangeRateFor($currency);
        if ($configuredRate !== null) {
            return $configuredRate;
        }

        return 1.0;
    }

    public static function configuredExchangeRateFor(?string $currency): ?float
    {
        $currency = self::normalizeCode($currency);
        $base = self::baseCode();

        if ($currency === $base) {
            return 1.0;
        }

        foreach (['exchange_rate_' . $currency . '_' . $base, 'exchange_rate_' . $currency] as $key) {
            $rate = SystemSettings::get($key);
            if ($rate !== null && (float) $rate > 0) {
                return round((float) $rate, 6);
            }
        }

        return null;
    }

    public static function requiresExchangeRate(?string $currency, mixed $provided = null): bool
    {
        $currency = self::normalizeCode($currency);

        if ($currency === self::baseCode()) {
            return false;
        }

        if ($provided !== null && (float) $provided > 0) {
            return false;
        }

        return self::configuredExchangeRateFor($currency) === null;
    }

    public static function toBase(float|int|string|null $amount, float|int|string|null $exchangeRate): float
    {
        return round((float) $amount * max((float) $exchangeRate, 0), 2);
    }

    public static function invoicePayload(?string $currencyCode, mixed $exchangeRate = null): array
    {
        if (! self::tableHasColumns('invoices', ['currency_code', 'exchange_rate'])) {
            return [];
        }

        $currencyCode = self::normalizeCode($currencyCode);

        return [
            'currency_code' => $currencyCode,
            'exchange_rate' => self::exchangeRateFor($currencyCode, $exchangeRate),
        ];
    }

    public static function receiptPayload(?string $currencyCode, mixed $exchangeRate = null): array
    {
        if (! self::tableHasColumns('receipts', ['currency_code', 'exchange_rate'])) {
            return [];
        }

        $currencyCode = self::normalizeCode($currencyCode);

        return [
            'currency_code' => $currencyCode,
            'exchange_rate' => self::exchangeRateFor($currencyCode, $exchangeRate),
        ];
    }

    public static function creditNotePayload(?string $currencyCode, mixed $exchangeRate = null): array
    {
        if (! self::tableHasColumns('credit_notes', ['currency_code', 'exchange_rate'])) {
            return [];
        }

        $currencyCode = self::normalizeCode($currencyCode);

        return [
            'currency_code' => $currencyCode,
            'exchange_rate' => self::exchangeRateFor($currencyCode, $exchangeRate),
        ];
    }

    public static function ledgerPayload(
        array $payload,
        ?string $sourceCurrencyCode = null,
        mixed $exchangeRate = null,
        float|int|string|null $sourceDebit = null,
        float|int|string|null $sourceCredit = null
    ): array {
        if (! self::tableHasColumns('ledger_entries', [
            'currency_code',
            'source_currency_code',
            'exchange_rate',
            'source_debit',
            'source_credit',
        ])) {
            return $payload;
        }

        $sourceCurrencyCode = self::normalizeCode($sourceCurrencyCode ?: self::baseCode());
        $exchangeRate = self::exchangeRateFor($sourceCurrencyCode, $exchangeRate);
        $sourceDebit = $sourceDebit ?? $payload['debit'] ?? 0;
        $sourceCredit = $sourceCredit ?? $payload['credit'] ?? 0;

        $payload['currency_code'] = self::baseCode();
        $payload['source_currency_code'] = $sourceCurrencyCode;
        $payload['exchange_rate'] = $exchangeRate;
        $payload['source_debit'] = round((float) $sourceDebit, 2);
        $payload['source_credit'] = round((float) $sourceCredit, 2);
        $payload['debit'] = self::toBase($sourceDebit, $exchangeRate);
        $payload['credit'] = self::toBase($sourceCredit, $exchangeRate);

        return $payload;
    }

    protected static function tableHasColumns(string $table, array $columns): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }
}
