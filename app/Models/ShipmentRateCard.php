<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ShipmentRateCard extends Model
{
    use HasFactory;

    public const BILLING_UNITS = ['chargeable_kg', 'cbm', 'shipment'];

    protected $fillable = [
        'client_id',
        'agent_id',
        'mode',
        'origin_country',
        'destination_country',
        'billing_unit',
        'rate',
        'minimum_charge',
        'volumetric_divisor',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'minimum_charge' => 'decimal:2',
        'volumetric_divisor' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public static function bestMatch(array $attributes, Carbon|string|null $date = null): ?self
    {
        $date = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
        $clientId = $attributes['client_id'] ?? null;
        $agentId = $attributes['agent_id'] ?? null;
        $origin = trim((string) ($attributes['origin_country'] ?? ''));
        $destination = trim((string) ($attributes['destination_country'] ?? ''));

        return self::query()
            ->where('is_active', true)
            ->where('mode', $attributes['mode'])
            ->when($clientId, fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNull('client_id')->orWhere('client_id', $clientId)),
                fn (Builder $query) => $query->whereNull('client_id'))
            ->when($agentId, fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNull('agent_id')->orWhere('agent_id', $agentId)),
                fn (Builder $query) => $query->whereNull('agent_id'))
            ->where(fn (Builder $query) => $query->whereNull('origin_country')->orWhere('origin_country', $origin))
            ->where(fn (Builder $query) => $query->whereNull('destination_country')->orWhere('destination_country', $destination))
            ->where(fn (Builder $query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))
            ->where(fn (Builder $query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date))
            ->orderByRaw(
                '(CASE WHEN client_id IS NOT NULL THEN 100 ELSE 0 END)
                + (CASE WHEN agent_id IS NOT NULL THEN 20 ELSE 0 END)
                + (CASE WHEN origin_country IS NOT NULL THEN 4 ELSE 0 END)
                + (CASE WHEN destination_country IS NOT NULL THEN 4 ELSE 0 END) DESC'
            )
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }
}
