<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalesDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfPeriod = $today->copy()->endOfDay();

        $invoices = Invoice::query()
            ->select(['id', 'order_id', 'issued_at', 'net_total', 'vat_amount', 'withholding'])
            ->with(['order.agent', 'creditNotes', 'items.product', 'receipts', 'advanceApplications'])
            ->whereBetween('issued_at', [$startOfMonth, $endOfPeriod])
            ->orderByDesc('issued_at')
            ->get();

        $totalInvoices = $invoices->count();
        $netSales = round((float) $invoices->sum(function (Invoice $invoice) use ($startOfMonth, $endOfPeriod) {
            return $invoice->netSalesAfterCreditsInRange($startOfMonth, $endOfPeriod);
        }), 2);
        $vatTotal = round((float) $invoices->sum(function (Invoice $invoice) use ($startOfMonth, $endOfPeriod) {
            return max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($startOfMonth, $endOfPeriod));
        }), 2);
        $withholdingTotal = $invoices->sum('withholding');

        $collected = Receipt::whereBetween('received_at', [$startOfMonth, $endOfPeriod])->sum('amount');

        $outstanding = round((float) $invoices->sum(fn (Invoice $invoice) => (float) $invoice->outstandingAsOf($endOfPeriod)), 2);

        // Simple breakdowns
        $topAgents = $invoices
            ->filter(fn ($invoice) => $invoice->order && $invoice->order->agent)
            ->groupBy(fn ($invoice) => (string) $invoice->order->agent->id)
            ->map(function ($group) use ($startOfMonth, $endOfPeriod) {
                $agent = $group->first()->order->agent;

                return [
                    'agent_id' => $agent->id,
                    'agent_name' => $agent->name ?? 'Unknown agent',
                    'net_sales' => $group->sum(function (Invoice $invoice) use ($startOfMonth, $endOfPeriod) {
                        return $invoice->netSalesAfterCreditsInRange($startOfMonth, $endOfPeriod);
                    }),
                ];
            })
            ->sortByDesc('net_sales')
            ->values()
            ->take(5);

        $topProducts = $this->topProductsForPeriod($invoices, $startOfMonth, $endOfPeriod);

        $recentOrders = Order::with('agent')
            ->where('order_type', '!=', 'return')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.sales.dashboard', [
            'periodLabel' => $startOfMonth->format('d M Y') . ' – ' . $endOfPeriod->format('d M Y'),
            'totalInvoices' => $totalInvoices,
            'netSales' => $netSales,
            'vatTotal' => $vatTotal,
            'withholdingTotal' => $withholdingTotal,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'topAgents' => $topAgents,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
        ]);
    }

    protected function topProductsForPeriod(Collection $invoices, Carbon $from, Carbon $to): Collection
    {
        return $invoices
            ->flatMap(function (Invoice $invoice) use ($from, $to) {
                $invoiceNet = max((float) $invoice->net_total, 0.0);
                $creditNet = min($invoice->creditNotesNetTotalInRange($from, $to), $invoiceNet);

                return $invoice->items->map(function (InvoiceItem $item) use ($invoiceNet, $creditNet) {
                    $lineTotal = (float) $item->line_total;
                    $creditShare = $invoiceNet > 0
                        ? $creditNet * ($lineTotal / $invoiceNet)
                        : 0.0;

                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name ?? 'Unknown product',
                        'qty' => (float) $item->quantity,
                        'net' => max(0.0, $lineTotal - $creditShare),
                    ];
                });
            })
            ->groupBy(fn (array $row) => (string) ($row['product_id'] ?? 'unknown'))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product_id' => $first['product_id'],
                    'product_name' => $first['product_name'],
                    'qty' => $group->sum('qty'),
                    'net' => $group->sum('net'),
                ];
            })
            ->sortByDesc('net')
            ->values()
            ->take(5);
    }
}
