<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'zone', 'day', 'vehicle_id', 'driver'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'route_id');
    }
}
