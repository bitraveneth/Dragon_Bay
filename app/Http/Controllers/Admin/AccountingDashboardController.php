<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\SalaryDistribution;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountingDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        [$from, $to, $range] = $this->resolvePeriod($request);
        $rangeOptions = [
            '7d' => 'Last 7 days',
            'month' => 'This month',
            'quarter' => 'This quarter',
            'year' => 'This year',
            'custom' => 'Custom range',
        ];

        $invoices = Invoice::query()
            ->select(['id', 'issued_at', 'net_total', 'vat_amount', 'withholding'])
            ->with('creditNotes')
            ->whereBetween('issued_at', [$from, $to])
            ->get();

        $totalInvoices = $invoices->count();
        $netSales = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        }), 2);
        $vatTotal = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($from, $to));
        }), 2);
        $withholdingTotal = $invoices->sum('withholding');

        $collected = Receipt::whereBetween('received_at', [$from, $to])->sum('amount');

        $outstanding = Invoice::query()
            ->with(['receipts', 'creditNotes', 'advanceApplications'])
            ->whereDate('issued_at', '<=', $to->toDateString())
            ->whereIn('status', ['issued', 'adjusted'])
            ->get()
            ->sum(fn (Invoice $invoice) => (float) $invoice->outstandingAsOf($to));

        $expenses = (float) Expense::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');
        $giftExpenses = (float) CustomerGift::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');
        $campaignExpenses = Campaign::where(function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to])
                ->orWhere(function ($campaignQuery) use ($from, $to) {
                    $campaignQuery
                        ->whereDate('start_date', '>=', $from->toDateString())
                        ->whereDate('start_date', '<=', $to->toDateString());
                });
        })->whereIn('status', [
            Campaign::STATUS_RUNNING,
            Campaign::STATUS_COMPLETED,
            'active',
            'paused',
        ])->sum('cost');
        $totalExpenses = $expenses + $giftExpenses + $campaignExpenses;

        $salaryDistributions = SalaryDistribution::whereBetween('period_start', [$from, $to])->get();
        $totalPayroll = $salaryDistributions->sum(function ($d) {
            return $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission;
        });

        $netProfitEstimate = $netSales - ($totalExpenses + $totalPayroll);

        return view('admin.accounting.dashboard', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $rangeOptions,
            'periodLabel' => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            'totalInvoices' => $totalInvoices,
            'netSales' => $netSales,
            'vatTotal' => $vatTotal,
            'withholdingTotal' => $withholdingTotal,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'totalExpenses' => $totalExpenses,
            'totalPayroll' => $totalPayroll,
            'netProfitEstimate' => $netProfitEstimate,
        ]);
    }

    protected function resolvePeriod(Request $request): array
    {
        $range = $request->query('range');
        $hasCustomDates = $request->filled('from') || $request->filled('to');
        $today = Carbon::today();

        if ($range === 'custom' || (! $range && $hasCustomDates)) {
            $from = $request->filled('from')
                ? Carbon::parse($request->query('from'))->startOfDay()
                : $today->copy()->startOfMonth();
            $to = $request->filled('to')
                ? Carbon::parse($request->query('to'))->endOfDay()
                : $today->copy()->endOfMonth();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to, 'custom'];
        }

        switch ($range) {
            case '7d':
                $from = $today->copy()->subDays(6)->startOfDay();
                $to = $today->copy()->endOfDay();
                break;
            case 'quarter':
                $from = $today->copy()->startOfQuarter();
                $to = $today->copy()->endOfDay();
                break;
            case 'year':
                $from = $today->copy()->startOfYear();
                $to = $today->copy()->endOfDay();
                break;
            case 'month':
            default:
                $from = $today->copy()->startOfMonth();
                $to = $today->copy()->endOfDay();
                $range = 'month';
                break;
        }

        return [$from, $to, $range];
    }
}
