<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentPackage extends Model
{
    use HasFactory;

    public const VOLUMETRIC_DIVISOR = 5000;

    protected $fillable = [
        'shipment_id',
        'description',
        'pieces',
        'actual_weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'cbm',
        'volumetric_weight_kg',
        'chargeable_weight_kg',
    ];

    protected $casts = [
        'pieces' => 'integer',
        'actual_weight_kg' => 'decimal:3',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'cbm' => 'decimal:4',
        'volumetric_weight_kg' => 'decimal:3',
        'chargeable_weight_kg' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShipmentPackage $package) {
            $package->recalculateMeasurements();
        });

        static::saved(function (ShipmentPackage $package) {
            $package->shipment?->recalculatePricing();
        });

        static::deleted(function (ShipmentPackage $package) {
            $package->shipment?->recalculatePricing();
        });
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function recalculateMeasurements(): void
    {
        $pieces = max((int) $this->pieces, 1);
        $length = max((float) $this->length_cm, 0.0);
        $width = max((float) $this->width_cm, 0.0);
        $height = max((float) $this->height_cm, 0.0);
        $actualWeight = max((float) $this->actual_weight_kg, 0.0);

        $volumeCm = $length * $width * $height * $pieces;
        $this->cbm = round($volumeCm / 1000000, 4);
        $this->volumetric_weight_kg = round($volumeCm / self::VOLUMETRIC_DIVISOR, 3);
        $this->chargeable_weight_kg = round(max($actualWeight, (float) $this->volumetric_weight_kg), 3);
    }
}
