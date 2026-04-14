<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentAdvance;
use App\Models\AgentAdvanceApplication;
use App\Models\CreditNote;
use App\Models\DeliveryItem;
use App\Models\LedgerEntry;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use App\Services\CommissionSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['order.agent', 'receipts', 'creditNotes', 'advanceApplications'])->latest()->paginate(10);
        return view('admin.finance.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.agent', 'items.product', 'receipts', 'creditNotes', 'advanceApplications']);
        return view('admin.finance.show', compact('invoice'));
    }

    public function updateWithholding(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'withholding' => 'required|numeric|min:0',
        ]);

        $grossTotal = $invoice->net_total + $invoice->vat_amount;
        if ($data['withholding'] > $grossTotal) {
            return back()->withErrors([
                'withholding' => 'Withholding cannot exceed invoice total (net + VAT).',
            ]);
        }

        DB::transaction(function () use ($invoice, $data) {
            $previousWithholding = (float) $invoice->withholding;
            $invoice->withholding = round((float) $data['withholding'], 2);
            $invoice->save();

            $this->recordWithholdingLedgerAdjustment(
                $invoice,
                $invoice->withholding - $previousWithholding,
                'Withholding adjustment on ' . $invoice->number
            );

            $invoice->recalculateStatus();
        });

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Withholding updated.');
    }

    public function createFromOrder(Order $order)
    {
        if ($order->status !== 'delivered') {
            abort(400, 'Only delivered orders can be invoiced.');
        }

        $existingInvoice = Invoice::where('order_id', $order->id)->first();
        if ($existingInvoice) {
            return redirect()
                ->route('admin.finance.show', $existingInvoice)
                ->with('status', 'Invoice already exists for this order.');
        }

        $invoice = $this->ensureInvoiceForOrder($order);

        if (! $invoice) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', 'This order type does not create a sales invoice automatically.');
        }

        return redirect()->route('admin.finance.index')->with('status', 'Invoice created from order.');
    }

    public function ensureInvoiceForOrder(Order $order): ?Invoice
    {
        if ($order->status !== 'delivered') {
            abort(400, 'Only delivered orders can be invoiced.');
        }

        $existingInvoice = Invoice::where('order_id', $order->id)->first();
        if ($existingInvoice) {
            return $existingInvoice;
        }

        if (! $this->orderRequiresInvoice($order)) {
            return null;
        }

        $relations = ['items.product.taxClass', 'agent'];
        if (Schema::hasTable('deliveries') && Schema::hasTable('delivery_items')) {
            $relations[] = 'delivery.items';
        }
        $order->loadMissing($relations);

        $deliveryItems = (Schema::hasTable('deliveries') && Schema::hasTable('delivery_items') && $order->delivery)
            ? DeliveryItem::summarizeForOrderItems($order->delivery->items)
            : collect();

        $netTotal = 0.0;
        $vatAmount = 0.0;
        $invoiceLines = [];

        foreach ($order->items as $item) {
            $quantity = (float) $item->quantity;
            $deliveryItem = $deliveryItems->get($item->id);

            if ($deliveryItem) {
                $quantity = (float) $deliveryItem['realized_quantity'];
            }

            if ($quantity <= 0) {
                continue;
            }

            $lineTotal = round($quantity * (float) $item->unit_price, 2);
            $netTotal += $lineTotal;

            $rate = (float) (optional($item->product->taxClass)->rate ?? 0);
            if ($rate > 0) {
                $vatAmount += round($lineTotal * ($rate / 100), 2);
            }

            $invoiceLines[] = [
                'product_id' => $item->product_id,
                'description' => $item->product?->name ?? 'Order item',
                'quantity' => (int) round($quantity),
                'unit_price' => $item->unit_price,
                'line_total' => $lineTotal,
            ];
        }

        if ($netTotal <= 0 || empty($invoiceLines)) {
            return null;
        }

        $invoiceData = [
            'order_id' => $order->id,
            'issued_at' => Carbon::today(),
            'due_at' => Carbon::today()->addDays(7),
            'net_total' => round($netTotal, 2),
            'vat_amount' => round($vatAmount, 2),
            'withholding' => 0,
            'status' => 'issued',
        ];

        $agent = $order->agent;
        if ($agent && $agent->withholding_rate > 0) {
            $grossTotal = $invoiceData['net_total'] + $invoiceData['vat_amount'];
            $invoiceData['withholding'] = round($grossTotal * ((float) $agent->withholding_rate / 100), 2);
        }

        $invoice = null;

        DB::transaction(function () use ($invoiceData, $order, $invoiceLines, &$invoice) {
            $invoice = Invoice::create(array_merge($invoiceData, [
                'number' => 'INV-TMP-' . Str::uuid(),
            ]));

            $invoice->update([
                'number' => $this->formatInvoiceNumber($invoice->id),
            ]);

            foreach ($invoiceLines as $line) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line['product_id'],
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }

            $invoiceDescription = 'Invoice ' . $invoice->number;

            LedgerEntry::create([
                'account' => 'Accounts Receivable',
                'description' => $invoiceDescription,
                'debit' => $invoice->net_total + $invoice->vat_amount,
                'credit' => 0,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
            ]);

            LedgerEntry::create([
                'account' => 'Sales Revenue',
                'description' => $invoiceDescription,
                'debit' => 0,
                'credit' => $invoice->net_total,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
            ]);

            if ((float) $invoice->vat_amount > 0) {
                LedgerEntry::create([
                    'account' => 'VAT Payable',
                    'description' => 'VAT on ' . $invoice->number,
                    'debit' => 0,
                    'credit' => $invoice->vat_amount,
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                ]);
            }

            $this->recordWithholdingLedgerAdjustment(
                $invoice,
                (float) $invoice->withholding,
                'Withholding on ' . $invoice->number
            );

            $this->applyAvailableAgentAdvances($invoice);
            $invoice->recalculateStatus();
        });

        return $invoice?->fresh();
    }

    public function storeReceipt(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'received_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ((float) $data['amount'] > (float) $invoice->outstanding) {
            return back()->withErrors([
                'amount' => 'Receipt amount exceeds the remaining outstanding amount for this invoice.',
            ]);
        }

        $receipt = null;

        DB::transaction(function () use ($invoice, $data, &$receipt) {
            $receipt = Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? null,
                'received_at' => $data['received_at'] ?? Carbon::today(),
                'notes' => $data['notes'] ?? null,
            ]);

            $description = $this->receiptLedgerDescription($receipt, $invoice);

            LedgerEntry::create([
                'account' => 'Bank',
                'description' => $description,
                'debit' => $receipt->amount,
                'credit' => 0,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            LedgerEntry::create([
                'account' => 'Accounts Receivable',
                'description' => $description,
                'debit' => 0,
                'credit' => $receipt->amount,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            $invoice->recalculateStatus();
        });

        app(CommissionSettlementService::class)->syncForInvoice(
            $invoice->fresh(['order', 'shipment', 'receipts', 'creditNotes', 'advanceApplications'])
        );

        $this->notifyRoles(
            ['super_admin', 'admin', 'accounts_officer'],
            [
                'title' => 'Client payment recorded',
                'message' => 'Payment of BDT ' . number_format((float) $receipt->amount, 2) . ' recorded for invoice ' . $invoice->number . '.',
                'variant' => 'success',
                'source' => 'Finance',
                'link' => route('admin.finance.show', $invoice),
                'context' => [
                    'invoice_id' => $invoice->id,
                    'receipt_id' => $receipt->id,
                    'amount' => (float) $receipt->amount,
                ],
            ]
        );

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Receipt recorded.');
    }

    public function destroyReceipt(Receipt $receipt)
    {
        $invoice = $receipt->invoice;
        $message = $receipt->reconciled
            ? 'Reconciled receipts cannot be deleted. Preserve the audit trail and record an adjusting entry instead.'
            : 'Posted receipts cannot be deleted. Preserve the audit trail and record an adjusting entry instead.';

        return $invoice
            ? redirect()->route('admin.finance.show', $invoice)->withErrors(['receipt' => $message])
            : back()->withErrors(['receipt' => $message]);
    }

    public function showCreditNoteForm(Invoice $invoice)
    {
        $invoice->load(['order.agent', 'receipts', 'creditNotes', 'advanceApplications']);

        $grossTotal = (float) $invoice->net_total + (float) $invoice->vat_amount;
        $cashTotal = $grossTotal - (float) $invoice->withholding;
        $remainingCredit = max(
            0,
            $cashTotal
            - (float) $invoice->receipts_total
            - (float) $invoice->advances_applied_total
            - (float) $invoice->credits_total
        );

        return view('admin.finance.credit_note', compact('invoice', 'remainingCredit'));
    }

    public function storeCreditNote(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);
        // Maximum credit cannot exceed the remaining receivable after
        // withholding, receipts, and applied advances.
        $grossTotal = $invoice->net_total + $invoice->vat_amount;
        $cashTotal  = $grossTotal - $invoice->withholding;
        $maxCredit = max(0, $cashTotal - $invoice->receipts_total - $invoice->advances_applied_total);
        $alreadyCredited = CreditNote::where('invoice_id', $invoice->id)->sum('amount');
        $remainingCredit = max(0, $maxCredit - $alreadyCredited);

        if ($data['amount'] > $remainingCredit) {
            return back()->withErrors([
                'amount' => 'Credit amount exceeds remaining invoice value. Remaining credit allowed: '
                    . number_format($remainingCredit, 2) . '.',
            ])->withInput();
        }

        $credit = null;
        $creditBreakdown = $invoice->creditBreakdown((float) $data['amount']);

        DB::transaction(function () use ($invoice, $data, $creditBreakdown, &$credit) {
            $credit = CreditNote::create([
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id,
                'number' => 'CN-TMP-' . Str::uuid(),
                'issued_at' => Carbon::today(),
                'amount' => $data['amount'],
                'reason' => $data['reason'] ?? null,
            ]);

            $credit->update([
                'number' => $this->formatCreditNoteNumber($credit->id),
            ]);

            LedgerEntry::create([
                'account' => 'Sales Returns',
                'description' => 'Credit note ' . $credit->number,
                'debit' => $creditBreakdown['net'],
                'credit' => 0,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            if ($creditBreakdown['vat'] > 0) {
                LedgerEntry::create([
                    'account' => 'VAT Payable',
                    'description' => 'VAT reversal on ' . $credit->number,
                    'debit' => $creditBreakdown['vat'],
                    'credit' => 0,
                    'order_id' => $invoice->order_id,
                    'invoice_id' => $invoice->id,
                ]);
            }

            LedgerEntry::create([
                'account' => 'Accounts Receivable',
                'description' => 'Credit note ' . $credit->number,
                'debit' => 0,
                'credit' => $credit->amount,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            $invoice->recalculateStatus();
        });

        return redirect()->route('admin.finance.show', $invoice)->with('status', 'Credit note created.');
    }

    public function destroyCreditNote(CreditNote $creditNote)
    {
        $invoice = $creditNote->invoice;
        $message = 'Posted credit notes cannot be deleted. Preserve the audit trail and issue an offsetting adjustment instead.';

        return $invoice
            ? redirect()->route('admin.finance.show', $invoice)->withErrors(['creditNote' => $message])
            : back()->withErrors(['creditNote' => $message]);
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['order.agent', 'items.product', 'receipts', 'creditNotes', 'advanceApplications']);

        $grossTotal    = $invoice->net_total + $invoice->vat_amount;
        $cashTotal     = $grossTotal - $invoice->withholding;
        $creditsTotal  = $invoice->creditNotes->sum('amount');
        $receiptsTotal = $invoice->receipts->sum('amount');
        $advancesTotal = $invoice->advanceApplications->sum('amount');
        $outstanding   = $cashTotal - $creditsTotal - $receiptsTotal - $advancesTotal;

        $pdf = Pdf::loadView('admin.finance.invoice_pdf', [
            'invoice'       => $invoice,
            'grossTotal'    => $grossTotal,
            'cashTotal'     => $cashTotal,
            'creditsTotal'  => $creditsTotal,
            'receiptsTotal' => $receiptsTotal,
            'advancesTotal' => $advancesTotal,
            'outstanding'   => $outstanding,
        ]);

        return $pdf->download('invoice-' . $invoice->number . '.pdf');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft' || LedgerEntry::where('invoice_id', $invoice->id)->exists()) {
            return redirect()->route('admin.finance.index')
                ->withErrors(['invoice' => 'Issued invoices cannot be deleted. Preserve the audit trail and adjust them through controlled finance entries instead.']);
        }

        if ($invoice->receipts()->exists() || $invoice->creditNotes()->exists()) {
            return redirect()->route('admin.finance.index')
                ->withErrors(['invoice' => 'Invoice has receipts or credit notes and cannot be deleted.']);
        }

        LedgerEntry::where('invoice_id', $invoice->id)->delete();
        $invoice->items()->delete();
        $invoice->delete();

        return redirect()->route('admin.finance.index')->with('status', 'Invoice deleted.');
    }

    protected function formatInvoiceNumber(int $invoiceId): string
    {
        return 'INV-' . str_pad((string) $invoiceId, 6, '0', STR_PAD_LEFT);
    }

    protected function formatCreditNoteNumber(int $creditNoteId): string
    {
        return 'CN-' . str_pad((string) $creditNoteId, 6, '0', STR_PAD_LEFT);
    }

    protected function receiptLedgerDescription(Receipt $receipt, Invoice $invoice): string
    {
        return 'Receipt #' . $receipt->id . ' for ' . $invoice->number;
    }

    protected function deleteReceiptLedgerEntries(Invoice $invoice, Receipt $receipt): void
    {
        $exactDescription = $this->receiptLedgerDescription($receipt, $invoice);

        $exactEntries = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('description', $exactDescription)
            ->get();

        if ($exactEntries->isNotEmpty()) {
            LedgerEntry::whereKey($exactEntries->pluck('id'))->delete();

            return;
        }

        $legacyEntries = LedgerEntry::where('invoice_id', $invoice->id)
            ->where('description', 'Receipt for ' . $invoice->number)
            ->where(function ($query) use ($receipt) {
                $query->where('debit', $receipt->amount)
                    ->orWhere('credit', $receipt->amount);
            })
            ->get();

        $isSafeLegacyPair = $legacyEntries->count() === 2
            && $legacyEntries->contains(fn (LedgerEntry $entry) => $entry->account === 'Bank' && (float) $entry->debit === (float) $receipt->amount)
            && $legacyEntries->contains(fn (LedgerEntry $entry) => $entry->account === 'Accounts Receivable' && (float) $entry->credit === (float) $receipt->amount);

        if ($isSafeLegacyPair) {
            LedgerEntry::whereKey($legacyEntries->pluck('id'))->delete();
        }
    }

    protected function orderRequiresInvoice(Order $order): bool
    {
        return in_array($order->order_type, ['regular', 'bulk'], true);
    }

    protected function applyAvailableAgentAdvances(Invoice $invoice): void
    {
        $invoice->loadMissing('order.agent');
        $agent = $invoice->order?->agent;

        if (! $agent) {
            return;
        }

        $remainingOutstanding = (float) $invoice->cash_total;
        if ($remainingOutstanding <= 0) {
            return;
        }

        $advances = AgentAdvance::where('agent_id', $agent->id)
            ->whereIn('status', ['open', 'partial'])
            ->whereDate('advanced_at', '<=', $invoice->issued_at->toDateString())
            ->orderBy('advanced_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($advances as $advance) {
            if ($remainingOutstanding <= 0) {
                break;
            }

            $availableAmount = $advance->available_amount;
            if ($availableAmount <= 0) {
                continue;
            }

            $applyAmount = min($availableAmount, $remainingOutstanding);

            AgentAdvanceApplication::create([
                'agent_advance_id' => $advance->id,
                'invoice_id' => $invoice->id,
                'amount' => $applyAmount,
                'applied_at' => Carbon::today(),
            ]);

            $advance->applied_amount = round((float) $advance->applied_amount + $applyAmount, 2);
            $advance->status = $advance->available_amount <= 0
                ? 'applied'
                : 'partial';
            $advance->save();

            $description = 'Advance applied from ' . $agent->name . ' to ' . $invoice->number;

            LedgerEntry::create([
                'account' => 'Agent Advances',
                'description' => $description,
                'debit' => $applyAmount,
                'credit' => 0,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            LedgerEntry::create([
                'account' => 'Accounts Receivable',
                'description' => $description,
                'debit' => 0,
                'credit' => $applyAmount,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            $remainingOutstanding -= $applyAmount;
        }
    }

    protected function recordWithholdingLedgerAdjustment(Invoice $invoice, float $delta, string $description): void
    {
        $delta = round($delta, 2);

        if (abs($delta) <= 0.00001) {
            return;
        }

        if ($delta > 0) {
            LedgerEntry::create([
                'account' => 'Withholding Tax Receivable',
                'description' => $description,
                'debit' => $delta,
                'credit' => 0,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            LedgerEntry::create([
                'account' => 'Accounts Receivable',
                'description' => $description,
                'debit' => 0,
                'credit' => $delta,
                'order_id' => $invoice->order_id,
                'invoice_id' => $invoice->id,
            ]);

            return;
        }

        $amount = abs($delta);

        LedgerEntry::create([
            'account' => 'Accounts Receivable',
            'description' => $description,
            'debit' => $amount,
            'credit' => 0,
            'order_id' => $invoice->order_id,
            'invoice_id' => $invoice->id,
        ]);

        LedgerEntry::create([
            'account' => 'Withholding Tax Receivable',
            'description' => $description,
            'debit' => 0,
            'credit' => $amount,
            'order_id' => $invoice->order_id,
            'invoice_id' => $invoice->id,
        ]);
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
}
