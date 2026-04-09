<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\CreditNote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AgentLedgerController extends Controller
{
    public function show(Agent $agent, Request $request)
    {
        $from = $request->query('from') ? Carbon::parse($request->query('from')) : Carbon::now()->startOfMonth();
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : Carbon::now()->endOfMonth();

        $invoices = Invoice::with('order')
            ->whereHas('order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        $receipts = Receipt::with('invoice.order')
            ->whereHas('invoice.order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->whereBetween('received_at', [$from, $to])
            ->get();

        $credits = CreditNote::with('invoice.order')
            ->whereHas('invoice.order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        $advances = AgentAdvance::where('agent_id', $agent->id)
            ->whereBetween('advanced_at', [$from->toDateString(), $to->toDateString()])
            ->get();

        $rows = [];

        foreach ($invoices as $invoice) {
            $rows[] = [
                'date' => $invoice->issued_at,
                'type' => 'invoice',
                'ref' => $invoice->number,
                'debit' => ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding,
                'credit' => 0,
            ];
        }

        foreach ($receipts as $receipt) {
            $rows[] = [
                'date' => $receipt->received_at,
                'type' => 'receipt',
                'ref' => $receipt->invoice->number,
                'debit' => 0,
                'credit' => $receipt->amount,
            ];
        }

        foreach ($credits as $credit) {
            $rows[] = [
                'date' => $credit->issued_at,
                'type' => 'credit_note',
                'ref' => $credit->number,
                'debit' => 0,
                'credit' => $credit->amount,
            ];
        }

        foreach ($advances as $advance) {
            $rows[] = [
                'date' => $advance->advanced_at,
                'type' => 'advance',
                'ref' => $advance->reference ?: ('ADV-' . $advance->id),
                'debit' => 0,
                'credit' => $advance->amount,
            ];
        }

        usort($rows, static function ($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        $balance = 0;
        foreach ($rows as &$row) {
            $balance += $row['debit'] - $row['credit'];
            $row['balance'] = $balance;
        }
        unset($row);

        return view('admin.agents.ledger', compact('agent', 'rows', 'from', 'to', 'balance'));
    }
}
