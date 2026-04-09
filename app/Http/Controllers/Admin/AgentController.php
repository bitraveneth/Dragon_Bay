<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\AgentCommissionSettlement;
use App\Models\AgentPriceList;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with(['parent', 'commissions'])->paginate(12);
        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        $parents = Agent::orderBy('name')->get();
        return view('admin.agents.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'area' => 'nullable|string',
            'zone' => 'nullable|string',
            'location_code' => 'nullable|string|max:100',
            'special_code' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric|min:0',
            'withholding_rate' => 'nullable|numeric|min:0|max:100',
            'kyc_documents' => 'nullable|array',
            'kyc_files' => 'nullable|array',
            'kyc_files.*' => 'file|max:4096',
            'parent_id' => 'nullable|exists:agents,id',
            'bank_details' => 'nullable|string',
        ]);

        $kycDocuments = [];

        // Text-based KYC docs (comma separated string in first element)
        if (!empty($data['kyc_documents'])) {
            $raw = $data['kyc_documents'][0] ?? null;
            if ($raw) {
                foreach (explode(',', $raw) as $piece) {
                    $trimmed = trim($piece);
                    if ($trimmed !== '') {
                        $kycDocuments[] = $trimmed;
                    }
                }
            }
        }

        unset($data['kyc_documents']);

        // Uploaded KYC files
        if ($request->hasFile('kyc_files')) {
            foreach ($request->file('kyc_files') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('agents/kyc', 'public');
                    $kycDocuments[] = $path;
                }
            }
        }

        if (!empty($kycDocuments)) {
            $data['kyc_documents'] = array_values($kycDocuments);
        }

        Agent::create($data);
        return redirect()->route('admin.agents.index')->with('status', 'Agent saved.');
    }

    public function edit(Agent $agent)
    {
        $parents = Agent::where('id', '!=', $agent->id)->orderBy('name')->get();
        return view('admin.agents.edit', compact('agent', 'parents'));
    }

    public function show(Agent $agent)
    {
        $agent->load(['parent', 'orders']);

        return view('admin.agents.show', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'area' => 'nullable|string',
            'zone' => 'nullable|string',
            'location_code' => 'nullable|string|max:100',
            'special_code' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric|min:0',
            'withholding_rate' => 'nullable|numeric|min:0|max:100',
            'kyc_documents' => 'nullable|array',
            'kyc_files' => 'nullable|array',
            'kyc_files.*' => 'file|max:4096',
            'parent_id' => 'nullable|exists:agents,id',
            'bank_details' => 'nullable|string',
        ]);

        // Start from existing documents
        $kycDocuments = is_array($agent->kyc_documents) ? $agent->kyc_documents : [];

        // Append text-based docs from input
        if (!empty($data['kyc_documents'])) {
            $raw = $data['kyc_documents'][0] ?? null;
            if ($raw) {
                foreach (explode(',', $raw) as $piece) {
                    $trimmed = trim($piece);
                    if ($trimmed !== '') {
                        $kycDocuments[] = $trimmed;
                    }
                }
            }
        }

        unset($data['kyc_documents']);

        // Append new uploaded files
        if ($request->hasFile('kyc_files')) {
            foreach ($request->file('kyc_files') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('agents/kyc', 'public');
                    $kycDocuments[] = $path;
                }
            }
        }

        if (!empty($kycDocuments)) {
            $data['kyc_documents'] = array_values($kycDocuments);
        }

        $agent->update($data);

        return redirect()->route('admin.agents.index')->with('status', 'Agent updated.');
    }

    public function destroy(Agent $agent)
    {
        $blockingHistory = $this->deletionBlockingHistory($agent->id);

        if ($blockingHistory !== []) {
            return redirect()
                ->route('admin.agents.index')
                ->withErrors([
                    'agent' => 'This agent has historical records (' . implode(', ', $blockingHistory) . ') and cannot be deleted. Disable or reassign it instead.',
                ]);
        }

        if (Order::where('agent_id', $agent->id)->exists()) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'Agent has orders and cannot be deleted. Consider disabling or reassigning instead.');
        }

        // Unlink any user accounts pointing at this agent so the record can be removed safely.
        User::where('agent_id', $agent->id)->update(['agent_id' => null]);

        AgentPriceList::where('agent_id', $agent->id)->delete();
        AgentCommissionRule::where('agent_id', $agent->id)->delete();
        AgentCommissionSettlement::where('agent_id', $agent->id)->delete();

        $agent->delete();

        return redirect()->route('admin.agents.index')->with('status', 'Agent deleted and any linked user accounts were unassigned.');
    }

    protected function deletionBlockingHistory(int $agentId): array
    {
        $checks = [
            'customer_gifts' => 'customer gifts',
            'visit_plans' => 'visit plans',
        ];

        $found = [];

        foreach ($checks as $table => $label) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (DB::table($table)->where('agent_id', $agentId)->exists()) {
                $found[] = $label;
            }
        }

        return $found;
    }
}
