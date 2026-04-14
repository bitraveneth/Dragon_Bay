<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::with('agent')
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->query('status'), function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        $agents = Agent::where('is_active', true)->orderBy('name')->get();
        return view('admin.clients.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'company_name'     => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:50',
            'address'          => 'nullable|string',
            'currency'         => 'nullable|string|max:10',
            'credit_limit'     => 'nullable|numeric|min:0',
            'withholding_rate' => 'nullable|numeric|min:0|max:100',
            'agent_id'         => 'nullable|exists:agents,id',
            'notes'            => 'nullable|string',
            'is_active'        => 'nullable|boolean',
        ]);

        $data['currency']  = $data['currency'] ?? 'BDT';
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        $client = Client::create($data);

        return redirect()->route('admin.clients.show', $client)
            ->with('status', 'Client created.');
    }

    public function show(Client $client)
    {
        $client->load('agent');

        $orders = Order::where('client_id', $client->id)
            ->latest()
            ->take(10)
            ->get();

        $shipments = Shipment::where('client_id', $client->id)
            ->latest()
            ->take(10)
            ->get();

        $invoices = Invoice::whereHas('order', fn ($q) => $q->where('client_id', $client->id))
            ->orWhereHas('shipment', fn ($q) => $q->where('client_id', $client->id))
            ->with('receipts', 'creditNotes')
            ->latest('issued_at')
            ->take(10)
            ->get();

        $outstanding = $invoices->sum(fn (Invoice $invoice) => (float) $invoice->outstanding);

        $portalUsers = User::where('client_id', $client->id)->get();

        return view('admin.clients.show', compact(
            'client', 'orders', 'shipments', 'invoices', 'outstanding', 'portalUsers'
        ));
    }

    public function edit(Client $client)
    {
        $agents = Agent::where('is_active', true)->orderBy('name')->get();
        return view('admin.clients.edit', compact('client', 'agents'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'company_name'     => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:50',
            'address'          => 'nullable|string',
            'currency'         => 'nullable|string|max:10',
            'credit_limit'     => 'nullable|numeric|min:0',
            'withholding_rate' => 'nullable|numeric|min:0|max:100',
            'agent_id'         => 'nullable|exists:agents,id',
            'notes'            => 'nullable|string',
            'is_active'        => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $client->update($data);

        return redirect()->route('admin.clients.show', $client)
            ->with('status', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        if (Order::where('client_id', $client->id)->exists()
            || Shipment::where('client_id', $client->id)->exists()) {
            return redirect()->route('admin.clients.index')
                ->withErrors(['client' => 'This client has orders or shipments and cannot be deleted. Deactivate instead.']);
        }

        // Unlink portal users
        User::where('client_id', $client->id)->update(['client_id' => null]);

        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('status', 'Client deleted.');
    }
}
