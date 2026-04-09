<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'product_type',
        'description',
        'size',
        'uom',
        'volume_ml',
        'sku_code',
        'packaging_type_id',
        'tax_class_id',
        'mineral_source',
        'ph',
        'tds',
        'certifications',
        'barcode',
        'qr_code',
        'image_path',
        'base_price',
        'standard_cost',
        'supplier_name',
        'is_active',
    ];

    protected $casts = [
        'volume_ml' => 'float',
        'ph' => 'float',
        'tds' => 'integer',
        'base_price' => 'decimal:2',
        'standard_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function packagingType()
    {
        return $this->belongsTo(PackagingType::class);
    }

    public function scopeSellable(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $builder) {
                $builder->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true);
    }

    public function scopeMaterials(Builder $query): Builder
    {
        return $query
            ->whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->where('is_active', true);
    }

    public function scopeStockTracked(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereNull('product_type')
                ->orWhereIn('product_type', ['finished', 'raw', 'inhouse']);
        });
    }

    public function isStockTracked(): bool
    {
        return $this->product_type === null
            || in_array($this->product_type, ['finished', 'raw', 'inhouse'], true);
    }

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function agentPriceLists()
    {
        return $this->hasMany(AgentPriceList::class);
    }

    public function billsOfMaterial()
    {
        return $this->hasMany(BillOfMaterial::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function productionMaterialIssueItems()
    {
        return $this->hasMany(ProductionMaterialIssueItem::class, 'component_product_id');
    }
}
