<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Http\Request;

class EmployeeContractController extends Controller
{
    private const VALID_STATUSES = [
        EmployeeContract::STATUS_ACTIVE,
        EmployeeContract::STATUS_ON_HOLD,
        EmployeeContract::STATUS_ENDED,
    ];

    public function all()
    {
        $contracts = EmployeeContract::with('employee')
            ->orderByDesc('start_date')
            ->paginate(20);

        return view('admin.employees.contracts.all', compact('contracts'));
    }

    public function createGlobal()
    {
        $employees = Employee::orderBy('name')->get();

        $workingSchedules = EmployeeContract::select('working_schedule')
            ->whereNotNull('working_schedule')
            ->distinct()
            ->orderBy('working_schedule')
            ->pluck('working_schedule');

        return view('admin.employees.contracts.create_global', [
            'employees' => $employees,
            'contract' => new EmployeeContract(),
            'workingSchedules' => $workingSchedules,
        ]);
    }

    public function storeGlobal(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'reference' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'working_schedule' => 'nullable|string|max:255',
            'salary_amount' => 'nullable|numeric|min:0',
            'travel_allowance' => 'nullable|numeric|min:0',
            'dearness_allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $employee->contracts()->create(collect($data)->except('employee_id')->all());

        return redirect()
            ->route('admin.contracts.index')
            ->with('status', 'Contract created.');
    }

    public function index(Employee $employee)
    {
        $contracts = $employee->contracts()->orderByDesc('start_date')->paginate(12);

        return view('admin.employees.contracts.index', compact('employee', 'contracts'));
    }

    public function create(Employee $employee)
    {
        $workingSchedules = EmployeeContract::select('working_schedule')
            ->whereNotNull('working_schedule')
            ->distinct()
            ->orderBy('working_schedule')
            ->pluck('working_schedule');

        return view('admin.employees.contracts.create', [
            'employee' => $employee,
            'contract' => new EmployeeContract(),
            'workingSchedules' => $workingSchedules,
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $this->validated($request);

        $employee->contracts()->create($data);

        return redirect()
            ->route('admin.employees.contracts.index', $employee)
            ->with('status', 'Contract created.');
    }

    public function edit(Employee $employee, EmployeeContract $contract)
    {
        if ($contract->employee_id !== $employee->id) {
            abort(404);
        }

        $workingSchedules = EmployeeContract::select('working_schedule')
            ->whereNotNull('working_schedule')
            ->distinct()
            ->orderBy('working_schedule')
            ->pluck('working_schedule');

        return view('admin.employees.contracts.edit', compact('employee', 'contract', 'workingSchedules'));
    }

    public function update(Request $request, Employee $employee, EmployeeContract $contract)
    {
        if ($contract->employee_id !== $employee->id) {
            abort(404);
        }

        $data = $this->validated($request);

        $contract->update($data);

        return redirect()
            ->route('admin.employees.contracts.index', $employee)
            ->with('status', 'Contract updated.');
    }

    public function destroy(Employee $employee, EmployeeContract $contract)
    {
        if ($contract->employee_id !== $employee->id) {
            abort(404);
        }

        return redirect()
            ->route('admin.employees.contracts.index', $employee)
            ->withErrors([
                'contract' => 'Employee contract history cannot be deleted. End or correct the contract instead.',
            ]);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'reference' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'working_schedule' => 'nullable|string|max:255',
            'salary_amount' => 'nullable|numeric|min:0',
            'travel_allowance' => 'nullable|numeric|min:0',
            'dearness_allowance' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
        ]);

        return $data;
    }
}
