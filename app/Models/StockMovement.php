<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_entry_id',
        'order_id',
        'type',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function typeMeta(string $type): array
    {
        $meta = [
            'receipt' => [
                'label' => 'Receipt',
                'badge' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                'bar' => 'bg-success-500',
            ],
            'goods-receipt' => [
                'label' => 'Goods Receipt',
                'badge' => 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
                'bar' => 'bg-success-500',
            ],
            'goods-receipt-reversal' => [
                'label' => 'GRN Reversal',
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                'bar' => 'bg-orange-500',
            ],
            'production-output' => [
                'label' => 'Production Output',
                'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400',
                'bar' => 'bg-emerald-500',
            ],
            'production-consumption' => [
                'label' => 'Production Consumption',
                'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                'bar' => 'bg-amber-500',
            ],
            'reservation-in' => [
                'label' => 'Reservation In',
                'badge' => 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400',
                'bar' => 'bg-warning-500',
            ],
            'reservation-out' => [
                'label' => 'Reservation Out',
                'badge' => 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400',
                'bar' => 'bg-warning-500',
            ],
            'reservation-release-in' => [
                'label' => 'Reservation Release In',
                'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-400',
                'bar' => 'bg-sky-500',
            ],
            'reservation-release-out' => [
                'label' => 'Reservation Release Out',
                'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-400',
                'bar' => 'bg-sky-500',
            ],
            'sale' => [
                'label' => 'Sale',
                'badge' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                'bar' => 'bg-error-500',
            ],
            'delivery' => [
                'label' => 'Delivery',
                'badge' => 'bg-error-100 text-error-700 dark:bg-error-500/20 dark:text-error-400',
                'bar' => 'bg-error-500',
            ],
            'customer-return' => [
                'label' => 'Customer Return',
                'badge' => 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
                'bar' => 'bg-brand-500',
            ],
            'transfer' => [
                'label' => 'Transfer',
                'badge' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                'bar' => 'bg-blue-light-500',
            ],
            'transfer-in' => [
                'label' => 'Transfer In',
                'badge' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                'bar' => 'bg-blue-light-500',
            ],
            'transfer-out' => [
                'label' => 'Transfer Out',
                'badge' => 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400',
                'bar' => 'bg-blue-light-500',
            ],
            'write-off' => [
                'label' => 'Write-Off',
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                'bar' => 'bg-orange-500',
            ],
            'expired' => [
                'label' => 'Expired',
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                'bar' => 'bg-orange-500',
            ],
            'wasted' => [
                'label' => 'Wasted',
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                'bar' => 'bg-orange-500',
            ],
            'supplier-return' => [
                'label' => 'Supplier Return',
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                'bar' => 'bg-orange-500',
            ],
            'production-loss' => [
                'label' => 'Production Loss',
                'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                'bar' => 'bg-orange-500',
            ],
            'other' => [
                'label' => 'Other Write-Off',
                'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                'bar' => 'bg-gray-500',
            ],
            'adjustment' => [
                'label' => 'Adjustment',
                'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                'bar' => 'bg-purple-500',
            ],
        ];

        return $meta[$type] ?? [
            'label' => str($type)->replace('-', ' ')->title()->value(),
            'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            'bar' => 'bg-gray-500',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return static::typeMeta($this->type)['label'];
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return static::typeMeta($this->type)['badge'];
    }

    public function getTypeBarClassAttribute(): string
    {
        return static::typeMeta($this->type)['bar'];
    }

    public function getQuantityClassAttribute(): string
    {
        return (float) $this->quantity >= 0
            ? 'text-success-600 dark:text-success-400'
            : 'text-error-600 dark:text-error-500';
    }

    public function getQuantityPrefixAttribute(): string
    {
        return (float) $this->quantity > 0 ? '+' : '';
    }

    public static function recordFor(
        StockEntry $entry,
        string $type,
        float $quantity,
        ?string $notes = null,
        ?int $orderId = null
    ): self {
        return self::create([
            'stock_entry_id' => $entry->id,
            'order_id' => $orderId,
            'type' => $type,
            'quantity' => $quantity,
            'notes' => $notes,
        ]);
    }
}
