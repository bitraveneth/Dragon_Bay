<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'agent_id',
        'order_type',
        'agent_reference',
        'delivery_date',
        'delivery_contact_name',
        'delivery_contact_phone',
        'delivery_address',
        'status',
        'total',
        'estimated_price',
        'commission_total',
        'notes',
        'is_credit_used',
        'payment_mode',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'commission_total' => 'decimal:2',
        'is_credit_used' => 'boolean',
    ];

    /** The cargo client who placed this order. */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /** The internal agent (salesperson) assigned to this order via the client. */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class)->latestOfMany();
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
