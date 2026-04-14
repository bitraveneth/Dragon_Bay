<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'order_type',
        'commission_rate',
        'commission_amount',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'pieces',
        'cbm',
        'chargeable_weight_kg',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'pieces' => 'integer',
        'cbm' => 'decimal:4',
        'chargeable_weight_kg' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->recalculateWeights();
        });
    }

    public function recalculateWeights(): void
    {
        $l = (float) ($this->length_cm ?? 0);
        $w = (float) ($this->width_cm ?? 0);
        $h = (float) ($this->height_cm ?? 0);
        $pieces = max((int) ($this->pieces ?? 1), 1);

        if ($l > 0 && $w > 0 && $h > 0) {
            $cbm = round(($l * $w * $h * $pieces) / 1_000_000, 4);
            $this->cbm = $cbm;

            // Volumetric weight: CBM × 167 (industry standard for air; use as default)
            $volumetricWeight = round($cbm * 167, 3);
            $actualWeight = (float) ($this->weight_kg ?? 0);
            $this->chargeable_weight_kg = $actualWeight > 0
                ? max($actualWeight, $volumetricWeight)
                : $volumetricWeight;
        } elseif ((float) ($this->weight_kg ?? 0) > 0) {
            $this->cbm = null;
            $this->chargeable_weight_kg = (float) $this->weight_kg;
        }
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function realizedQuantity(): float
    {
        $quantity = (float) $this->quantity;
        $deliveryItem = DeliveryItem::summarizeForOrderItems(
            $this->relationLoaded('deliveryItems') ? $this->deliveryItems : $this->deliveryItems()->get()
        )->get($this->id);

        if ($deliveryItem) {
            $quantity = (float) $deliveryItem['realized_quantity'];
        }

        return max($quantity, 0);
    }

    public function realizedSalesTotal(): float
    {
        return round($this->realizedQuantity() * (float) $this->unit_price, 2);
    }

    public function realizedCommissionTotal(): float
    {
        $salesTotal = $this->realizedSalesTotal();

        if ($salesTotal <= 0) {
            return 0.0;
        }

        if ($this->commission_rate !== null) {
            return round($salesTotal * ((float) $this->commission_rate / 100), 2);
        }

        $orderedSalesTotal = (float) $this->quantity * (float) $this->unit_price;
        if ($orderedSalesTotal <= 0 || $this->commission_amount === null) {
            return 0.0;
        }

        $effectiveRate = (float) $this->commission_amount / $orderedSalesTotal;

        return round($salesTotal * $effectiveRate, 2);
    }
}
