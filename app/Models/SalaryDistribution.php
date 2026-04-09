<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'base_salary',
        'bonus',
        'ta_allowances',
        'da_allowances',
        'commission',
        'payment_method',
        'document_path',
        'remarks',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'base_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'ta_allowances' => 'decimal:2',
        'da_allowances' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

