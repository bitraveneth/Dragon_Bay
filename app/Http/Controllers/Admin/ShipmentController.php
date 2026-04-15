<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentExpense;
use App\Models\ShipmentLeg;
use App\Models\ShipmentPackage;
use App\Models\ShipmentPod;
use App\Models\ShipmentRateCard;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\SystemAlertNotification;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::with(['client', 'agent', 'order', 'packages', 'expenses'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('mode'), fn ($query, $mode) => $query->where('mode', $mode))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.shipments.index', [
            'shipments' => $shipments,
            'statuses' => Shipment::STATUSES,
            'modes' => Shipment::MODES,
        ]);
    }

    public function create()
    {
        return view('admin.shipments.create', [
            'shipment' => new Shipment([
                'shipment_no' => Shipment::nextShipmentNumber(),
                'origin_country' => 'China',
                'destination_country' => 'Bangladesh',
            ]),
            'clients' => \App\Models\Client::where('is_active', true)->orderBy('name')->get(),
            'agents' => Agent::where('is_active', true)->orderBy('name')->get(),
            'orders' => Order::with('client')->latest()->take(100)->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'modes' => Shipment::MODES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedShipment($request);
        $data['shipment_no'] = ($data['shipment_no'] ?? null) ?: Shipment::nextShipmentNumber();
        $data['status'] = 'draft';
        $data = $this->applyEstimatedPricing($data);

        $shipment = DB::transaction(function () use ($data) {
            $shipment = Shipment::create($data);
            $this->seedDefaultLegs($shipment);
            AuditLog::record('shipment.created', $shipment, [], $shipment->toArray());

            return $shipment;
        });

        return redirect()
            ->route('admin.shipments.show', $shipment)
            ->with('status', 'Shipment created.');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load([
            'client',
            'agent',
            'estimatedRateCard',
            'order',
            'originWarehouse',
            'destinationWarehouse',
            'packages',
            'legs.expenses',
            'expenses.leg',
            'invoices',
            'pod',
        ]);

        return view('admin.shipments.show', [
            'shipment' => $shipment,
            'statuses' => Shipment::STATUSES,
            'expenseTypes' => ['air_freight', 'sea_freight', 'customs_duty', 'port_warehouse', 'last_mile', 'other'],
        ]);
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Shipment::STATUSES)],
        ]);

        $old = $shipment->only(['status']);

        try {
            $shipment->transitionTo($data['status']);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        AuditLog::record('shipment.status_changed', $shipment, $old, $shipment->only(['status']));
        $this->notifyRoles(
            ['super_admin', 'admin', 'sales_officer', 'delivery_coordinator', 'warehouse_officer'],
            [
                'title' => 'Shipment status updated',
                'message' => $shipment->shipment_no . ' is now ' . ucwords(str_replace('_', ' ', $shipment->status)) . '.',
                'variant' => 'info',
                'source' => 'Logistics',
                'link' => route('admin.shipments.show', $shipment),
                'context' => [
                    'shipment_id' => $shipment->id,
                    'shipment_no' => $shipment->shipment_no,
                    'status' => $shipment->status,
                ],
            ]
        );

        return back()->with('status', 'Shipment status updated.');
    }

    public function storePackage(Request $request, Shipment $shipment)
    {
        if ($shipment->pricing_locked) {
            return back()->withErrors(['package' => 'Pricing is locked. Unlocking requires a correcting shipment, not editing measurements.']);
        }

        $data = $request->validate([
            'description' => 'nullable|string|max:255',
            'pieces' => 'required|integer|min:1',
            'actual_weight_kg' => 'required|numeric|min:0',
            'length_cm' => 'required|numeric|min:0',
            'width_cm' => 'required|numeric|min:0',
            'height_cm' => 'required|numeric|min:0',
        ]);

        $package = $shipment->packages()->create($data);
        AuditLog::record('shipment.package_added', $package, [], $package->toArray());

        return back()->with('status', 'Package added and pricing recalculated.');
    }

    public function storeLeg(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'sequence' => 'required|integer|min:1',
            'leg_type' => 'required|in:origin,international,customs,last_mile',
            'status' => 'required|in:pending,received,in_transit,customs,out_for_delivery,delivered,exception',
            'from_location' => 'nullable|string|max:255',
            'to_location' => 'nullable|string|max:255',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $leg = $shipment->legs()->updateOrCreate(
            ['sequence' => $data['sequence']],
            $data
        );
        AuditLog::record('shipment.leg_saved', $leg, [], $leg->toArray());

        return back()->with('status', 'Shipment leg saved.');
    }

    public function storeExpense(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'shipment_leg_id' => [
                'nullable',
                Rule::exists('shipment_legs', 'id')->where('shipment_id', $shipment->id),
            ],
            'expense_type' => 'required|in:air_freight,sea_freight,customs_duty,port_warehouse,last_mile,other',
            'estimated_amount' => 'required|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,submitted,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $expense = null;

        DB::transaction(function () use ($request, $shipment, $data, &$expense) {
            if ($data['status'] === 'approved') {
                $data['approved_by'] = optional($request->user())->id;
                $data['approved_at'] = now();
            }

            $expense = $shipment->expenses()->create($data);
            AuditLog::record('shipment.expense_added', $expense, [], $expense->toArray());

            if ($expense->status === 'approved') {
                $this->postApprovedExpenseToLedger($expense);
            }
        });

        return back()->with('status', 'Shipment expense recorded.');
    }

    public function storePod(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'received_by' => 'nullable|string|max:255',
            'receiver_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'delivered_at' => 'nullable|date',
        ]);

        $pod = $shipment->pod;
        $old = $pod?->only(['document_path', 'received_by', 'receiver_phone', 'notes', 'delivered_at']) ?? [];

        if ($request->hasFile('document')) {
            if ($pod?->document_path) {
                Storage::disk('public')->delete($pod->document_path);
            }

            $data['document_path'] = $request->file('document')->store('shipments/pod', 'public');
        }

        unset($data['document']);

        $pod = $shipment->pod()->updateOrCreate([], $data);
        AuditLog::record('shipment.pod_saved', $pod, $old, $pod->only(['document_path', 'received_by', 'receiver_phone', 'notes', 'delivered_at']));

        return back()->with('status', 'Shipment POD saved.');
    }

    public function approveExpense(Request $request, Shipment $shipment, ShipmentExpense $expense)
    {
        abort_unless((int) $expense->shipment_id === (int) $shipment->id, 404);

        DB::transaction(function () use ($request, $expense) {
            $old = $expense->only(['status', 'approved_by', 'approved_at']);
            $expense->approve(optional($request->user())->id);
            AuditLog::record('shipment.expense_approved', $expense, $old, $expense->only(['status', 'approved_by', 'approved_at']));
            $this->postApprovedExpenseToLedger($expense->fresh(['shipment']));
        });

        return back()->with('status', 'Shipment expense approved.');
    }

    public function lockPricing(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'final_unit_rate' => 'required|numeric|min:0',
        ]);

        $old = $shipment->only(['final_unit_rate', 'final_price', 'pricing_locked']);
        $shipment->final_unit_rate = $data['final_unit_rate'];
        $shipment->lockPricing(optional($request->user())->id);
        AuditLog::record('shipment.pricing_locked', $shipment, $old, $shipment->only(['final_unit_rate', 'final_price', 'pricing_locked']));

        return back()->with('status', 'Shipment pricing locked.');
    }

    public function createInvoice(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'invoice_type' => 'required|in:proforma,final',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
        ]);

        if ($data['invoice_type'] === 'final' && ! $shipment->pricing_locked) {
            return back()->withErrors(['invoice' => 'Lock final pricing before issuing a final invoice.']);
        }

        $invoiceCurrency = $shipment->client?->currency ?? Currency::baseCode();
        if (Currency::requiresExchangeRate($invoiceCurrency, $data['exchange_rate'] ?? null)) {
            return back()
                ->withErrors(['exchange_rate' => 'Enter an exchange rate for ' . $invoiceCurrency . ' to ' . Currency::baseCode() . ' before creating this invoice.'])
                ->withInput();
        }

        $invoice = DB::transaction(function () use ($shipment, $data) {
            $shipment->loadMissing('packages');
            $amount = $data['invoice_type'] === 'final'
                ? (float) ($shipment->final_price ?? $shipment->estimated_price)
                : (float) $shipment->estimated_price;

            $invoice = Invoice::create([
                'order_id' => $shipment->order_id,
                'shipment_id' => $shipment->id,
                'number' => $this->invoiceNumber($data['invoice_type']),
                'invoice_type' => $data['invoice_type'],
                'issued_at' => Carbon::today(),
                'due_at' => Carbon::today()->addDays(7),
                'net_total' => round($amount, 2),
                'vat_amount' => 0,
                'withholding' => 0,
                'status' => $data['invoice_type'] === 'proforma' ? 'draft' : 'issued',
                'locked_at' => $data['invoice_type'] === 'final' ? now() : null,
            ] + Currency::invoicePayload(
                $shipment->client?->currency ?? null,
                $data['exchange_rate'] ?? null
            ));

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Freight charges for ' . $shipment->shipment_no,
                'quantity' => 1,
                'unit_price' => $invoice->net_total,
                'line_total' => $invoice->net_total,
            ]);

            if ($data['invoice_type'] === 'final') {
                LedgerEntry::create($this->ledgerPayloadForInvoice($invoice, [
                    'account' => 'Accounts Receivable',
                    'description' => 'Final invoice ' . $invoice->number,
                    'debit' => $invoice->net_total,
                    'credit' => 0,
                    'order_id' => $shipment->order_id,
                    'invoice_id' => $invoice->id,
                ]));

                LedgerEntry::create($this->ledgerPayloadForInvoice($invoice, [
                    'account' => 'Freight Revenue',
                    'description' => 'Final invoice ' . $invoice->number,
                    'debit' => 0,
                    'credit' => $invoice->net_total,
                    'order_id' => $shipment->order_id,
                    'invoice_id' => $invoice->id,
                ]));

                if ($shipment->status === 'delivered') {
                    $shipment->transitionTo('final_invoiced');
                }
            }

            AuditLog::record('shipment.invoice_created', $invoice, [], $invoice->toArray());

            return $invoice;
        });

        return redirect()
            ->route('admin.finance.show', $invoice)
            ->with('status', ucfirst($data['invoice_type']) . ' invoice created.');
    }

    protected function validatedShipment(Request $request): array
    {
        return $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'client_id' => 'required|exists:clients,id',
            'agent_id' => 'nullable|exists:agents,id',
            'shipment_no' => 'nullable|string|max:80|unique:shipments,shipment_no',
            'mode' => ['required', Rule::in(Shipment::MODES)],
            'origin_country' => 'required|string|max:80',
            'destination_country' => 'required|string|max:80',
            'origin_warehouse_id' => 'nullable|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|exists:warehouses,id',
            'estimated_departure' => 'nullable|date',
            'estimated_arrival' => 'nullable|date',
            'estimated_unit_rate' => 'nullable|numeric|min:0',
            'pricing_basis' => ['nullable', Rule::in(ShipmentRateCard::BILLING_UNITS)],
            'volumetric_divisor' => 'nullable|integer|min:1|max:100000',
            'notes' => 'nullable|string',
        ]);
    }

    protected function applyEstimatedPricing(array $data): array
    {
        $client = Client::find($data['client_id']);
        $data['agent_id'] = $data['agent_id'] ?? $client?->agent_id;

        if (! $data['agent_id']) {
            throw ValidationException::withMessages([
                'agent_id' => 'Select an internal agent or assign one to the client profile before creating a shipment.',
            ]);
        }

        $manualRate = array_key_exists('estimated_unit_rate', $data) && (float) $data['estimated_unit_rate'] > 0;

        $data['pricing_basis'] = $data['pricing_basis'] ?? 'chargeable_kg';
        $data['volumetric_divisor'] = $data['volumetric_divisor'] ?? ShipmentPackage::VOLUMETRIC_DIVISOR;

        if ($manualRate) {
            $data['estimated_rate_card_id'] = null;

            return $data;
        }

        $rateCard = ShipmentRateCard::bestMatch($data, $data['estimated_departure'] ?? null);

        if ($rateCard) {
            $data['estimated_rate_card_id'] = $rateCard->id;
            $data['estimated_unit_rate'] = $rateCard->rate;
            $data['pricing_basis'] = $rateCard->billing_unit;
            $data['volumetric_divisor'] = $rateCard->volumetric_divisor;

            return $data;
        }

        $data['estimated_unit_rate'] = $data['estimated_unit_rate'] ?? 0;
        $data['estimated_rate_card_id'] = null;

        return $data;
    }

    protected function seedDefaultLegs(Shipment $shipment): void
    {
        $legs = [
            ['sequence' => 1, 'leg_type' => 'origin', 'from_location' => $shipment->origin_country, 'to_location' => 'Origin warehouse'],
            ['sequence' => 2, 'leg_type' => 'international', 'from_location' => 'Origin warehouse', 'to_location' => $shipment->destination_country],
            ['sequence' => 3, 'leg_type' => 'customs', 'from_location' => 'Port / airport', 'to_location' => 'Customs clearance'],
            ['sequence' => 4, 'leg_type' => 'last_mile', 'from_location' => 'Destination hub', 'to_location' => 'Client delivery'],
        ];

        foreach ($legs as $leg) {
            $shipment->legs()->create($leg);
        }
    }

    protected function invoiceNumber(string $type): string
    {
        $prefix = $type === 'proforma' ? 'PRO' : 'FIN';

        do {
            $number = $prefix . '-' . now()->format('Ymd') . '-' . random_int(1000, 9999);
        } while (Invoice::where('number', $number)->exists());

        return $number;
    }

    protected function notifyRoles(array $roles, array $payload): void
    {
        if (! Schema::hasTable('notifications') || ! Schema::hasTable('users')) {
            return;
        }

        User::query()
            ->get()
            ->filter(fn (User $user) => $user->hasAnyRole($roles))
            ->each(fn (User $user) => $user->notify(new SystemAlertNotification($payload)));
    }

    protected function postApprovedExpenseToLedger(ShipmentExpense $expense): void
    {
        if (! Schema::hasColumn('ledger_entries', 'shipment_expense_id')) {
            return;
        }

        $amount = round((float) $expense->effective_amount, 2);
        if ($amount <= 0) {
            return;
        }

        if (LedgerEntry::where('shipment_expense_id', $expense->id)->exists()) {
            return;
        }

        $expense->loadMissing('shipment');
        $shipment = $expense->shipment;
        $description = 'Approved ' . str_replace('_', ' ', $expense->expense_type)
            . ' for shipment ' . ($shipment?->shipment_no ?? ('#' . $expense->shipment_id));

        $context = [
            'order_id' => $shipment?->order_id,
            'client_id' => $shipment?->client_id,
            'shipment_id' => $expense->shipment_id,
            'shipment_expense_id' => $expense->id,
        ];

        LedgerEntry::create(array_merge($context, [
            'account' => $this->expenseLedgerAccount($expense->expense_type),
            'description' => $description,
            'debit' => $amount,
            'credit' => 0,
        ]));

        LedgerEntry::create(array_merge($context, [
            'account' => 'Accrued Logistics Payable',
            'description' => $description,
            'debit' => 0,
            'credit' => $amount,
        ]));
    }

    protected function expenseLedgerAccount(string $expenseType): string
    {
        return match ($expenseType) {
            'air_freight' => 'Air Freight Cost',
            'sea_freight' => 'Sea Freight Cost',
            'customs_duty' => 'Customs & Duty Expense',
            'port_warehouse' => 'Port & Warehouse Expense',
            'last_mile' => 'Last Mile Delivery Expense',
            default => 'Logistics Expense',
        };
    }

    protected function ledgerPayloadForInvoice(Invoice $invoice, array $payload): array
    {
        return Currency::ledgerPayload(
            $payload,
            $invoice->currency_code ?? Currency::baseCode(),
            $invoice->exchange_rate ?? null
        );
    }
}
