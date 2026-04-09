<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'order_item_id',
        'product_id',
        'batch_id',
        'qty_dispatched',
        'qty_delivered',
        'qty_short',
        'qty_damaged',
        'notes',
    ];

    protected $casts = [
        'qty_dispatched' => 'decimal:2',
        'qty_delivered' => 'decimal:2',
        'qty_short' => 'decimal:2',
        'qty_damaged' => 'decimal:2',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public static function summarizeForOrderItems(iterable $items): Collection
    {
        return collect($items)
            ->groupBy(function ($item) {
                return (int) $item->order_item_id;
            })
            ->map(function (Collection $rows) {
                $qtyDispatched = (float) $rows->sum('qty_dispatched');
                $qtyDelivered = (float) $rows->sum('qty_delivered');
                $qtyShort = (float) $rows->sum('qty_short');
                $qtyDamaged = (float) $rows->sum('qty_damaged');

                return [
                    'qty_dispatched' => $qtyDispatched,
                    'qty_delivered' => $qtyDelivered,
                    'qty_short' => $qtyShort,
                    'qty_damaged' => $qtyDamaged,
                    'realized_quantity' => self::realizedQuantityFromTotals(
                        $qtyDispatched,
                        $qtyDelivered,
                        $qtyShort,
                        $qtyDamaged
                    ),
                ];
            });
    }

    public static function realizedQuantityFromTotals(
        float $qtyDispatched,
        float $qtyDelivered,
        float $qtyShort,
        float $qtyDamaged
    ): float {
        if (
            $qtyDelivered <= 0
            && ($qtyDispatched > 0 || $qtyShort > 0 || $qtyDamaged > 0)
        ) {
            return max($qtyDispatched - $qtyShort - $qtyDamaged, 0);
        }

        return max($qtyDelivered, 0);
    }
}
