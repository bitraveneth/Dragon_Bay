<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\CreditNote;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $client = $this->client($request);

        $orderQuery = Order::query()->where('client_id', $client->id);
        $shipmentQuery = Shipment::query()->where('client_id', $client->id);
        $invoiceQuery = $this->clientInvoicesQuery($client->id)->with(['receipts', 'creditNotes', 'advanceApplications']);

        $openOrders = (clone $orderQuery)
            ->whereIn('status', ['draft', 'confirmed', 'picked', 'packed', 'dispatched'])
            ->count();

        $activeShipments = (clone $shipmentQuery)
            ->whereNotIn('status', ['closed'])
            ->count();

        $deliveredThisMonth = (clone $shipmentQuery)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $outstanding = (clone $invoiceQuery)->get()->sum(fn (Invoice $invoice) => (float) $invoice->outstanding);

        $lastReceipt = Receipt::query()
            ->whereHas('invoice', function (Builder $query) use ($client) {
                $this->scopeInvoiceQueryToClient($query, $client->id);
            })
            ->latest('received_at')
            ->first();

        $recentShipments = (clone $shipmentQuery)
            ->with(['order', 'pod'])
            ->latest()
            ->take(5)
            ->get();

        $recentInvoices = (clone $invoiceQuery)
            ->with(['order', 'shipment'])
            ->latest('issued_at')
            ->take(5)
            ->get();

        return view('portal.dashboard', compact(
            'client',
            'openOrders',
            'activeShipments',
            'deliveredThisMonth',
            'outstanding',
            'lastReceipt',
            'recentShipments',
            'recentInvoices',
        ));
    }

    public function shipments(Request $request)
    {
        $client = $this->client($request);

        $shipments = Shipment::with(['order', 'packages', 'legs', 'pod'])
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(12);

        return view('portal.shipments.index', compact('client', 'shipments'));
    }

    public function showShipment(Request $request, Shipment $shipment)
    {
        $client = $this->client($request);
        abort_unless((int) $shipment->client_id === (int) $client->id, 404);

        $shipment->load(['order', 'packages', 'legs.expenses', 'expenses.leg', 'pod', 'invoices']);

        return view('portal.shipments.show', compact('client', 'shipment'));
    }

    public function orders(Request $request)
    {
        $client = $this->client($request);

        $orders = Order::with(['items.product', 'statusHistory', 'delivery', 'shipment'])
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(12);

        return view('portal.orders.index', compact('client', 'orders'));
    }

    public function showOrder(Request $request, Order $order)
    {
        $client = $this->client($request);
        abort_unless((int) $order->client_id === (int) $client->id, 404);

        $order->load(['items.product', 'statusHistory', 'delivery.route', 'delivery.vehicle', 'shipment']);

        return view('portal.orders.show', compact('client', 'order'));
    }

    public function invoices(Request $request)
    {
        $client = $this->client($request);

        $invoices = $this->clientInvoicesQuery($client->id)
            ->with(['order', 'shipment', 'receipts', 'creditNotes'])
            ->latest('issued_at')
            ->paginate(12);

        return view('portal.invoices.index', compact('client', 'invoices'));
    }

    public function showInvoice(Request $request, Invoice $invoice)
    {
        $client = $this->client($request);
        abort_unless($this->invoiceBelongsToClient($invoice, $client->id), 404);

        $invoice->load(['order', 'shipment', 'items.product', 'receipts', 'creditNotes', 'advanceApplications']);

        return view('portal.invoices.show', compact('client', 'invoice'));
    }

    public function deliveries(Request $request)
    {
        $client = $this->client($request);

        $deliveries = Delivery::with(['order', 'route', 'vehicle', 'pod'])
            ->whereHas('order', fn (Builder $query) => $query->where('client_id', $client->id))
            ->latest()
            ->paginate(12);

        return view('portal.deliveries.index', compact('client', 'deliveries'));
    }

    public function statement(Request $request)
    {
        $client = $this->client($request);

        $from = $request->query('from');
        $to = $request->query('to');

        $fromDate = $from ? now()->parse($from)->startOfDay() : now()->startOfMonth();
        $toDate = $to ? now()->parse($to)->endOfDay() : now()->endOfMonth();

        $invoices = $this->clientInvoicesQuery($client->id)
            ->with(['order', 'shipment'])
            ->whereBetween('issued_at', [$fromDate, $toDate])
            ->get();

        $receipts = Receipt::with('invoice')
            ->whereHas('invoice', function (Builder $query) use ($client) {
                $this->scopeInvoiceQueryToClient($query, $client->id);
            })
            ->whereBetween('received_at', [$fromDate, $toDate])
            ->get();

        $credits = CreditNote::with('invoice')
            ->whereHas('invoice', function (Builder $query) use ($client) {
                $this->scopeInvoiceQueryToClient($query, $client->id);
            })
            ->whereBetween('issued_at', [$fromDate, $toDate])
            ->get();

        $rows = [];

        foreach ($invoices as $invoice) {
            $rows[] = [
                'date' => $invoice->issued_at?->toDateString(),
                'type' => 'invoice',
                'reference' => $invoice->number,
                'description' => $invoice->shipment?->shipment_no
                    ? 'Shipment ' . $invoice->shipment->shipment_no
                    : 'Order #' . $invoice->order_id,
                'amount' => $invoice->cash_total,
            ];
        }

        foreach ($receipts as $receipt) {
            $rows[] = [
                'date' => $receipt->received_at?->toDateString(),
                'type' => 'receipt',
                'reference' => $receipt->invoice?->number,
                'description' => 'Payment received',
                'amount' => (float) $receipt->amount * -1,
            ];
        }

        foreach ($credits as $credit) {
            $rows[] = [
                'date' => $credit->issued_at?->toDateString(),
                'type' => 'credit_note',
                'reference' => $credit->number,
                'description' => $credit->reason ?: 'Credit note',
                'amount' => (float) $credit->amount * -1,
            ];
        }

        usort($rows, static fn (array $left, array $right) => strcmp($left['date'] ?? '', $right['date'] ?? ''));

        $balance = 0.0;
        foreach ($rows as &$row) {
            $balance += (float) $row['amount'];
            $row['balance'] = $balance;
        }
        unset($row);

        return view('portal.statement', compact('client', 'rows', 'fromDate', 'toDate', 'balance'));
    }

    public function profile(Request $request)
    {
        $client = $this->client($request);
        $user = $request->user();

        $stats = [
            'orders' => Order::where('client_id', $client->id)->count(),
            'shipments' => Shipment::where('client_id', $client->id)->count(),
            'invoices' => $this->clientInvoicesQuery($client->id)->count(),
        ];

        return view('portal.profile', compact('client', 'user', 'stats'));
    }

    protected function client(Request $request)
    {
        $user = $request->user()->loadMissing('client');

        abort_unless($user->client, 403, 'Client portal access requires a linked client account.');

        return $user->client;
    }

    protected function clientInvoicesQuery(int $clientId)
    {
        return Invoice::query()->where(function (Builder $query) use ($clientId) {
            $this->scopeInvoiceQueryToClient($query, $clientId);
        });
    }

    protected function scopeInvoiceQueryToClient(Builder $query, int $clientId): void
    {
        $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('client_id', $clientId))
            ->orWhereHas('shipment', fn (Builder $shipmentQuery) => $shipmentQuery->where('client_id', $clientId));
    }

    protected function invoiceBelongsToClient(Invoice $invoice, int $clientId): bool
    {
        $invoice->loadMissing(['order', 'shipment']);

        return (int) ($invoice->order?->client_id ?? 0) === $clientId
            || (int) ($invoice->shipment?->client_id ?? 0) === $clientId;
    }
}
