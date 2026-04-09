<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeAllowanceController extends Controller
{
    private const VALID_STATUSES = [
        EmployeeAllowance::STATUS_SUBMITTED,
        EmployeeAllowance::STATUS_APPROVED,
        EmployeeAllowance::STATUS_PAID,
        EmployeeAllowance::STATUS_REJECTED,
    ];

    public function all()
    {
        $allowances = EmployeeAllowance::with('employee')
            ->orderByDesc('date')
            ->paginate(20);

        return view('admin.employees.allowances.all', compact('allowances'));
    }

    public function createGlobal()
    {
        $employees = Employee::orderBy('name')->get();

        return view('admin.employees.allowances.create_global', [
            'employees' => $employees,
            'allowance' => new EmployeeAllowance(),
        ]);
    }

    public function storeGlobal(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'type' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
            'attachment' => 'nullable|file|max:5120',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $payload = collect($data)->except(['employee_id', 'attachment'])->all();

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')->store('employees/allowances', 'public');
        }

        $employee->allowances()->create($payload);

        return redirect()
            ->route('admin.allowances.index')
            ->with('status', 'Allowance/TA slip submitted.');
    }

    public function index(Employee $employee)
    {
        $allowances = $employee->allowances()->orderByDesc('date')->paginate(15);

        return view('admin.employees.allowances.index', compact('employee', 'allowances'));
    }

    public function create(Employee $employee)
    {
        return view('admin.employees.allowances.create', [
            'employee' => $employee,
            'allowance' => new EmployeeAllowance(),
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('employees/allowances', 'public');
        }

        $employee->allowances()->create($data);

        return redirect()
            ->route('admin.employees.allowances.index', $employee)
            ->with('status', 'Allowance/TA slip submitted.');
    }

    public function edit(Employee $employee, EmployeeAllowance $allowance)
    {
        if ($allowance->employee_id !== $employee->id) {
            abort(404);
        }

        return view('admin.employees.allowances.edit', compact('employee', 'allowance'));
    }

    public function update(Request $request, Employee $employee, EmployeeAllowance $allowance)
    {
        if ($allowance->employee_id !== $employee->id) {
            abort(404);
        }

        $data = $this->validated($request);

        if ($request->hasFile('attachment')) {
            if ($allowance->attachment_path) {
                Storage::disk('public')->delete($allowance->attachment_path);
            }

            $data['attachment_path'] = $request->file('attachment')->store('employees/allowances', 'public');
        }

        $allowance->update($data);

        return redirect()
            ->route('admin.employees.allowances.index', $employee)
            ->with('status', 'Allowance/TA slip updated.');
    }

    public function destroy(Employee $employee, EmployeeAllowance $allowance)
    {
        if ($allowance->employee_id !== $employee->id) {
            abort(404);
        }

        return redirect()
            ->route('admin.employees.allowances.index', $employee)
            ->withErrors([
                'allowance' => 'Allowance and TA history cannot be deleted. Update the status or correct the record instead.',
            ]);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'date' => 'required|date',
            'type' => 'required|string|max:50',
            'reference' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
            'attachment' => 'nullable|file|max:5120',
        ]);

        return $data;
    }
}
