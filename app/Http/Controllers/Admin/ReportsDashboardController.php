<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\ProductionRun;
use App\Models\SalaryDistribution;
use App\Models\Agent;
use Carbon\Carbon;

class ReportsDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $startOfYear = $today->copy()->startOfYear();
        $endOfPeriod = $today->copy()->endOfDay();

        // Revenue side
        $invoices = Invoice::with(['receipts', 'creditNotes', 'advanceApplications'])
            ->whereBetween('issued_at', [$startOfYear, $endOfPeriod])
            ->get();

        $grossRevenue = round((float) $invoices->sum(function (Invoice $invoice) use ($startOfYear, $endOfPeriod) {
            return $invoice->netSalesAfterCreditsInRange($startOfYear, $endOfPeriod);
        }), 2);
        $withholdingTotal = $invoices->sum('withholding');

        $totalCollections = round((float) $invoices->sum(function (Invoice $invoice) use ($startOfYear, $endOfPeriod) {
            return $invoice->receiptsTotalInRange($startOfYear, $endOfPeriod);
        }), 2);

        $outstanding = round((float) $invoices->sum(function (Invoice $invoice) use ($endOfPeriod) {
            return $invoice->outstandingAsOf($endOfPeriod);
        }), 2);

        // Cost side
        $expenses = (float) Expense::whereBetween('date', [$startOfYear, $endOfPeriod])
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');
        $giftExpenses = (float) CustomerGift::whereBetween('date', [$startOfYear, $endOfPeriod])
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');
        $campaignExpenses = Campaign::where(function ($query) use ($startOfYear, $endOfPeriod) {
            $query->whereBetween('created_at', [$startOfYear, $endOfPeriod])
                ->orWhereBetween('start_date', [$startOfYear->toDateString(), $endOfPeriod->toDateString()]);
        })->whereIn('status', [
            Campaign::STATUS_RUNNING,
            Campaign::STATUS_COMPLETED,
            'active',
            'paused',
        ])->sum('cost');
        $totalExpenses = $expenses + $giftExpenses + $campaignExpenses;

        $salaryDistributions = SalaryDistribution::whereBetween('period_start', [$startOfYear, $endOfPeriod])->get();
        $totalPayroll = $salaryDistributions->sum(function ($d) {
            return $d->base_salary + $d->bonus + $d->ta_allowances + $d->da_allowances + $d->commission;
        });
        $commissionsTotal = (float) LedgerEntry::where('account', 'Commission Expense')
            ->whereBetween('created_at', [$startOfYear, $endOfPeriod])
            ->sum('debit');
        $cogsEstimate = $this->estimateCogs($startOfYear, $endOfPeriod);

        $productionQty = ProductionRun::whereBetween('created_at', [$startOfYear, $endOfPeriod])
            ->where('qc_status', 'approved')
            ->sum('quantity');

        $activeAgents = Agent::where('is_active', true)->count();

        $netProfitEstimate = $grossRevenue - ($cogsEstimate + $commissionsTotal + $totalExpenses + $totalPayroll);

        return view('admin.reports.dashboard', [
            'yearLabel'          => $startOfYear->format('Y'),
            'grossRevenue'       => $grossRevenue,
            'withholdingTotal'   => $withholdingTotal,
            'totalCollections'   => $totalCollections,
            'outstanding'        => $outstanding,
            'cogsEstimate'       => $cogsEstimate,
            'commissionsTotal'   => $commissionsTotal,
            'totalExpenses'      => $totalExpenses,
            'totalPayroll'       => $totalPayroll,
            'netProfitEstimate'  => $netProfitEstimate,
            'productionQty'      => $productionQty,
            'activeAgents'       => $activeAgents,
        ]);
    }

    protected function estimateCogs(Carbon $from, Carbon $to): float
    {
        $invoiceItems = InvoiceItem::whereHas('invoice', function ($query) use ($from, $to) {
            $query
                ->whereDate('issued_at', '>=', $from->toDateString())
                ->whereDate('issued_at', '<=', $to->toDateString());
        })->get();

        $costByProduct = ProductionRun::whereNotNull('material_unit_cost')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($group) => (float) $group->avg('material_unit_cost'));

        $cogs = 0.0;

        foreach ($invoiceItems as $item) {
            if (! $item->product_id) {
                continue;
            }

            $unitCost = $costByProduct->get($item->product_id);

            if ($unitCost === null) {
                continue;
            }

            $cogs += $unitCost * (float) $item->quantity;
        }

        return round($cogs, 2);
    }
}
