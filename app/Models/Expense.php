<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    public const STATUS_RECORDED = 'recorded';
    public const STATUS_REVIEWED = 'reviewed';

    protected $fillable = [
        'date',
        'category',
        'description',
        'amount',
        'reference',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            self::STATUS_REVIEWED,
            'paid',
            'overdue' => self::STATUS_REVIEWED,
            default => self::STATUS_RECORDED,
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
