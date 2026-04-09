<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationDispatchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dedupe_key',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
