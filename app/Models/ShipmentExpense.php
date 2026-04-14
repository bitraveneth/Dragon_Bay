<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'shipment_leg_id',
        'expense_type',
        'estimated_amount',
        'actual_amount',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function leg()
    {
        return $this->belongsTo(ShipmentLeg::class, 'shipment_leg_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getEffectiveAmountAttribute(): float
    {
        return (float) ($this->actual_amount ?? $this->estimated_amount);
    }

    public function approve(?int $userId = null): void
    {
        $this->forceFill([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ])->save();
    }
}
