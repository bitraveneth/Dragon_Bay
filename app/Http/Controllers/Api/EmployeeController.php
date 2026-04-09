<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLocationLog;
use App\Models\EmployeeContract;
use App\Models\EmployeeLeaveBalance;
use App\Models\SalesTarget;
use App\Models\VisitPlan;
use App\Models\Agent;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected function requireEmployee(Request $request)
    {
        $user = $request->user();

        if (! $user->employee) {
            abort(403, 'Employee access required.');
        }

        return $user->employee;
    }

    public function profile(Request $request)
    {
        $employee = $this->requireEmployee($request);
        $employee->load(['contracts', 'allowances']);

        return response()->json([
            'employee' => $employee,
        ]);
    }

    public function contracts(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $contracts = EmployeeContract::where('employee_id', $employee->id)
            ->orderByDesc('start_date')
            ->paginate(20);

        return response()->json($contracts);
    }

    public function allowances(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $allowances = EmployeeAllowance::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->paginate(20);

        return response()->json($allowances);
    }

    public function storeAllowance(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $data = $request->validate([
            'date' => 'required|date',
            'type' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $data['status'] = 'submitted';

        $allowance = EmployeeAllowance::create($data + ['employee_id' => $employee->id]);

        return response()->json($allowance, 201);
    }

    public function leaves(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $leaves = EmployeeLeave::where('employee_id', $employee->id)
            ->orderByDesc('start_date')
            ->paginate(20);

        return response()->json($leaves);
    }

    public function storeLeave(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string|max:50',
            'reason' => 'nullable|string|max:255',
        ]);

        $data['status'] = 'pending';

        $leave = EmployeeLeave::create($data + ['employee_id' => $employee->id]);

        return response()->json($leave, 201);
    }

    public function locationLogs(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $limit = (int) $request->query('limit', 0);

        $query = EmployeeLocationLog::where('employee_id', $employee->id)
            ->orderByDesc('logged_at');

        if ($limit > 0) {
            $logs = $query->limit($limit)->get();

            return response()->json([
                'data' => $logs,
            ]);
        }

        $logs = $query->paginate(20);

        return response()->json($logs);
    }

    public function storeLocation(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $data = $request->validate([
            'logged_at' => 'required|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_label' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $log = EmployeeLocationLog::create($data + ['employee_id' => $employee->id]);

        return response()->json($log, 201);
    }

    public function dashboard(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $today = now()->toDateString();

        $todayLogs = EmployeeLocationLog::where('employee_id', $employee->id)
            ->whereDate('logged_at', $today)
            ->orderBy('logged_at')
            ->get();

        $punchIn = $todayLogs->first(function (EmployeeLocationLog $log) {
            return $log->event_type === 'punch_in';
        });

        $punchOut = $todayLogs->last(function (EmployeeLocationLog $log) {
            return $log->event_type === 'punch_out';
        });

        if (! $punchIn && ! $punchOut) {
            $todayAttendance = 'Not punched in yet';
        } elseif ($punchIn && ! $punchOut) {
            $todayAttendance = 'In at ' . $punchIn->logged_at->format('H:i');
        } elseif ($punchIn && $punchOut) {
            $todayAttendance = 'In at ' . $punchIn->logged_at->format('H:i') .
                ' · Out at ' . $punchOut->logged_at->format('H:i');
        } else {
            $todayAttendance = 'Out at ' . $punchOut->logged_at->format('H:i');
        }

        $ta = EmployeeAllowance::where('employee_id', $employee->id)
            ->where('type', 'TA')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $da = EmployeeAllowance::where('employee_id', $employee->id)
            ->where('type', 'DA')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $bonus = EmployeeAllowance::where('employee_id', $employee->id)
            ->where('type', 'BONUS')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $openLeaves = EmployeeLeave::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        return response()->json([
            'today_attendance' => $todayAttendance,
            'ta_this_month' => $ta,
            'da_this_month' => $da,
            'bonus_this_month' => $bonus,
            'open_leaves' => $openLeaves,
        ]);
    }

    public function punchIn(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $data = $request->validate([
            'logged_at' => 'nullable|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_label' => 'nullable|string|max:255',
        ]);

        $data['logged_at'] = $data['logged_at'] ?? now();

        $log = EmployeeLocationLog::create($data + [
            'employee_id' => $employee->id,
            'event_type' => 'punch_in',
            'source' => 'mobile',
        ]);

        return response()->json($log, 201);
    }

    public function punchOut(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $data = $request->validate([
            'logged_at' => 'nullable|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_label' => 'nullable|string|max:255',
        ]);

        $data['logged_at'] = $data['logged_at'] ?? now();

        $log = EmployeeLocationLog::create($data + [
            'employee_id' => $employee->id,
            'event_type' => 'punch_out',
            'source' => 'mobile',
        ]);

        return response()->json($log, 201);
    }

    public function agents(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $query = Agent::query();

        if ($employee->work_zone) {
            $query->where('zone', $employee->work_zone);
        }

        $agents = $query->orderBy('name')->paginate(50);

        return response()->json($agents);
    }

    public function visitPlans(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $date = $request->query('date');

        $query = VisitPlan::with('agent')
            ->where('employee_id', $employee->id);

        if ($date) {
            $query->whereDate('date', $date);
        }

        $plans = $query->orderBy('date')->get();

        $data = $plans->map(function (VisitPlan $plan) {
            $agent = $plan->agent;

            return [
                'id' => $plan->id,
                'date' => $plan->date?->toDateString(),
                'status' => $plan->status,
                'code' => $agent->location_code ?? $agent->special_code ?? null,
                'name' => $agent->name ?? null,
                'slot' => $plan->title,
                'notes' => $plan->notes,
            ];
        })->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function storeVisitPlan(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $data = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $plan = VisitPlan::create($data + [
            'employee_id' => $employee->id,
            'status' => 'planned',
        ]);

        return response()->json($plan, 201);
    }

    public function completeVisitPlan(Request $request, VisitPlan $plan)
    {
        $employee = $this->requireEmployee($request);

        if ($plan->employee_id !== $employee->id) {
            abort(404);
        }

        $plan->status = 'visited';
        $plan->save();

        return response()->json($plan);
    }

    public function targets(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $month = $request->query('month');
        $period = $month ? now()->parse($month . '-01') : now();

        $from = $period->copy()->startOfMonth();
        $to = $period->copy()->endOfMonth();

        $target = SalesTarget::where('employee_id', $employee->id)
            ->where('period_start', $from->toDateString())
            ->where('period_end', $to->toDateString())
            ->first();

        $targetValue = $target?->target_value ?? 0;

        // For now we do not tie employee directly to orders; treat achievement as 0
        $achievement = 0;
        $remaining = max($targetValue - $achievement, 0);

        return response()->json([
            'period_start' => $from->toDateString(),
            'period_end' => $to->toDateString(),
            'target' => $targetValue,
            'achieved' => $achievement,
            'remaining' => $remaining,
        ]);
    }

    public function leaveSummary(Request $request)
    {
        $employee = $this->requireEmployee($request);

        $year = (int) ($request->query('year') ?? now()->year);

        $balances = EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->get()
            ->keyBy('type');

        $leaves = EmployeeLeave::where('employee_id', $employee->id)
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->get();

        $usedByType = [];
        foreach ($leaves as $leave) {
            $days = $leave->start_date->diffInDays($leave->end_date) + 1;
            $type = $leave->type;
            $usedByType[$type] = ($usedByType[$type] ?? 0) + $days;
        }

        $result = [];

        foreach ($balances as $type => $balance) {
            $used = $usedByType[$type] ?? 0;

            $result[$type] = [
                'total' => $balance->entitled_days,
                'used' => $used,
                'left' => max($balance->entitled_days - $used, 0),
            ];
        }

        return response()->json($result);
    }
}
