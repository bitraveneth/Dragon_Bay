<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_of_materials_id',
        'component_product_id',
        'quantity',
        'unit_cost',
        'unit',
    ];

    public function bom()
    {
        return $this->belongsTo(BillOfMaterial::class, 'bill_of_materials_id');
    }

    public function component()
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }
}
