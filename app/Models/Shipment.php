<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Shipment extends Model
{
    use HasFactory;

    public const MODES = ['courier', 'air', 'sea_lcl', 'sea_fcl', 'ddp'];

    public const STATUSES = [
        'draft',
        'confirmed',
        'received_at_origin',
        'in_transit',
        'customs_clearance',
        'out_for_delivery',
        'delivered',
        'final_invoiced',
        'closed',
    ];

    protected $fillable = [
        'order_id',
        'agent_id',
        'shipment_no',
        'mode',
        'status',
        'origin_country',
        'destination_country',
        'origin_warehouse_id',
        'destination_warehouse_id',
        'estimated_departure',
        'estimated_arrival',
        'estimated_unit_rate',
        'final_unit_rate',
        'estimated_price',
        'final_price',
        'pricing_locked',
        'pricing_locked_at',
        'pricing_locked_by',
        'notes',
    ];

    protected $casts = [
        'estimated_departure' => 'date',
        'estimated_arrival' => 'date',
        'estimated_unit_rate' => 'decimal:2',
        'final_unit_rate' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'pricing_locked' => 'boolean',
        'pricing_locked_at' => 'datetime',
    ];

    public static function nextShipmentNumber(): string
    {
        return 'SHP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function originWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function packages()
    {
        return $this->hasMany(ShipmentPackage::class);
    }

    public function legs()
    {
        return $this->hasMany(ShipmentLeg::class)->orderBy('sequence');
    }

    public function expenses()
    {
        return $this->hasMany(ShipmentExpense::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function pod()
    {
        return $this->hasOne(ShipmentPod::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'agent_id');
    }

    public function getTotalActualWeightKgAttribute(): float
    {
        return (float) $this->packages->sum('actual_weight_kg');
    }

    public function getTotalCbmAttribute(): float
    {
        return (float) $this->packages->sum('cbm');
    }

    public function getTotalChargeableWeightKgAttribute(): float
    {
        return (float) $this->packages->sum('chargeable_weight_kg');
    }

    public function getApprovedExpenseTotalAttribute(): float
    {
        return (float) $this->expenses
            ->where('status', 'approved')
            ->sum(fn (ShipmentExpense $expense) => $expense->effective_amount);
    }

    public function recalculatePricing(): void
    {
        $this->loadMissing('packages');

        $chargeable = $this->packages->sum('chargeable_weight_kg');
        $this->estimated_price = round($chargeable * (float) $this->estimated_unit_rate, 2);

        if ($this->final_unit_rate !== null) {
            $this->final_price = round($chargeable * (float) $this->final_unit_rate, 2);
        }

        $this->save();
    }

    public function lockPricing(?int $userId = null): void
    {
        $this->recalculatePricing();
        $this->forceFill([
            'pricing_locked' => true,
            'pricing_locked_at' => now(),
            'pricing_locked_by' => $userId,
        ])->save();
    }

    public function transitionTo(string $nextStatus): void
    {
        if (! in_array($nextStatus, self::STATUSES, true)) {
            throw new InvalidArgumentException("Unknown shipment status [{$nextStatus}].");
        }

        $currentIndex = array_search($this->status, self::STATUSES, true);
        $nextIndex = array_search($nextStatus, self::STATUSES, true);

        if ($nextIndex < $currentIndex) {
            throw new InvalidArgumentException('Shipment status cannot move backwards.');
        }

        $this->status = $nextStatus;
        $this->save();
    }
}
