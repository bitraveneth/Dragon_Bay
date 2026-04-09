<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_ENDED = 'ended';

    protected $fillable = [
        'employee_id',
        'reference',
        'start_date',
        'end_date',
        'working_schedule',
        'salary_amount',
        'travel_allowance',
        'dearness_allowance',
        'bonus',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'salary_amount' => 'decimal:2',
        'travel_allowance' => 'decimal:2',
        'dearness_allowance' => 'decimal:2',
        'bonus' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'expired' => self::STATUS_ENDED,
            'draft' => self::STATUS_ON_HOLD,
            self::STATUS_ACTIVE => self::STATUS_ACTIVE,
            self::STATUS_ENDED => self::STATUS_ENDED,
            default => self::STATUS_ON_HOLD,
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
