<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';

    public const ACTUAL_COST_STATUSES = [
        self::STATUS_RUNNING,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'name',
        'platform',
        'start_date',
        'end_date',
        'reach',
        'impressions',
        'cost',
        'status',
        'campaign_code',
        'attachment_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reach' => 'integer',
        'impressions' => 'integer',
        'cost' => 'decimal:2',
    ];

    public static function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'active' => self::STATUS_RUNNING,
            'paused' => self::STATUS_RUNNING,
            'draft' => self::STATUS_PLANNED,
            self::STATUS_RUNNING => self::STATUS_RUNNING,
            self::STATUS_COMPLETED => self::STATUS_COMPLETED,
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
