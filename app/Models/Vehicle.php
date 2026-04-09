<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'license_plate', 'driver', 'capacity_crates', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function routes()
    {
        return $this->hasMany(DeliveryRoute::class);
    }
}
