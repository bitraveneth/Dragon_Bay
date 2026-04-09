<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\PackagingConversion;
use App\Models\PackagingType;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VehicleLoadController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $deliveries = Delivery::with('order.items.product.packagingType', 'vehicle')
            ->where(function ($query) use ($date) {
                $query->whereHas('order', function ($orderQuery) use ($date) {
                    $orderQuery->whereDate('delivery_date', $date);
                })->orWhere(function ($legacyQuery) use ($date) {
                    $legacyQuery->whereDate('created_at', $date)
                        ->whereHas('order', function ($orderQuery) {
                            $orderQuery->whereNull('delivery_date');
                        });
                });
            })
            ->get();

        $loadPackagingTypeIds = PackagingType::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%crate%'])
                    ->orWhereRaw('LOWER(COALESCE(unit, \'\')) LIKE ?', ['%crate%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%carton%'])
                    ->orWhereRaw('LOWER(COALESCE(unit, \'\')) LIKE ?', ['%carton%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%case%'])
                    ->orWhereRaw('LOWER(COALESCE(unit, \'\')) LIKE ?', ['%case%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%jar%'])
                    ->orWhereRaw('LOWER(COALESCE(unit, \'\')) LIKE ?', ['%jar%']);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $conversionRules = empty($loadPackagingTypeIds)
            ? collect()
            : PackagingConversion::query()
                ->where(function ($query) use ($loadPackagingTypeIds) {
                    $query->whereIn('from_packaging_type_id', $loadPackagingTypeIds)
                        ->orWhereIn('to_packaging_type_id', $loadPackagingTypeIds);
                })
                ->get();

        $byVehicle = [];

        foreach ($deliveries as $delivery) {
            if (!$delivery->vehicle) {
                continue;
            }
            $vid = $delivery->vehicle->id;
            if (!isset($byVehicle[$vid])) {
                $byVehicle[$vid] = [
                    'vehicle' => $delivery->vehicle,
                    'crateLoad' => 0,
                    'deliveries' => [],
                ];
            }

            $crateEstimate = 0;
            foreach ($delivery->order->items as $item) {
                $crateEstimate += $this->estimateLoadUnits($item, $loadPackagingTypeIds, $conversionRules);
            }

            $byVehicle[$vid]['crateLoad'] += $crateEstimate;
            $byVehicle[$vid]['deliveries'][] = [
                'delivery' => $delivery,
                'crates' => $crateEstimate,
            ];
        }

        return view('admin.deliveries.load', [
            'date' => $date,
            'rows' => $byVehicle,
        ]);
    }

    protected function estimateLoadUnits($item, array $loadPackagingTypeIds, Collection $conversionRules): int
    {
        $quantity = max((float) ($item->quantity ?? 0), 0);

        if ($quantity <= 0) {
            return 0;
        }

        $packagingTypeId = (int) ($item->product?->packaging_type_id ?? 0);

        if ($packagingTypeId > 0 && in_array($packagingTypeId, $loadPackagingTypeIds, true)) {
            return (int) ceil($quantity);
        }

        $conversion = $conversionRules->first(function (PackagingConversion $rule) use ($packagingTypeId, $loadPackagingTypeIds) {
            return ((int) $rule->from_packaging_type_id === $packagingTypeId && in_array((int) $rule->to_packaging_type_id, $loadPackagingTypeIds, true))
                || ((int) $rule->to_packaging_type_id === $packagingTypeId && in_array((int) $rule->from_packaging_type_id, $loadPackagingTypeIds, true));
        });

        if ($conversion && (float) $conversion->factor > 0) {
            return (int) ceil($quantity / (float) $conversion->factor);
        }

        return (int) ceil($quantity);
    }
}
