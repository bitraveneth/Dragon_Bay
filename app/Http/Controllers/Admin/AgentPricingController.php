<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\AgentPriceList;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgentPricingController extends Controller
{
    public function edit(Agent $agent)
    {
        $products = Product::sellable()->orderBy('name')->get();
        $priceLists = $agent->priceLists()->get()->keyBy('product_id');
        $commissions = $agent->commissions()->orderBy('sku')->get();

        return view('admin.agents.pricing', compact('agent', 'products', 'priceLists', 'commissions'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'prices' => 'nullable|array',
            'prices.*' => 'nullable|numeric|min:0',
            'commissions' => 'nullable|array',
            'commissions.*.sku' => 'nullable|string',
            'commissions.*.type' => 'nullable|in:percentage,fixed',
            'commissions.*.value' => 'nullable|numeric|min:0',
            'commissions.*.order_type' => 'nullable|in:regular,bulk,sample,return',
            'commissions.*.frequency' => 'nullable|in:per_order,monthly',
            'commissions.*.threshold_min' => 'nullable|numeric|min:0',
            'commissions.*.threshold_max' => 'nullable|numeric|min:0',
        ]);

        $prices = $data['prices'] ?? [];
        $commissions = $data['commissions'] ?? [];
        $allowedProductIds = Product::sellable()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $allowedSkus = Product::whereNotNull('sku')->pluck('sku')->all();
        $errors = [];

        foreach (array_keys($prices) as $productId) {
            if (! in_array((string) $productId, $allowedProductIds, true)) {
                $errors["prices.$productId"] = 'Only active sellable products can be assigned agent pricing.';
            }
        }

        foreach ($commissions as $index => $row) {
            $sku = trim((string) ($row['sku'] ?? ''));

            if ($sku !== '' && ! in_array($sku, $allowedSkus, true)) {
                $errors["commissions.$index.sku"] = 'The selected SKU does not exist.';
            }

            $thresholdMin = $row['threshold_min'] ?? null;
            $thresholdMax = $row['threshold_max'] ?? null;

            if ($thresholdMin !== null && $thresholdMax !== null && (float) $thresholdMax < (float) $thresholdMin) {
                $errors["commissions.$index.threshold_max"] = 'Tier max must be greater than or equal to tier min.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($agent, $prices, $commissions) {
            AgentPriceList::where('agent_id', $agent->id)->delete();
            foreach ($prices as $productId => $price) {
                if ($price === null || $price === '' || (float) $price === 0.0) {
                    continue;
                }

                AgentPriceList::create([
                    'agent_id' => $agent->id,
                    'product_id' => $productId,
                    'price' => $price,
                    'notes' => null,
                ]);
            }

            AgentCommissionRule::where('agent_id', $agent->id)->delete();
            foreach ($commissions as $row) {
                $sku = trim((string) ($row['sku'] ?? ''));
                $value = $row['value'] ?? null;
                $type = $row['type'] ?? null;

                if (! $type || $value === null || $value === '' || (float) $value === 0.0) {
                    continue;
                }

                AgentCommissionRule::create([
                    'agent_id' => $agent->id,
                    'sku' => $sku !== '' ? $sku : null,
                    'type' => $type,
                    'value' => $value,
                    'order_type' => $row['order_type'] ?? null,
                    'frequency' => $row['frequency'] ?? 'per_order',
                    'threshold_min' => ($row['threshold_min'] ?? '') !== '' ? $row['threshold_min'] : null,
                    'threshold_max' => ($row['threshold_max'] ?? '') !== '' ? $row['threshold_max'] : null,
                ]);
            }
        });

        return redirect()
            ->route('admin.agents.pricing.edit', $agent)
            ->with('status', 'Pricing and commission rules updated.');
    }
}
