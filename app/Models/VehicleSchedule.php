<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'route_id',
        'scheduled_date',
        'driver',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }
}

