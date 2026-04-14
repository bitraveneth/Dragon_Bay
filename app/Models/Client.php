<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'currency',
        'credit_limit',
        'withholding_rate',
        'agent_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'withholding_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** The internal agent (salesperson) assigned to this client. */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, Order::class);
    }
}
