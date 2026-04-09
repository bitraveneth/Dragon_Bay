<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLocationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'event_type',
        'logged_at',
        'latitude',
        'longitude',
        'location_label',
        'source',
        'notes',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
