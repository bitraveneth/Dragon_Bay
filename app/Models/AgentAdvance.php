<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'amount',
        'applied_amount',
        'advanced_at',
        'payment_method',
        'reference',
        'notes',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'advanced_at' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function applications()
    {
        return $this->hasMany(AgentAdvanceApplication::class);
    }

    public function getAvailableAmountAttribute(): float
    {
        return max(0.0, (float) $this->amount - (float) $this->applied_amount);
    }
}
