<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerGift extends Model
{
    use HasFactory;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_GIVEN = 'given';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'agent_id',
        'employee_id',
        'date',
        'occasion',
        'gift_type',
        'description',
        'amount',
        'campaign_code',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'pending' => self::STATUS_PLANNED,
            'delivered' => self::STATUS_GIVEN,
            self::STATUS_CANCELLED => self::STATUS_CANCELLED,
            self::STATUS_GIVEN => self::STATUS_GIVEN,
            default => self::STATUS_PLANNED,
        };
    }

    public function getStatusAttribute($value): string
    {
        return self::normalizeStatus($value);
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }
}
