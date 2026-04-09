<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Support\CommissionCalculator;

class CommissionReportController extends Controller
{
    public function rules()
    {
        $agents = Agent::with('commissions')->orderBy('name')->get();

        return view('admin.agents.commission_rules', compact('agents'));
    }

    public function index(Request $request)
    {
        [$month, $byAgent] = $this->buildCommissionSummary($request);

        return view('admin.agents.commissions', [
            'rows' => $byAgent,
            'month' => $month,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$month, $byAgent] = $this->buildCommissionSummary($request);

        $filename = 'commission-summary-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($byAgent) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Agent ID', 'Agent Name', 'Sales', 'Commission', 'Effective Rate']);

            foreach ($byAgent as $agentId => $row) {
                $sales = (float) ($row['sales'] ?? 0);
                $commission = (float) ($row['commission'] ?? 0);
                $rate = $sales > 0 ? round(($commission / $sales) * 100, 2) : 0;

                fputcsv($handle, [
                    $agentId,
                    $row['agent']->name ?? 'Unknown',
                    number_format($sales, 2, '.', ''),
                    number_format($commission, 2, '.', ''),
                    number_format($rate, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function buildCommissionSummary(Request $request): array
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();
        $calculator = app(CommissionCalculator::class);

        $agents = Agent::query()
            ->where(function ($query) use ($from, $to) {
                $query->whereHas('orders', function ($orderQuery) use ($from, $to) {
                    $orderQuery->where('status', 'delivered')
                        ->whereHas('invoice', function ($invoiceQuery) use ($from, $to) {
                            $invoiceQuery
                                ->whereDate('issued_at', '>=', $from->toDateString())
                                ->whereDate('issued_at', '<=', $to->toDateString());
                        });
                })->orWhereHas('commissions', function ($commissionQuery) {
                    $commissionQuery->where('frequency', 'monthly');
                });
            })
            ->orderBy('name')
            ->get();

        $byAgent = [];

        foreach ($agents as $agent) {
            $summary = $calculator->buildMonthlySummaryForAgent($agent, $from, $to);

            if ($summary['sales'] <= 0 && $summary['commission'] <= 0) {
                continue;
            }

            $byAgent[$agent->id] = [
                'agent' => $agent,
                'sales' => $summary['sales'],
                'commission' => $summary['commission'],
            ];
        }

        uasort($byAgent, function (array $left, array $right) {
            return ($right['commission'] <=> $left['commission'])
                ?: (($right['sales'] ?? 0) <=> ($left['sales'] ?? 0));
        });

        return [$month, $byAgent];
    }
}
