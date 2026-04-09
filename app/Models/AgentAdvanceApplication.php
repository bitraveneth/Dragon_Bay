<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAdvanceApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_advance_id',
        'invoice_id',
        'amount',
        'applied_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'applied_at' => 'date',
    ];

    public function advance()
    {
        return $this->belongsTo(AgentAdvance::class, 'agent_advance_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
