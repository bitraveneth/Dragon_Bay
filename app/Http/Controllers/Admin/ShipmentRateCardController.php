<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Shipment;
use App\Models\ShipmentRateCard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShipmentRateCardController extends Controller
{
    public function index(Request $request)
    {
        $rateCards = ShipmentRateCard::with(['client', 'agent'])
            ->when($request->query('mode'), fn ($query, $mode) => $query->where('mode', $mode))
            ->when($request->query('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->query('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('mode')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.shipments.rate-cards', [
            'rateCards' => $rateCards,
            'clients' => Client::where('is_active', true)->orderBy('name')->get(),
            'agents' => Agent::where('is_active', true)->orderBy('name')->get(),
            'modes' => Shipment::MODES,
            'billingUnits' => ShipmentRateCard::BILLING_UNITS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        ShipmentRateCard::create($data);

        return redirect()->route('admin.shipment-rate-cards.index')->with('status', 'Shipment rate card created.');
    }

    public function destroy(ShipmentRateCard $rateCard)
    {
        if (Shipment::where('estimated_rate_card_id', $rateCard->id)->exists()) {
            $rateCard->update(['is_active' => false]);

            return redirect()
                ->route('admin.shipment-rate-cards.index')
                ->with('status', 'Rate card has shipment history, so it was deactivated instead of deleted.');
        }

        $rateCard->delete();

        return redirect()->route('admin.shipment-rate-cards.index')->with('status', 'Shipment rate card deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'agent_id' => 'nullable|exists:agents,id',
            'mode' => ['required', Rule::in(Shipment::MODES)],
            'origin_country' => 'nullable|string|max:80',
            'destination_country' => 'nullable|string|max:80',
            'billing_unit' => ['required', Rule::in(ShipmentRateCard::BILLING_UNITS)],
            'rate' => 'required|numeric|min:0',
            'minimum_charge' => 'nullable|numeric|min:0',
            'volumetric_divisor' => 'nullable|integer|min:1|max:100000',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $data['origin_country'] = $data['origin_country'] ? trim($data['origin_country']) : null;
        $data['destination_country'] = $data['destination_country'] ? trim($data['destination_country']) : null;
        $data['minimum_charge'] = $data['minimum_charge'] ?? 0;
        $data['volumetric_divisor'] = $data['volumetric_divisor'] ?? 5000;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
