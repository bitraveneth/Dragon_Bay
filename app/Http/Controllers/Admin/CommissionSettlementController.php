<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentCommissionSettlement;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\CommissionCalculator;

class CommissionSettlementController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $settlements = AgentCommissionSettlement::with('agent')
            ->whereBetween('period_start', [$from, $to])
            ->orderBy('agent_id')
            ->get();

        return view('admin.agents.settlements', compact('settlements', 'month'));
    }

    public function generate(Request $request)
    {
        $month = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $calculator = app(CommissionCalculator::class);
        $agents = Agent::query()
            ->where(function ($query) use ($from, $to) {
                $query->whereHas('orders', function ($orderQuery) use ($from, $to) {
                    $orderQuery->where('status', 'delivered')
                        ->whereHas('invoice', function ($invoiceQuery) use ($from, $to) {
                            $invoiceQuery
                                ->whereDate('issued_at', '>=', $from->toDateString())
                                ->whereDate('issued_at', '<=', $to->toDateString());
                        });
                })->orWhereHas('commissions', function ($commissionQuery) {
                    $commissionQuery->where('frequency', 'monthly');
                });
            })
            ->orderBy('name')
            ->get();

        $processedAgentIds = [];

        foreach ($agents as $agent) {
            $summary = $calculator->buildMonthlySummaryForAgent($agent, $from, $to);
            if ($summary['sales'] <= 0 && $summary['commission'] <= 0) {
                continue;
            }

            $settlement = AgentCommissionSettlement::firstOrNew([
                'agent_id' => $agent->id,
                'period_start' => $from,
                'period_end' => $to,
            ]);

            if ($settlement->exists && $settlement->status !== 'open') {
                $processedAgentIds[] = $agent->id;
                continue;
            }

            $settlement->fill([
                'sales_total' => $summary['sales'],
                'commission_total' => $summary['commission'],
                'status' => $settlement->status ?: 'open',
            ]);
            $settlement->save();

            $processedAgentIds[] = $agent->id;
        }

        AgentCommissionSettlement::whereBetween('period_start', [$from, $to])
            ->where('status', 'open')
            ->whereNotIn('agent_id', $processedAgentIds)
            ->update([
                'sales_total' => 0,
                'commission_total' => 0,
            ]);

        return redirect()->route('admin.settlements.index')->with('status', 'Monthly settlements generated.');
    }

    public function updateStatus(Request $request, AgentCommissionSettlement $settlement)
    {
        $data = $request->validate([
            'status' => 'required|in:open,approved,paid',
            'payment_method' => 'nullable|in:cash,bkash,bank_transfer,cheque',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $workflow = ['open' => 0, 'approved' => 1, 'paid' => 2];
        $currentState = $workflow[$settlement->status] ?? 0;
        $targetState = $workflow[$data['status']] ?? 0;

        if ($targetState < $currentState || $targetState > $currentState + 1) {
            return redirect()->route('admin.settlements.index')->withErrors([
                'status' => 'Settlement status must move forward one step at a time.',
            ]);
        }

        DB::transaction(function () use ($settlement, $data) {
            if (in_array($data['status'], ['approved', 'paid'], true)) {
                $this->ensureAccrued($settlement);
            }

            if ($data['status'] === 'paid') {
                $this->ensurePaid(
                    $settlement,
                    $data['payment_method'] ?? 'bank_transfer',
                    $data['payment_reference'] ?? null
                );
            }

            $settlement->update([
                'status' => $data['status'],
                'payment_method' => $data['status'] === 'paid'
                    ? ($data['payment_method'] ?? $settlement->payment_method ?? 'bank_transfer')
                    : $settlement->payment_method,
                'payment_reference' => $data['status'] === 'paid'
                    ? ($data['payment_reference'] ?? $settlement->payment_reference)
                    : $settlement->payment_reference,
            ]);
        });

        return redirect()->route('admin.settlements.index')->with('status', 'Settlement updated.');
    }

    protected function ensureAccrued(AgentCommissionSettlement $settlement): void
    {
        if ($settlement->accrued_at || (float) $settlement->commission_total <= 0) {
            return;
        }

        $description = $this->settlementDescription($settlement);

        LedgerEntry::create([
            'account' => 'Commission Expense',
            'description' => $description,
            'debit' => $settlement->commission_total,
            'credit' => 0,
            'order_id' => null,
            'invoice_id' => null,
        ]);

        LedgerEntry::create([
            'account' => 'Commission Payable',
            'description' => $description,
            'debit' => 0,
            'credit' => $settlement->commission_total,
            'order_id' => null,
            'invoice_id' => null,
        ]);

        $settlement->forceFill([
            'accrued_at' => now(),
        ])->save();
    }

    protected function ensurePaid(AgentCommissionSettlement $settlement, string $paymentMethod, ?string $paymentReference): void
    {
        if ($settlement->paid_at || (float) $settlement->commission_total <= 0) {
            return;
        }

        $description = $this->settlementDescription($settlement) . ' payout';

        LedgerEntry::create([
            'account' => 'Commission Payable',
            'description' => $description,
            'debit' => $settlement->commission_total,
            'credit' => 0,
            'order_id' => null,
            'invoice_id' => null,
        ]);

        LedgerEntry::create([
            'account' => 'Bank',
            'description' => $description,
            'debit' => 0,
            'credit' => $settlement->commission_total,
            'order_id' => null,
            'invoice_id' => null,
        ]);

        $settlement->forceFill([
            'paid_at' => now()->toDateString(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ])->save();
    }

    protected function settlementDescription(AgentCommissionSettlement $settlement): string
    {
        $settlement->loadMissing('agent');

        return 'Commission settlement #' . $settlement->id
            . ' for ' . ($settlement->agent?->name ?? 'Agent')
            . ' (' . $settlement->period_start->format('Y-m') . ')';
    }
}
