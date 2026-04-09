<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMaterialIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_run_id',
        'warehouse_id',
        'issued_by',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function productionRun()
    {
        return $this->belongsTo(ProductionRun::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items()
    {
        return $this->hasMany(ProductionMaterialIssueItem::class);
    }
}
