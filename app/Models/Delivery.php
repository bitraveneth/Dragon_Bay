<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'route_id',
        'vehicle_id',
        'status',
        'sequence',
        'pod_photo',
        'exception_notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function pod()
    {
        return $this->hasOne(DeliveryPod::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }
}
