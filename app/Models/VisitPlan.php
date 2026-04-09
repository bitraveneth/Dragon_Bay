<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'agent_id',
        'date',
        'status',
        'title',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}

