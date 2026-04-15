<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission as PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CustomerGift;
use App\Models\Employee;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerGiftController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $query = CustomerGift::with(['agent', 'employee'])
            ->whereBetween('date', [$from, $to]);

        if ($agentId = $request->query('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        $gifts = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $total = (clone $query)
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');

        $agents = Agent::orderBy('name')->get();

        return view('admin.crm.gifts.index', compact('gifts', 'from', 'to', 'total', 'agents'));
    }

    public function create()
    {
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        return view('admin.crm.gifts.create', [
            'gift' => new CustomerGift([
                'date' => Carbon::today(),
                'status' => CustomerGift::STATUS_PLANNED,
            ]),
            'agents' => $agents,
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($this->payloadPostsLedgerEntries($data)) {
            $this->ensureCanManageLedgerEntries();
        }

        DB::transaction(function () use ($data) {
            $gift = CustomerGift::create($data);
            $this->syncLedgerEntries($gift);
        });

        return redirect()->route('admin.gifts.index')->with('status', 'Customer gift recorded.');
    }

    public function edit(CustomerGift $gift)
    {
        $agents = Agent::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        return view('admin.crm.gifts.edit', compact('gift', 'agents', 'employees'));
    }

    public function update(Request $request, CustomerGift $gift)
    {
        if ($this->hasPostedLedgerEntries($gift)) {
            return redirect()
                ->route('admin.gifts.index')
                ->withErrors([
                    'gift' => 'Posted customer gifts cannot be edited. Preserve the ledger trail and record a correcting adjustment instead.',
                ]);
        }

        $data = $this->validated($request);

        if ($this->payloadPostsLedgerEntries($data)) {
            $this->ensureCanManageLedgerEntries();
        }

        DB::transaction(function () use ($gift, $data) {
            $gift->update($data);
            $this->syncLedgerEntries($gift);
        });

        return redirect()->route('admin.gifts.index')->with('status', 'Customer gift updated.');
    }

    public function destroy(CustomerGift $gift)
    {
        if ($this->hasPostedLedgerEntries($gift)) {
            return redirect()
                ->route('admin.gifts.index')
                ->withErrors([
                    'gift' => 'Posted customer gifts cannot be deleted. Preserve the ledger trail and record a correcting adjustment instead.',
                ]);
        }

        DB::transaction(function () use ($gift) {
            $this->deleteLedgerEntries($gift);
            $gift->delete();
        });

        return redirect()->route('admin.gifts.index')->with('status', 'Customer gift deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'employee_id' => 'nullable|exists:employees,id',
            'date' => 'required|date',
            'occasion' => 'nullable|string|max:100',
            'gift_type' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
            'campaign_code' => 'nullable|string|max:100',
            'status' => 'required|in:planned,given,cancelled',
        ]);
    }

    protected function syncLedgerEntries(CustomerGift $gift): void
    {
        $this->deleteLedgerEntries($gift);

        if ((float) $gift->amount <= 0 || $gift->status !== CustomerGift::STATUS_GIVEN) {
            return;
        }

        $description = $this->ledgerDescription($gift);

        LedgerEntry::create([
            'account' => 'Selling & Distribution Expense',
            'description' => $description,
            'debit' => $gift->amount,
            'credit' => 0,
        ]);

        LedgerEntry::create([
            'account' => 'Bank',
            'description' => $description,
            'debit' => 0,
            'credit' => $gift->amount,
        ]);
    }

    protected function deleteLedgerEntries(CustomerGift $gift): void
    {
        LedgerEntry::where('description', $this->ledgerDescription($gift))->delete();
    }

    protected function ledgerDescription(CustomerGift $gift): string
    {
        return 'Customer gift #' . $gift->id;
    }

    protected function payloadPostsLedgerEntries(array $data): bool
    {
        return (float) ($data['amount'] ?? 0) > 0
            && CustomerGift::normalizeStatus($data['status'] ?? null) === CustomerGift::STATUS_GIVEN;
    }

    protected function hasPostedLedgerEntries(CustomerGift $gift): bool
    {
        return LedgerEntry::where('description', $this->ledgerDescription($gift))->exists();
    }

    protected function ensureCanManageLedgerEntries(): void
    {
        if (! PermissionHelper::can(auth()->user(), 'accounting.manage')) {
            abort(403, 'Accounting permission is required to post or remove customer-gift ledger entries.');
        }
    }
}
