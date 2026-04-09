<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAllowance extends Model
{
    use HasFactory;

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'reference',
        'amount',
        'description',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'pending' => self::STATUS_SUBMITTED,
            self::STATUS_APPROVED => self::STATUS_APPROVED,
            self::STATUS_PAID => self::STATUS_PAID,
            self::STATUS_REJECTED => self::STATUS_REJECTED,
            default => self::STATUS_SUBMITTED,
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
