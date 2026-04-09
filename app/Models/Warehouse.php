<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'type'];

    public function entries()
    {
        return $this->hasMany(StockEntry::class);
    }

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function productionMaterialIssues()
    {
        return $this->hasMany(ProductionMaterialIssue::class);
    }
}
