<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'badge_id',
        'granted_at',
        'granted_by',
        'note',
    ];

    protected $casts = [
        'granted_at' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
