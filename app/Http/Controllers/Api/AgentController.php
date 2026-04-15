<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentPriceList;
use App\Models\AgentCommissionRule;
use App\Models\Order;
use App\Models\Product;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\CreditNote;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    protected function requireAgent(Request $request)
    {
        $user = $request->user();

        if (! $user->agent) {
            abort(403, 'Agent access required.');
        }

        return $user->agent;
    }

    public function products(Request $request)
    {
        $agent = $this->requireAgent($request);

        $products = Product::with(['packagingType', 'taxClass'])
            ->orderBy('name')
            ->get();

        $priceList = AgentPriceList::where('agent_id', $agent->id)
            ->pluck('price', 'product_id');

        $data = $products->map(function (Product $product) use ($priceList) {
            $agentPrice = $priceList[$product->id] ?? null;
            $effectivePrice = $agentPrice ?? $product->base_price;

            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'size' => $product->size,
                'volume_ml' => $product->volume_ml,
                'packaging' => $product->packagingType?->name,
                'tax_class' => $product->taxClass?->name,
                'tax_rate' => $product->taxClass?->rate,
                'base_price' => $product->base_price,
                'agent_price' => $agentPrice,
                'effective_price' => $effectivePrice,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function orders(Request $request)
    {
        $agent = $this->requireAgent($request);

        $orders = Order::with(['items.product', 'statusHistory', 'delivery'])
            ->where('agent_id', $agent->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($orders);
    }

    public function showOrder(Request $request, Order $order)
    {
        $agent = $this->requireAgent($request);

        if ($order->agent_id !== $agent->id) {
            abort(404);
        }

        $order->loadMissing(['items.product', 'statusHistory', 'delivery']);

        return response()->json($order);
    }

    public function storeOrder(Request $request)
    {
        $this->requireAgent($request);

        abort(403, 'Orders are created by staff only.');
    }

    public function deliveries(Request $request)
    {
        $agent = $this->requireAgent($request);

        $deliveries = Delivery::with(['order.items.product', 'route', 'vehicle'])
            ->whereHas('order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($deliveries);
    }

    public function statement(Request $request)
    {
        $agent = $this->requireAgent($request);

        $from = $request->query('from');
        $to = $request->query('to');

        $fromDate = $from ? now()->parse($from)->startOfDay() : now()->startOfMonth();
        $toDate = $to ? now()->parse($to)->endOfDay() : now()->endOfMonth();

        $invoices = Invoice::with('order')
            ->whereHas('order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->whereBetween('issued_at', [$fromDate, $toDate])
            ->get();

        $receipts = Receipt::with('invoice.order')
            ->whereHas('invoice.order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->whereBetween('received_at', [$fromDate, $toDate])
            ->get();

        $credits = CreditNote::with('invoice.order')
            ->whereHas('invoice.order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->whereBetween('issued_at', [$fromDate, $toDate])
            ->get();

        $rows = [];

        foreach ($invoices as $invoice) {
            $rows[] = [
                'date' => $invoice->issued_at?->format('Y-m-d'),
                'type' => 'invoice',
                'ref' => $invoice->number,
                'amount' => ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding,
            ];
        }

        foreach ($receipts as $receipt) {
            $rows[] = [
                'date' => $receipt->received_at?->format('Y-m-d'),
                'type' => 'receipt',
                'ref' => $receipt->invoice->number ?? null,
                'amount' => $receipt->amount * -1,
            ];
        }

        foreach ($credits as $credit) {
            $rows[] = [
                'date' => $credit->issued_at?->format('Y-m-d'),
                'type' => 'credit_note',
                'ref' => $credit->number,
                'amount' => $credit->amount * -1,
            ];
        }

        usort($rows, static function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        $balance = 0;
        foreach ($rows as &$row) {
            $balance += $row['amount'];
            $row['balance'] = $balance;
        }
        unset($row);

        return response()->json([
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'rows' => $rows,
            'closing_balance' => $balance,
        ]);
    }

    public function invoices(Request $request)
    {
        $agent = $this->requireAgent($request);

        $invoices = Invoice::with(['order.agent', 'items', 'receipts', 'creditNotes'])
            ->whereHas('order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->orderByDesc('issued_at')
            ->paginate(20);

        return response()->json($invoices);
    }

    public function showInvoice(Request $request, Invoice $invoice)
    {
        $agent = $this->requireAgent($request);

        if (! $invoice->order || $invoice->order->agent_id !== $agent->id) {
            abort(404);
        }

        $invoice->loadMissing(['order.agent', 'items.product', 'receipts', 'creditNotes']);

        return response()->json($invoice);
    }

    public function dashboard(Request $request)
    {
        $agent = $this->requireAgent($request);

        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $baseOrderQuery = Order::where('agent_id', $agent->id);

        $openOrders = (clone $baseOrderQuery)
            ->whereIn('status', ['draft', 'confirmed', 'picked', 'packed', 'dispatched'])
            ->count();

        $deliveredThisMonth = (clone $baseOrderQuery)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $invoices = Invoice::with('receipts')
            ->whereHas('order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->get();

        $outstanding = $invoices->sum(function (Invoice $invoice) {
            $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
            $paid = $invoice->receipts->sum('amount');

            return max($gross - $paid, 0);
        });

        $lastReceipt = Receipt::whereHas('invoice.order', function ($q) use ($agent) {
                $q->where('agent_id', $agent->id);
            })
            ->orderByDesc('received_at')
            ->first();

        return response()->json([
            'open_orders' => $openOrders,
            'delivered_this_month' => $deliveredThisMonth,
            'outstanding_balance' => $outstanding,
            'last_receipt' => $lastReceipt,
        ]);
    }
}
