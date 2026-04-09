<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'product_id',
        'batch_id',
        'warehouse_id',
        'line',
        'shift',
        'quantity',
        'status',
        'supervisor_id',
        'qc_status',
        'approved_by',
        'approved_at',
        'material_unit_cost',
        'material_total_cost',
        'stock_confirmed_at',
        'stock_confirmed_by',
        'notes',
        'materials_reserved',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'approved_at' => 'datetime',
        'stock_confirmed_at' => 'datetime',
        'material_unit_cost' => 'decimal:4',
        'material_total_cost' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function stockConfirmer()
    {
        return $this->belongsTo(User::class, 'stock_confirmed_by');
    }

    public function materialIssues()
    {
        return $this->hasMany(ProductionMaterialIssue::class);
    }
}
