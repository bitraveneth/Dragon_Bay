<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommissionSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'period_start',
        'period_end',
        'sales_total',
        'commission_total',
        'status',
        'accrued_at',
        'paid_at',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'sales_total' => 'decimal:2',
        'commission_total' => 'decimal:2',
        'accrued_at' => 'datetime',
        'paid_at' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
