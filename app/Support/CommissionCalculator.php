<?php

namespace App\Support;

use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CommissionCalculator
{
    public function calculatePerOrderCommission(Collection $rules, ?string $sku, string $orderType, float $lineTotal): float
    {
        $matchingRules = $rules
            ->where('frequency', 'per_order')
            ->filter(fn (AgentCommissionRule $rule) => $this->ruleMatchesItem($rule, $sku, $orderType, $lineTotal));

        if ($matchingRules->isEmpty()) {
            return 0.0;
        }

        $selectedRules = $this->selectTieredRules($matchingRules);
        $commission = 0.0;

        foreach ($selectedRules as $rule) {
            $commission += $this->ruleCommissionAmount($rule, $lineTotal);
        }

        return round($commission, 2);
    }

    public function buildMonthlySummaryForAgent(Agent $agent, Carbon $from, Carbon $to): array
    {
        $items = OrderItem::with(['order.invoice', 'order.delivery.items', 'product'])
            ->whereHas('order', function ($query) use ($agent, $from, $to) {
                $query->where('agent_id', $agent->id)
                    ->where('status', 'delivered')
                    ->whereHas('invoice', function ($invoiceQuery) use ($from, $to) {
                        $invoiceQuery
                            ->whereDate('issued_at', '>=', $from->toDateString())
                            ->whereDate('issued_at', '<=', $to->toDateString());
                    });
            })
            ->get();

        $salesTotal = 0.0;
        $perOrderCommission = 0.0;

        foreach ($items as $item) {
            $salesTotal += $item->realizedSalesTotal();
            $perOrderCommission += $item->realizedCommissionTotal();
        }

        $monthlyCommission = $this->calculateMonthlyRuleCommission(
            $agent->commissions()->where('frequency', 'monthly')->get(),
            $items
        );

        return [
            'sales' => round($salesTotal, 2),
            'per_order_commission' => round($perOrderCommission, 2),
            'monthly_commission' => round($monthlyCommission, 2),
            'commission' => round($perOrderCommission + $monthlyCommission, 2),
        ];
    }

    protected function calculateMonthlyRuleCommission(Collection $rules, Collection $items): float
    {
        if ($rules->isEmpty() || $items->isEmpty()) {
            return 0.0;
        }

        $commission = 0.0;

        foreach ($rules->groupBy(fn (AgentCommissionRule $rule) => $this->ruleGroupKey($rule)) as $group) {
            $referenceRule = $group->first();
            $eligibleSales = $items->sum(function (OrderItem $item) use ($referenceRule) {
                return $this->ruleMatchesMonthlyItem($referenceRule, $item)
                    ? $item->realizedSalesTotal()
                    : 0.0;
            });

            if ($eligibleSales <= 0) {
                continue;
            }

            $matchedRule = $group
                ->filter(fn (AgentCommissionRule $rule) => $this->thresholdMatches($rule, $eligibleSales))
                ->sortByDesc(fn (AgentCommissionRule $rule) => (float) ($rule->threshold_min ?? 0))
                ->first();

            if (! $matchedRule) {
                continue;
            }

            $commission += $this->ruleCommissionAmount($matchedRule, $eligibleSales);
        }

        return round($commission, 2);
    }

    protected function ruleMatchesItem(AgentCommissionRule $rule, ?string $sku, string $orderType, float $amount): bool
    {
        if ($rule->sku && $rule->sku !== $sku) {
            return false;
        }

        if ($rule->order_type && $rule->order_type !== $orderType) {
            return false;
        }

        return $this->thresholdMatches($rule, $amount);
    }

    protected function ruleMatchesMonthlyItem(AgentCommissionRule $rule, OrderItem $item): bool
    {
        $itemSku = $item->product?->sku;

        if ($rule->sku && $rule->sku !== $itemSku) {
            return false;
        }

        if ($rule->order_type && $rule->order_type !== $item->order_type) {
            return false;
        }

        return true;
    }

    protected function thresholdMatches(AgentCommissionRule $rule, float $basisAmount): bool
    {
        if ($rule->threshold_min !== null && $basisAmount < (float) $rule->threshold_min) {
            return false;
        }

        if ($rule->threshold_max !== null && $basisAmount > (float) $rule->threshold_max) {
            return false;
        }

        return true;
    }

    protected function selectTieredRules(Collection $rules): Collection
    {
        return $rules
            ->groupBy(fn (AgentCommissionRule $rule) => $this->ruleGroupKey($rule))
            ->map(function (Collection $group) {
                return $group
                    ->sortByDesc(fn (AgentCommissionRule $rule) => (float) ($rule->threshold_min ?? 0))
                    ->first();
            })
            ->values();
    }

    protected function ruleGroupKey(AgentCommissionRule $rule): string
    {
        return implode('|', [
            $rule->frequency ?? 'per_order',
            $rule->sku ?? '*',
            $rule->order_type ?? '*',
            $rule->type ?? 'percentage',
        ]);
    }

    protected function ruleCommissionAmount(AgentCommissionRule $rule, float $basisAmount): float
    {
        if ($rule->type === 'fixed') {
            return round((float) $rule->value, 2);
        }

        return round($basisAmount * ((float) $rule->value / 100), 2);
    }
}
