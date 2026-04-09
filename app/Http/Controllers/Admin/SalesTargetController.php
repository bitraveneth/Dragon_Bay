<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\SalesTarget;
use App\Models\VisitPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SalesTargetController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')->startOfMonth()
            : Carbon::now()->startOfMonth();

        $targets = SalesTarget::with(['employee', 'agent'])
            ->whereDate('period_start', '<=', $month->copy()->endOfMonth()->toDateString())
            ->whereDate('period_end', '>=', $month->copy()->startOfMonth()->toDateString())
            ->orderByDesc('period_start')
            ->paginate(15)
            ->through(function (SalesTarget $target) {
                $achieved = $this->achievedValue($target);

                return [
                    'target' => $target,
                    'achieved' => $achieved,
                    'remaining' => max((float) $target->target_value - $achieved, 0),
                    'progress' => (float) $target->target_value > 0
                        ? min(100, round(($achieved / (float) $target->target_value) * 100, 2))
                        : 0.0,
                ];
            });

        return view('admin.sales-targets.index', compact('targets', 'month'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();

        return view('admin.sales-targets.create', compact('employees', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        SalesTarget::create($data);

        return redirect()->route('admin.sales-targets.index')->with('status', 'Sales target saved.');
    }

    public function edit(SalesTarget $salesTarget)
    {
        $employees = Employee::orderBy('name')->get();
        $agents = Agent::orderBy('name')->get();

        return view('admin.sales-targets.edit', compact('salesTarget', 'employees', 'agents'));
    }

    public function update(Request $request, SalesTarget $salesTarget)
    {
        $data = $this->validated($request, $salesTarget);

        $salesTarget->update($data);

        return redirect()->route('admin.sales-targets.index')->with('status', 'Sales target updated.');
    }

    protected function validated(Request $request, ?SalesTarget $salesTarget = null): array
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable|exists:employees,id',
            'agent_id' => 'nullable|exists:agents,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'target_value' => 'required|numeric|min:0.01',
        ]);

        $validator->after(function ($validator) use ($request, $salesTarget) {
            $hasEmployee = filled($request->input('employee_id'));
            $hasAgent = filled($request->input('agent_id'));

            if ($hasEmployee === $hasAgent) {
                $validator->errors()->add('employee_id', 'Select exactly one target owner: either an employee or an agent.');
            }

            $query = SalesTarget::query()
                ->whereDate('period_start', '<=', $request->input('period_end'))
                ->whereDate('period_end', '>=', $request->input('period_start'));

            if ($hasEmployee) {
                $query->where('employee_id', $request->input('employee_id'))->whereNull('agent_id');
            }

            if ($hasAgent) {
                $query->where('agent_id', $request->input('agent_id'))->whereNull('employee_id');
            }

            if ($salesTarget) {
                $query->whereKeyNot($salesTarget->id);
            }

            if ($query->exists()) {
                $validator->errors()->add('target_value', 'A target already exists for this owner in an overlapping period.');
            }
        });

        return $validator->validate();
    }

    protected function achievedValue(SalesTarget $target): float
    {
        $from = $target->period_start->toDateString();
        $to = $target->period_end->toDateString();

        $query = Invoice::query()
            ->with('creditNotes')
            ->whereDate('issued_at', '>=', $from)
            ->whereDate('issued_at', '<=', $to)
            ->whereHas('order.agent');

        if ($target->agent_id) {
            return $this->sumNetSalesAfterCredits($query
                ->whereHas('order', function ($orderQuery) use ($target) {
                    $orderQuery->where('agent_id', $target->agent_id);
                })
                ->get(), $from, $to);
        }

        $employee = $target->employee;
        if (! $employee) {
            return 0.0;
        }

        if (Schema::hasTable('visit_plans')) {
            $plans = VisitPlan::query()
                ->where('employee_id', $employee->id)
                ->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to)
                ->get(['agent_id', 'status']);

            if ($plans->isNotEmpty()) {
                $agentIds = $plans
                ->whereIn('status', ['visited', 'completed'])
                ->pluck('agent_id')
                ->filter()
                ->unique()
                ->values();

                if ($agentIds->isNotEmpty()) {
                    return $this->sumNetSalesAfterCredits($query
                        ->whereHas('order', function ($orderQuery) use ($agentIds) {
                            $orderQuery->whereIn('agent_id', $agentIds);
                        })
                        ->get(), $from, $to);
                }

                return 0.0;
            }
        }

        if ($employee->work_zone) {
            return $this->sumNetSalesAfterCredits($query
                ->whereHas('order.agent', function ($agentQuery) use ($employee) {
                    $agentQuery->where('zone', $employee->work_zone);
                })
                ->get(), $from, $to);
        }

        return 0.0;
    }

    protected function sumNetSalesAfterCredits($invoices, string $from, string $to): float
    {
        return round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        }), 2);
    }
}
