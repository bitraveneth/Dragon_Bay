<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'area',
        'zone',
        'location_code',
        'special_code',
        'latitude',
        'longitude',
        'credit_limit',
        'withholding_rate',
        'kyc_documents',
        'is_active',
        'parent_id',
        'bank_details',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'kyc_documents' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'withholding_rate' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function priceLists()
    {
        return $this->hasMany(AgentPriceList::class);
    }

    public function commissions()
    {
        return $this->hasMany(AgentCommissionRule::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function advances()
    {
        return $this->hasMany(AgentAdvance::class);
    }
}
