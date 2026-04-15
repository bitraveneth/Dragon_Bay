<?php

namespace App\Services;

use App\Models\AgentAdvance;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ClientCreditService
{
    public function snapshot(Client $client, ?Order $excludingOrder = null): array
    {
        $creditLimit = (float) ($client->credit_limit ?? 0);

        $invoiceOutstanding = Invoice::query()
            ->where(function (Builder $query) use ($client) {
                $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('client_id', $client->id));

                if (Schema::hasTable('shipments')) {
                    $query->orWhereHas('shipment', fn (Builder $shipmentQuery) => $shipmentQuery->where('client_id', $client->id));
                }
            })
            ->get()
            ->sum(fn (Invoice $invoice) => (float) $invoice->outstanding);

        $pendingCreditOrders = Order::query()
            ->where('client_id', $client->id)
            ->where('is_credit_used', true)
            ->when($excludingOrder?->id, fn (Builder $query) => $query->whereKeyNot($excludingOrder->id))
            ->whereDoesntHave('invoice')
            ->sum('total');

        $availableAdvances = 0.0;
        if ($client->agent_id && Schema::hasTable('agent_advances')) {
            $availableAdvances = AgentAdvance::query()
                ->where('agent_id', $client->agent_id)
                ->whereIn('status', ['open', 'partial'])
                ->get()
                ->sum(fn (AgentAdvance $advance) => (float) $advance->available_amount);
        }

        $currentExposure = max((float) $invoiceOutstanding + (float) $pendingCreditOrders - (float) $availableAdvances, 0.0);

        return [
            'credit_limit' => $creditLimit,
            'invoice_outstanding' => round((float) $invoiceOutstanding, 2),
            'pending_credit_orders' => round((float) $pendingCreditOrders, 2),
            'available_advances' => round((float) $availableAdvances, 2),
            'current_exposure' => round($currentExposure, 2),
            'available_credit' => round(max($creditLimit - $currentExposure, 0.0), 2),
        ];
    }

    public function ensureWithinLimit(Client $client, float $proposedAmount, ?Order $excludingOrder = null): void
    {
        $snapshot = $this->snapshot($client, $excludingOrder);
        $creditLimit = (float) $snapshot['credit_limit'];

        if ($creditLimit <= 0) {
            return;
        }

        $requestedAmount = round(max($proposedAmount, 0.0), 2);
        $projectedExposure = round((float) $snapshot['current_exposure'] + $requestedAmount, 2);

        if ($projectedExposure <= $creditLimit + 0.00001) {
            return;
        }

        $currencyCode = strtoupper((string) ($client->currency ?: 'BDT'));

        throw ValidationException::withMessages([
            'client_id' => [sprintf(
                'Credit limit exceeded. Limit: %s %s, current exposure: %s %s, requested order: %s %s.',
                $currencyCode,
                number_format($creditLimit, 2),
                $currencyCode,
                number_format((float) $snapshot['current_exposure'], 2),
                $currencyCode,
                number_format($requestedAmount, 2)
            )],
        ]);
    }
}
