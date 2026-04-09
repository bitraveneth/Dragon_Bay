<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_code',
        'production_date',
        'expiry_date',
        'qc_status',
        'notes',
    ];

    protected $dates = [
        'production_date',
        'expiry_date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productionRuns()
    {
        return $this->hasMany(ProductionRun::class);
    }

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function productionMaterialIssueItems()
    {
        return $this->hasMany(ProductionMaterialIssueItem::class);
    }

    /**
     * All stock movements related to this batch via its stock entries.
     */
    public function stockMovements()
    {
        return $this->hasManyThrough(
            StockMovement::class,
            StockEntry::class,
            'batch_id',        // Foreign key on stock_entries...
            'stock_entry_id',  // Foreign key on stock_movements...
            'id',              // Local key on batches...
            'id'               // Local key on stock_entries...
        );
    }
}
