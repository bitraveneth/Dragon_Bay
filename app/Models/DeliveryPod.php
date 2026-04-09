<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPod extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'signed_by',
        'signature_path',
        'delivered_at',
        'latitude',
        'longitude',
        'receiver_name',
        'receiver_phone',
        'notes',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
