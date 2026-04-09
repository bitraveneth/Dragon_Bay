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
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

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
