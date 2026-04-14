<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('LedgerEntry records are immutable. Create a correcting entry instead.');
        });

        static::deleting(function () {
            throw new \LogicException('LedgerEntry records cannot be deleted. Create a correcting entry instead.');
        });
    }

    protected $fillable = [
        'account',
        'description',
        'debit',
        'credit',
        'order_id',
        'invoice_id',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
