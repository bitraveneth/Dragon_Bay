<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentLeg extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'sequence',
        'leg_type',
        'status',
        'from_location',
        'to_location',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function expenses()
    {
        return $this->hasMany(ShipmentExpense::class);
    }
}
