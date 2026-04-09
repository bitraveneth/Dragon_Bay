<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Http\Request;

class EmployeeLeaveController extends Controller
{
    public function all()
    {
        $leaves = EmployeeLeave::with('employee')
            ->orderByDesc('start_date')
            ->paginate(20);

        return view('admin.employees.leaves.all', compact('leaves'));
    }

    public function index(Employee $employee)
    {
        $leaves = $employee->leaves()->orderByDesc('start_date')->paginate(15);

        return view('admin.employees.leaves.index', compact('employee', 'leaves'));
    }

    public function create(Employee $employee)
    {
        return view('admin.employees.leaves.create', [
            'employee' => $employee,
            'leave' => new EmployeeLeave(),
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $this->validated($request);

        $employee->leaves()->create($data);

        return redirect()
            ->route('admin.employees.leaves.index', $employee)
            ->with('status', 'Leave request submitted.');
    }

    public function edit(Employee $employee, EmployeeLeave $leave)
    {
        if ($leave->employee_id !== $employee->id) {
            abort(404);
        }

        return view('admin.employees.leaves.edit', compact('employee', 'leave'));
    }

    public function update(Request $request, Employee $employee, EmployeeLeave $leave)
    {
        if ($leave->employee_id !== $employee->id) {
            abort(404);
        }

        $data = $this->validated($request);

        // If status changed to approved/rejected, stamp approver
        if (in_array($data['status'], ['approved', 'rejected'], true)) {
            $data['approved_by'] = auth()->user()->name ?? $leave->approved_by;
            $data['approved_at'] = now();
        }

        $leave->update($data);

        return redirect()
            ->route('admin.employees.leaves.index', $employee)
            ->with('status', 'Leave request updated.');
    }

    public function destroy(Employee $employee, EmployeeLeave $leave)
    {
        if ($leave->employee_id !== $employee->id) {
            abort(404);
        }

        return redirect()
            ->route('admin.employees.leaves.index', $employee)
            ->withErrors([
                'leave' => 'Leave history cannot be deleted. Update the request status instead.',
            ]);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string|max:50',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        return $data;
    }

    public function createGlobal()
    {
        $employees = Employee::orderBy('name')->get();

        return view('admin.employees.leaves.create_global', [
            'employees' => $employees,
            'leave' => new EmployeeLeave(),
        ]);
    }

    public function storeGlobal(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|string|max:50',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $payload = collect($data)->except('employee_id')->all();

        $employee->leaves()->create($payload);

        return redirect()
            ->route('admin.leaves.index')
            ->with('status', 'Leave request submitted.');
    }
}
