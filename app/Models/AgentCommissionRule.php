<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'sku',
        'type',
        'value',
        'order_type',
        'frequency',
        'threshold_min',
        'threshold_max',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'threshold_min' => 'decimal:2',
        'threshold_max' => 'decimal:2',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
