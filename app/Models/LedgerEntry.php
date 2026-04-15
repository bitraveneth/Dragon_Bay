<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use App\Support\Currency;

class LedgerEntry extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (LedgerEntry $entry) {
            $entry->hydrateLogisticsContext();
        });

        static::updating(function () {
            throw new \LogicException('LedgerEntry records are immutable. Create a correcting entry instead.');
        });

        static::deleting(function () {
            throw new \LogicException('LedgerEntry records cannot be deleted. Create a correcting entry instead.');
        });
    }

    protected $fillable = [
        'account',
        'description',
        'debit',
        'credit',
        'currency_code',
        'source_currency_code',
        'exchange_rate',
        'source_debit',
        'source_credit',
        'order_id',
        'invoice_id',
        'client_id',
        'shipment_id',
        'shipment_expense_id',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'source_debit' => 'decimal:2',
        'source_credit' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function shipmentExpense()
    {
        return $this->belongsTo(ShipmentExpense::class);
    }

    protected function hydrateLogisticsContext(): void
    {
        if (! Schema::hasTable('ledger_entries')) {
            return;
        }

        $hasClientColumn = Schema::hasColumn('ledger_entries', 'client_id');
        $hasShipmentColumn = Schema::hasColumn('ledger_entries', 'shipment_id');

        if (! $hasClientColumn && ! $hasShipmentColumn) {
            $this->hydrateCurrencyContext();

            return;
        }

        if ($this->invoice_id) {
            $invoice = Invoice::with(['order', 'shipment'])->find($this->invoice_id);

            if ($invoice) {
                if ($hasShipmentColumn && ! $this->shipment_id && $invoice->shipment_id) {
                    $this->shipment_id = $invoice->shipment_id;
                }

                if (! $this->order_id && $invoice->order_id) {
                    $this->order_id = $invoice->order_id;
                }

                if ($hasClientColumn && ! $this->client_id) {
                    $this->client_id = $invoice->shipment?->client_id
                        ?? $invoice->order?->client_id
                        ?? null;
                }
            }
        }

        if ($this->shipment_id && $hasClientColumn && ! $this->client_id) {
            $shipment = Shipment::find($this->shipment_id);
            $this->client_id = $shipment?->client_id;
        }

        if ($this->order_id && $hasClientColumn && ! $this->client_id) {
            $order = Order::find($this->order_id);
            $this->client_id = $order?->client_id;
        }

        $this->hydrateCurrencyContext();
    }

    protected function hydrateCurrencyContext(): void
    {
        if (! Schema::hasColumn('ledger_entries', 'currency_code')) {
            return;
        }

        $sourceCurrency = $this->source_currency_code ?: null;
        $exchangeRate = $this->exchange_rate ?: null;

        if ($this->invoice_id) {
            $invoice = Invoice::find($this->invoice_id);
            $sourceCurrency = $sourceCurrency ?: $invoice?->currency_code;
            $exchangeRate = $exchangeRate ?: $invoice?->exchange_rate;
        }

        $sourceCurrency = Currency::normalizeCode($sourceCurrency ?: Currency::baseCode());
        $exchangeRate = Currency::exchangeRateFor($sourceCurrency, $exchangeRate);

        $this->currency_code = Currency::baseCode();
        $this->source_currency_code = $sourceCurrency;
        $this->exchange_rate = $exchangeRate;

        if (Schema::hasColumn('ledger_entries', 'source_debit') && $this->source_debit === null) {
            $this->source_debit = round((float) $this->debit, 2);
            $this->debit = Currency::toBase($this->source_debit, $exchangeRate);
        }

        if (Schema::hasColumn('ledger_entries', 'source_credit') && $this->source_credit === null) {
            $this->source_credit = round((float) $this->credit, 2);
            $this->credit = Currency::toBase($this->source_credit, $exchangeRate);
        }
    }
}
