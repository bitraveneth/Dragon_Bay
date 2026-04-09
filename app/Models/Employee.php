<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo_path',
        'work_email',
        'work_phone',
        'work_mobile',
        'department',
        'job_position',
        'work_zone',
        'tags',
        'cv_path',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function contracts()
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function allowances()
    {
        return $this->hasMany(EmployeeAllowance::class);
    }

    public function equipment()
    {
        return $this->hasMany(EmployeeEquipment::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'employee_badges')
            ->withPivot(['granted_at', 'granted_by', 'note'])
            ->withTimestamps();
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function locationLogs()
    {
        return $this->hasMany(EmployeeLocationLog::class);
    }
}
