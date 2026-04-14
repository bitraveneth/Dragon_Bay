<?php

namespace App\Services;

use App\Models\AgentCommissionSettlement;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CommissionSettlementService
{
    public function syncForInvoice(Invoice $invoice): void
    {
        $invoice->loadMissing(['order', 'shipment']);

        $agentId = $invoice->order?->agent_id ?? $invoice->shipment?->agent_id;
        if (! $agentId || ! $invoice->issued_at) {
            return;
        }

        $periodStart = $invoice->issued_at->copy()->startOfMonth()->toDateString();
        $periodEnd = $invoice->issued_at->copy()->endOfMonth()->toDateString();

        $settlement = AgentCommissionSettlement::query()
            ->where('agent_id', $agentId)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first();

        if ($settlement) {
            $this->refreshStatus($settlement);
        }
    }

    public function refreshStatus(AgentCommissionSettlement $settlement): void
    {
        if ((float) $settlement->commission_total <= 0) {
            $settlement->forceFill(['status' => AgentCommissionSettlement::STATUS_OPEN])->save();

            return;
        }

        if ($settlement->paid_at || $settlement->status === AgentCommissionSettlement::STATUS_PAID) {
            $settlement->forceFill(['status' => AgentCommissionSettlement::STATUS_PAID])->save();

            return;
        }

        $invoices = $this->relevantInvoicesQuery($settlement)->get();

        if ($invoices->isEmpty()) {
            // Commission calculated but no invoices issued yet
            $settlement->forceFill(['status' => AgentCommissionSettlement::STATUS_OPEN])->save();
            return;
        }

        $allPaid = $invoices->every(fn (Invoice $invoice) => (float) $invoice->outstanding <= 0.00001);

        if ($allPaid) {
            // All client invoices paid — commission is now payable to agent
            $settlement->forceFill([
                'status' => AgentCommissionSettlement::STATUS_APPROVED,
                'accrued_at' => $settlement->accrued_at ?? now(),
            ])->save();
        } else {
            // Invoices issued, commission expected but awaiting client payment
            $settlement->forceFill(['status' => AgentCommissionSettlement::STATUS_EXPECTED])->save();
        }
    }

    protected function relevantInvoicesQuery(AgentCommissionSettlement $settlement): Builder
    {
        $from = $settlement->period_start instanceof Carbon
            ? $settlement->period_start->copy()->startOfDay()
            : Carbon::parse($settlement->period_start)->startOfDay();
        $to = $settlement->period_end instanceof Carbon
            ? $settlement->period_end->copy()->endOfDay()
            : Carbon::parse($settlement->period_end)->endOfDay();

        return Invoice::query()
            ->with(['receipts', 'creditNotes', 'advanceApplications'])
            ->whereBetween('issued_at', [$from->toDateString(), $to->toDateString()])
            ->where(function (Builder $query) use ($settlement) {
                $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('agent_id', $settlement->agent_id));

                if (Schema::hasTable('shipments')) {
                    $query->orWhereHas('shipment', fn (Builder $shipmentQuery) => $shipmentQuery->where('agent_id', $settlement->agent_id));
                }
            })
            ->where(function (Builder $query) {
                $query->whereNull('invoice_type')
                    ->orWhere('invoice_type', '!=', 'proforma');
            });
    }
}
