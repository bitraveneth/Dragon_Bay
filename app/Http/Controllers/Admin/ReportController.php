<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\ProductionRun;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeeAllowance;
use App\Models\BillOfMaterial;
use App\Models\SalaryDistribution;
use App\Models\PurchaseBill;
use App\Models\Client;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function shipmentProfitability(Request $request)
    {
        [$from, $to, $rows] = $this->buildShipmentReport('profitability', $request);

        return view('admin.reports.shipment-profitability', compact('from', 'to', 'rows'));
    }

    public function weightUsage(Request $request)
    {
        [$from, $to, $rows] = $this->buildShipmentReport('weight', $request);

        return view('admin.reports.weight-usage', compact('from', 'to', 'rows'));
    }

    public function modePerformance(Request $request)
    {
        [$from, $to, $rows] = $this->buildShipmentReport('mode', $request);

        return view('admin.reports.mode-performance', compact('from', 'to', 'rows'));
    }

    public function exportShipmentReport(Request $request, string $report, string $format)
    {
        abort_unless(in_array($report, ['profitability', 'weight', 'mode'], true), 404);
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        [$from, $to, $rows] = $this->buildShipmentReport($report, $request);

        if ($format === 'pdf') {
            return Pdf::loadView('admin.reports.shipments-pdf', [
                'title' => $this->shipmentReportTitle($report),
                'report' => $report,
                'from' => $from,
                'to' => $to,
                'rows' => $rows,
            ])->download('shipment-report-' . $report . '-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf');
        }

        return $this->downloadShipmentReportCsv($report, $from, $to, $rows);
    }

    public function profitAndLoss(Request $request)
    {
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $entries = LedgerEntry::whereBetween('created_at', [$from, $to])->get();

        $sales = $entries->where('account', 'Sales Revenue')->sum('credit');
        $returns = $entries->where('account', 'Sales Returns')->sum('debit');
        $commissions = $entries->where('account', 'Commission Expense')->sum('debit');

        // Include simple period expenses recorded in the expenses module.
        $otherExpenses = $this->operatingExpensesTotal($from, $to);
        $payroll = SalaryDistribution::whereBetween('period_start', [$from, $to])
            ->get()
            ->sum(function (SalaryDistribution $distribution) {
                return (float) $distribution->base_salary
                    + (float) $distribution->bonus
                    + (float) $distribution->ta_allowances
                    + (float) $distribution->da_allowances
                    + (float) $distribution->commission;
            });

        $netSales = $sales - $returns;

        // --- Approximate COGS using production material cost snapshot ---
        $invoiceItems = InvoiceItem::with(['invoice', 'product'])
            ->whereHas('invoice', function ($q) use ($from, $to) {
                $q
                    ->whereDate('issued_at', '>=', $from->toDateString())
                    ->whereDate('issued_at', '<=', $to->toDateString());
            })
            ->get();

        // Average material_unit_cost per product from all runs where it is set
        $runsWithCost = ProductionRun::whereNotNull('material_unit_cost')->get();
        $costByProduct = $runsWithCost->groupBy('product_id')->map(
            fn ($group) => (float) $group->avg('material_unit_cost')
        );

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

        $grossProfit = $netSales - $cogs;
        $profit = $grossProfit - $commissions - $otherExpenses - $payroll;

        // Detailed ledger breakdown by account for the period
        $accountRows = $entries->groupBy('account')->map(function (Collection $rows, string $account) {
            $debit  = (float) $rows->sum('debit');
            $credit = (float) $rows->sum('credit');
            $net    = $credit - $debit; // income-style: positive = income, negative = expense

            return [
                'account' => $account,
                'debit'   => $debit,
                'credit'  => $credit,
                'net'     => $net,
            ];
        })->sortBy('account')->values();

        return view('admin.finance.pl', compact(
            'from',
            'to',
            'sales',
            'returns',
            'commissions',
            'otherExpenses',
            'payroll',
            'netSales',
            'cogs',
            'grossProfit',
            'profit',
            'accountRows'
        ));
    }

    public function vat(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')->startOfMonth()
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $outputEntries = LedgerEntry::where('account', 'VAT Payable')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $inputEntries = LedgerEntry::where('account', 'Input VAT')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $outputVat = (float) $outputEntries->sum('credit') - (float) $outputEntries->sum('debit');
        $inputVat = (float) $inputEntries->sum('debit') - (float) $inputEntries->sum('credit');
        $vatCollected = round($outputVat - $inputVat, 2);

        // Detailed per-invoice breakdown (output VAT)
        $invoices = Invoice::with(['order.agent', 'creditNotes'])
            ->whereDate('issued_at', '>=', $from->toDateString())
            ->whereDate('issued_at', '<=', $to->toDateString())
            ->orderBy('issued_at')
            ->get();

        $invoiceRows = $invoices->map(function (Invoice $invoice) use ($from, $to) {
            $taxable = $invoice->netSalesAfterCreditsInRange($from, $to);
            $vat = max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($from, $to));
            $rate = $taxable > 0 ? round(($vat / $taxable) * 100, 2) : null;

            return [
                'date'        => $invoice->issued_at,
                'number'      => $invoice->number ?? $invoice->id,
                'customer'    => $invoice->order?->agent?->name,
                'taxable'     => $taxable,
                'vat'         => $vat,
                'vat_rate'    => $rate,
            ];
        });

        $totals = [
            'taxable' => $invoiceRows->sum('taxable'),
            'vat'     => $invoiceRows->sum('vat'),
        ];

        $purchaseBills = PurchaseBill::with('supplier')
            ->whereDate('bill_date', '>=', $from->toDateString())
            ->whereDate('bill_date', '<=', $to->toDateString())
            ->orderBy('bill_date')
            ->get();

        $purchaseRows = $purchaseBills->map(function (PurchaseBill $bill) {
            $rate = (float) $bill->net_total > 0
                ? round(((float) $bill->vat_amount / (float) $bill->net_total) * 100, 2)
                : null;

            return [
                'date' => $bill->bill_date,
                'number' => $bill->number,
                'supplier' => $bill->supplier?->name,
                'taxable' => (float) $bill->net_total,
                'vat' => (float) $bill->vat_amount,
                'vat_rate' => $rate,
            ];
        });

        $purchaseTotals = [
            'taxable' => $purchaseRows->sum('taxable'),
            'vat' => $purchaseRows->sum('vat'),
        ];

        return view('admin.finance.vat', [
            'month'        => $month,
            'vatCollected' => $vatCollected,
            'outputVat'    => $outputVat,
            'inputVat'     => $inputVat,
            'from'         => $from,
            'to'           => $to,
            'invoiceRows'  => $invoiceRows,
            'totals'       => $totals,
            'purchaseRows' => $purchaseRows,
            'purchaseTotals' => $purchaseTotals,
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $endOfDay = $asOf->copy()->endOfDay();

        $entries = LedgerEntry::where('created_at', '<=', $endOfDay)->get();

        $balances = [];
        foreach ($entries as $entry) {
            $account = $entry->account;
            if (!isset($balances[$account])) {
                $balances[$account] = 0;
            }
            $balances[$account] += $entry->debit - $entry->credit;
        }

        $assetsAccounts = Account::where('type', 'asset')->pluck('name')->all() ?: ['Bank', 'Accounts Receivable', 'Input VAT'];
        $liabilityAccounts = Account::where('type', 'liability')->pluck('name')->all() ?: ['Accounts Payable', 'VAT Payable', 'Agent Advances', 'Commission Payable'];

        $assets = [];
        $liabilities = [];

        foreach ($balances as $account => $amount) {
            if (in_array($account, $assetsAccounts, true)) {
                $assets[$account] = $amount;
            } elseif (in_array($account, $liabilityAccounts, true)) {
                $liabilities[$account] = $amount * -1;
            }
        }

        $totalAssets = array_sum($assets);
        $totalLiabilities = array_sum($liabilities);
        $equity = $totalAssets - $totalLiabilities;

        return view('admin.finance.bs', compact('asOf', 'assets', 'liabilities', 'totalAssets', 'totalLiabilities', 'equity'));
    }

    public function cashflow(Request $request)
    {
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $bankAccounts = Account::where('type', 'asset')
            ->where(function ($query) {
                $query->where('name', 'like', '%Bank%')
                    ->orWhere('name', 'like', '%Cash%')
                    ->orWhere('code', 'like', '10%');
            })
            ->pluck('name')
            ->all();

        $entries = LedgerEntry::whereIn('account', $bankAccounts ?: ['Bank'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $cashIn = $entries->sum('debit');
        $cashOut = $entries->sum('credit');
        $net = $cashIn - $cashOut;

        return view('admin.finance.cashflow', compact('from', 'to', 'cashIn', 'cashOut', 'net'));
    }

    public function agentPerformance(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $invoices = Invoice::with(['order.agent', 'receipts', 'creditNotes', 'advanceApplications'])
            ->whereBetween('issued_at', [$from, $to])
            ->whereHas('order.agent')
            ->get();

        $byAgent = [];

        foreach ($invoices as $invoice) {
            $agent = $invoice->order?->agent;

            if (! $agent) {
                continue;
            }

            $agentId = $agent->id;

            if (! isset($byAgent[$agentId])) {
                $byAgent[$agentId] = [
                    'agent' => $agent,
                    'order_ids' => [],
                    'invoice_count' => 0,
                    'invoiced' => 0,
                    'credits' => 0,
                    'receipts' => 0,
                    'advances' => 0,
                ];
            }

            $bucket = &$byAgent[$agentId];

            $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
            $bucket['invoiced'] += $gross;
            $bucket['invoice_count']++;

            if ($invoice->order_id) {
                $bucket['order_ids'][$invoice->order_id] = true;
            }

            $bucket['credits'] += $invoice->creditNotesTotalInRange($from, $to);
            $bucket['advances'] += $invoice->advancesAppliedTotalInRange($from, $to);
            $bucket['receipts'] += $invoice->receiptsTotalInRange($from, $to);
        }

        $rows = collect($byAgent)->map(function (array $bucket) {
            $netSales = $bucket['invoiced'] - $bucket['credits'];
            $outstanding = max(0, $netSales - $bucket['receipts'] - $bucket['advances']);

            return [
                'agent' => $bucket['agent'],
                'order_count' => count($bucket['order_ids']),
                'invoice_count' => $bucket['invoice_count'],
                'invoiced' => $bucket['invoiced'],
                'credits' => $bucket['credits'],
                'net_sales' => $netSales,
                'receipts' => $bucket['receipts'],
                'advances' => $bucket['advances'],
                'outstanding' => $outstanding,
            ];
        })->sortByDesc('net_sales');

        return view('admin.finance.agent_performance', [
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
        ]);
    }

    public function productionSummary(Request $request)
    {
        [$from, $to] = $this->resolveDateRange(
            $request,
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $production = ProductionRun::with('product')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        // Preload active BOMs (with component standard_cost) for all products
        $productIds = $production->pluck('product_id')->unique()->filter()->all();

        $boms = collect();
        if (
            Schema::hasTable('bill_of_materials')
            && Schema::hasTable('bill_of_material_items')
            && ! empty($productIds)
        ) {
            $boms = BillOfMaterial::whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->with(['items.component'])
                ->orderByDesc('id')
                ->get()
                ->keyBy('product_id');
        }

        $byProduct = $production->groupBy('product_id')->map(function ($runs, $productId) use ($boms) {
            $product  = $runs->first()->product;
            $quantity = $runs->sum('quantity');

            $unitCost  = null;
            $totalCost = null;

            // Prefer persisted costing data if available
            $totalCostFromRuns = $runs->sum('material_total_cost');
            if ($quantity > 0 && $totalCostFromRuns > 0) {
                $totalCost = $totalCostFromRuns;
                $unitCost  = $totalCostFromRuns / (float) $quantity;
            } else {
                // Fallback: estimate from BOM + component standard_cost / BOM unit_cost
                $bom = $boms->get($productId);
                if ($bom && $bom->items->isNotEmpty()) {
                    if ($bom->material_unit_cost !== null && $bom->material_unit_cost > 0) {
                        $unitCost  = (float) $bom->material_unit_cost;
                        $totalCost = $unitCost * (float) $quantity;
                    } else {
                        $accumulator = 0.0;
                        foreach ($bom->items as $item) {
                            $component = $item->component;
                            $baseCost = null;
                            if ($item->unit_cost !== null) {
                                $baseCost = (float) $item->unit_cost;
                            } elseif ($component && $component->standard_cost !== null) {
                                $baseCost = (float) $component->standard_cost;
                            }
                            if ($baseCost !== null) {
                                $accumulator += $baseCost * (float) $item->quantity;
                            }
                        }
                        if ($accumulator > 0) {
                            $unitCost  = $accumulator;
                            $totalCost = $unitCost * (float) $quantity;
                        }
                    }
                }
            }

            return [
                'product'    => $product,
                'runs'       => $runs->count(),
                'quantity'   => $quantity,
                'unit_cost'  => $unitCost,
                'total_cost' => $totalCost,
            ];
        });

        $salesInvoices = Invoice::with('creditNotes')
            ->whereBetween('issued_at', [$from, $to])
            ->get();
        $salesTotal = $salesInvoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        });

        $expensesTotal = $this->operatingExpensesTotal($from, $to);

        $materialCostTotal = $byProduct->sum(fn (array $row) => (float) ($row['total_cost'] ?? 0));

        return view('admin.finance.production_summary', [
            'from' => $from,
            'to' => $to,
            'byProduct' => $byProduct,
            'salesTotal' => $salesTotal,
            'expensesTotal' => $expensesTotal,
            'materialCostTotal' => $materialCostTotal,
            'approxProfit' => $salesTotal - $materialCostTotal - $expensesTotal,
        ]);
    }

    public function payrollSummary(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $distributions = SalaryDistribution::with('employee')
            ->whereBetween('period_start', [$from, $to])
            ->orderBy('employee_id')
            ->orderBy('period_start')
            ->get()
            ->groupBy('employee_id');

        $rows = $distributions->map(function ($employeeDistributions) {
            $employee = $employeeDistributions->first()->employee;
            $baseSalary = $employeeDistributions->sum('base_salary');
            $totalTa = $employeeDistributions->sum('ta_allowances');
            $totalDa = $employeeDistributions->sum('da_allowances');
            $totalBonus = $employeeDistributions->sum('bonus');
            $commission = $employeeDistributions->sum('commission');
            $grandTotal = $baseSalary + $totalTa + $totalDa + $totalBonus + $commission;

            return [
                'employee' => $employee,
                'contracts' => collect(),
                'distributions' => $employeeDistributions,
                'base_salary' => $baseSalary,
                'base_ta' => 0,
                'base_da' => 0,
                'base_bonus' => 0,
                'ta_allowances' => $totalTa,
                'da_allowances' => $totalDa,
                'bonus_allowances' => $totalBonus,
                'commission' => $commission,
                'total_salary' => $baseSalary,
                'total_ta' => $totalTa,
                'total_da' => $totalDa,
                'total_bonus' => $totalBonus + $commission,
                'grand_total' => $grandTotal,
            ];
        })->filter(function (array $row) {
            return $row['grand_total'] > 0;
        })->values();

        $totals = [
            'salary' => $rows->sum('total_salary'),
            'ta' => $rows->sum('total_ta'),
            'da' => $rows->sum('total_da'),
            'bonus' => $rows->sum('total_bonus'),
            'grand' => $rows->sum('grand_total'),
        ];

        return view('admin.finance.payroll_summary', compact('from', 'to', 'rows', 'totals'));
    }

    protected function buildShipmentReport(string $report, Request $request): array
    {
        [$from, $to] = $this->resolveDateRange($request, Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());

        if ($report === 'profitability') {
            $shipments = Shipment::with(['client', 'agent', 'packages', 'expenses', 'invoices'])
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $rows = $shipments->map(function (Shipment $shipment) {
                $revenue = (float) ($shipment->final_price ?? $shipment->estimated_price);
                $cost = (float) $shipment->expenses
                    ->where('status', 'approved')
                    ->sum(fn ($expense) => $expense->effective_amount);

                return [
                    'shipment' => $shipment,
                    'client' => $shipment->client?->name ?? $shipment->agent?->name,
                    'mode' => $shipment->mode,
                    'chargeable_weight' => $shipment->packages->sum('chargeable_weight_kg'),
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $revenue - $cost,
                ];
            });

            return [$from, $to, $rows];
        }

        if ($report === 'weight') {
            $shipments = Shipment::with(['client', 'agent', 'packages'])
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $rows = $shipments->map(function (Shipment $shipment) {
                return [
                    'shipment' => $shipment,
                    'client' => $shipment->client?->name ?? $shipment->agent?->name,
                    'actual_weight' => $shipment->packages->sum('actual_weight_kg'),
                    'cbm' => $shipment->packages->sum('cbm'),
                    'volumetric_weight' => $shipment->packages->sum('volumetric_weight_kg'),
                    'chargeable_weight' => $shipment->packages->sum('chargeable_weight_kg'),
                ];
            });

            return [$from, $to, $rows];
        }

        $shipments = Shipment::with(['packages', 'expenses'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $rows = $shipments
            ->groupBy('mode')
            ->map(function (Collection $group, string $mode) {
                $revenue = (float) $group->sum(fn (Shipment $shipment) => (float) ($shipment->final_price ?? $shipment->estimated_price));
                $cost = (float) $group->sum(function (Shipment $shipment) {
                    return $shipment->expenses
                        ->where('status', 'approved')
                        ->sum(fn ($expense) => $expense->effective_amount);
                });

                return [
                    'mode' => $mode,
                    'shipments' => $group->count(),
                    'chargeable_weight' => $group->sum(fn (Shipment $shipment) => $shipment->packages->sum('chargeable_weight_kg')),
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $revenue - $cost,
                ];
            })
            ->values();

        return [$from, $to, $rows];
    }

    protected function shipmentReportTitle(string $report): string
    {
        return match ($report) {
            'profitability' => 'Shipment Profitability',
            'weight' => 'KG vs CBM Usage',
            default => 'Air vs Sea Performance',
        };
    }

    protected function downloadShipmentReportCsv(string $report, Carbon $from, Carbon $to, Collection $rows): StreamedResponse
    {
        $filename = 'shipment-report-' . $report . '-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($report, $rows) {
            $handle = fopen('php://output', 'w');

            if ($report === 'mode') {
                fputcsv($handle, ['Mode', 'Shipments', 'Chargeable KG', 'Revenue', 'Cost', 'Profit']);
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        strtoupper(str_replace('_', ' ', $row['mode'])),
                        $row['shipments'],
                        number_format($row['chargeable_weight'], 3, '.', ''),
                        number_format($row['revenue'], 2, '.', ''),
                        number_format($row['cost'], 2, '.', ''),
                        number_format($row['profit'], 2, '.', ''),
                    ]);
                }
            } elseif ($report === 'weight') {
                fputcsv($handle, ['Shipment', 'Client', 'Actual KG', 'CBM', 'Vol KG', 'Charge KG']);
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row['shipment']->shipment_no,
                        $row['client'] ?? '',
                        number_format($row['actual_weight'], 3, '.', ''),
                        number_format($row['cbm'], 4, '.', ''),
                        number_format($row['volumetric_weight'], 3, '.', ''),
                        number_format($row['chargeable_weight'], 3, '.', ''),
                    ]);
                }
            } else {
                fputcsv($handle, ['Shipment', 'Client', 'Mode', 'Chargeable KG', 'Revenue', 'Cost', 'Profit']);
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row['shipment']->shipment_no,
                        $row['client'] ?? '',
                        strtoupper(str_replace('_', ' ', $row['mode'])),
                        number_format($row['chargeable_weight'], 3, '.', ''),
                        number_format($row['revenue'], 2, '.', ''),
                        number_format($row['cost'], 2, '.', ''),
                        number_format($row['profit'], 2, '.', ''),
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function resolveDateRange(Request $request, Carbon $defaultFrom, Carbon $defaultTo): array
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : $defaultFrom->copy()->startOfDay();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : $defaultTo->copy()->endOfDay();

        return [$from, $to];
    }

    protected function operatingExpensesTotal(Carbon $from, Carbon $to): float
    {
        $expenses = (float) Expense::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [Expense::STATUS_RECORDED, Expense::STATUS_REVIEWED, 'paid', 'overdue'])
            ->sum('amount');

        $giftExpenses = (float) CustomerGift::whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->whereIn('status', [CustomerGift::STATUS_GIVEN, 'delivered'])
            ->sum('amount');

        $campaignExpenses = (float) Campaign::where(function ($query) use ($from, $to) {
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

        return $expenses + $giftExpenses + $campaignExpenses;
    }

    public function clientOutstanding(Request $request)
    {
        $agentId = $request->query('agent_id');

        $clients = Client::with('agent')
            ->when($agentId, fn ($q) => $q->where('agent_id', $agentId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $agents = \App\Models\Agent::orderBy('name')->get();

        $rows = $clients->map(function (Client $client) {
            $invoices = Invoice::whereHas('order', fn ($q) => $q->where('client_id', $client->id))
                ->orWhereHas('shipment', fn ($q) => $q->where('client_id', $client->id))
                ->with(['receipts', 'creditNotes', 'advanceApplications'])
                ->where(function ($q) {
                    $q->whereNull('invoice_type')->orWhere('invoice_type', '!=', 'proforma');
                })
                ->get();

            $totalInvoiced  = $invoices->sum(fn (Invoice $i) => (float) ($i->cash_total ?? 0));
            $totalPaid      = $invoices->sum(fn (Invoice $i) => (float) $i->receipts->sum('amount'));
            $totalOutstanding = $invoices->sum(fn (Invoice $i) => (float) $i->outstanding);
            $overdueCount   = $invoices->filter(fn (Invoice $i) => (float) $i->outstanding > 0)->count();

            return [
                'client'         => $client,
                'total_invoiced' => $totalInvoiced,
                'total_paid'     => $totalPaid,
                'outstanding'    => $totalOutstanding,
                'overdue_count'  => $overdueCount,
                'credit_limit'   => (float) $client->credit_limit,
                'credit_used_pct'=> $client->credit_limit > 0
                    ? min(round(($totalOutstanding / $client->credit_limit) * 100, 1), 999)
                    : null,
            ];
        })->filter(fn ($row) => $row['total_invoiced'] > 0 || $request->boolean('show_all'));

        $grandOutstanding = $rows->sum('outstanding');

        return view('admin.reports.client-outstanding', compact('rows', 'agents', 'agentId', 'grandOutstanding'));
    }
}
