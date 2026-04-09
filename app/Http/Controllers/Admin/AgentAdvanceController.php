<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AgentAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $advances = AgentAdvance::with('agent')
            ->when($request->query('agent_id'), function ($query, $agentId) {
                $query->where('agent_id', $agentId);
            })
            ->orderByDesc('advanced_at')
            ->paginate(15)
            ->withQueryString();

        $agents = Agent::orderBy('name')->get();

        return view('admin.agent-advances.index', compact('advances', 'agents'));
    }

    public function create(Request $request)
    {
        $agents = Agent::orderBy('name')->get();
        $selectedAgent = $request->query('agent_id');

        return view('admin.agent-advances.create', compact('agents', 'selectedAgent'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'amount' => 'required|numeric|min:0.01',
            'advanced_at' => 'required|date',
            'payment_method' => 'nullable|in:cash,bkash,bank_transfer,cheque',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $advance = AgentAdvance::create([
                'agent_id' => $data['agent_id'],
                'amount' => $data['amount'],
                'applied_amount' => 0,
                'advanced_at' => $data['advanced_at'],
                'payment_method' => $data['payment_method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'open',
            ]);

            $agent = Agent::findOrFail($data['agent_id']);
            $description = $this->advanceLedgerDescription($advance, $agent);
            $ledgerTimestamp = Carbon::parse($advance->advanced_at)->startOfDay();

            $bankEntry = new LedgerEntry([
                'account' => 'Bank',
                'description' => $description,
                'debit' => $advance->amount,
                'credit' => 0,
                'order_id' => null,
                'invoice_id' => null,
            ]);
            $bankEntry->created_at = $ledgerTimestamp;
            $bankEntry->updated_at = $ledgerTimestamp;
            $bankEntry->save();

            $advanceEntry = new LedgerEntry([
                'account' => 'Agent Advances',
                'description' => $description,
                'debit' => 0,
                'credit' => $advance->amount,
                'order_id' => null,
                'invoice_id' => null,
            ]);
            $advanceEntry->created_at = $ledgerTimestamp;
            $advanceEntry->updated_at = $ledgerTimestamp;
            $advanceEntry->save();
        });

        return redirect()->route('admin.agent-advances.index')->with('status', 'Agent advance recorded.');
    }

    protected function advanceLedgerDescription(AgentAdvance $advance, Agent $agent): string
    {
        $date = Carbon::parse($advance->advanced_at)->toDateString();

        return 'Agent advance #' . $advance->id . ' from ' . $agent->name . ' on ' . $date;
    }
}
