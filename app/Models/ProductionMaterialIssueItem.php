<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMaterialIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_material_issue_id',
        'component_product_id',
        'batch_id',
        'quantity',
        'unit_cost',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'line_total' => 'decimal:2',
    ];

    public function issue()
    {
        return $this->belongsTo(ProductionMaterialIssue::class, 'production_material_issue_id');
    }

    public function component()
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
