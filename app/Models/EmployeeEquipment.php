<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEquipment extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'employee_id',
        'effective_date',
        'product_name',
        'device_identifier',
        'status',
        'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'assigned' => self::STATUS_ACTIVE,
            'maintenance' => self::STATUS_ACTIVE,
            'retired' => self::STATUS_RETURNED,
            self::STATUS_RETURNED => self::STATUS_RETURNED,
            self::STATUS_LOST => self::STATUS_LOST,
            default => self::STATUS_ACTIVE,
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
