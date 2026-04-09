<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeEquipment;
use Illuminate\Http\Request;

class EmployeeEquipmentController extends Controller
{
    private const VALID_STATUSES = [
        EmployeeEquipment::STATUS_ACTIVE,
        EmployeeEquipment::STATUS_RETURNED,
        EmployeeEquipment::STATUS_LOST,
    ];

    public function all()
    {
        $equipment = EmployeeEquipment::with('employee')
            ->orderByDesc('effective_date')
            ->paginate(20);

        return view('admin.employees.equipment.all', compact('equipment'));
    }

    public function createGlobal()
    {
        $employees = Employee::orderBy('name')->get();

        return view('admin.employees.equipment.create_global', [
            'employees' => $employees,
            'item' => new EmployeeEquipment(),
        ]);
    }

    public function storeGlobal(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'effective_date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'device_identifier' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $payload = collect($data)->except('employee_id')->all();

        $employee->equipment()->create($payload);

        return redirect()
            ->route('admin.equipment.index')
            ->with('status', 'Equipment assigned.');
    }

    public function index(Employee $employee)
    {
        $equipment = $employee->equipment()->orderByDesc('effective_date')->paginate(15);

        return view('admin.employees.equipment.index', compact('employee', 'equipment'));
    }

    public function create(Employee $employee)
    {
        return view('admin.employees.equipment.create', [
            'employee' => $employee,
            'item' => new EmployeeEquipment(),
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $this->validated($request);

        $employee->equipment()->create($data);

        return redirect()
            ->route('admin.employees.equipment.index', $employee)
            ->with('status', 'Equipment assigned.');
    }

    public function edit(Employee $employee, EmployeeEquipment $equipment)
    {
        if ($equipment->employee_id !== $employee->id) {
            abort(404);
        }

        return view('admin.employees.equipment.edit', [
            'employee' => $employee,
            'item' => $equipment,
        ]);
    }

    public function update(Request $request, Employee $employee, EmployeeEquipment $equipment)
    {
        if ($equipment->employee_id !== $employee->id) {
            abort(404);
        }

        $data = $this->validated($request);

        $equipment->update($data);

        return redirect()
            ->route('admin.employees.equipment.index', $employee)
            ->with('status', 'Equipment updated.');
    }

    public function destroy(Employee $employee, EmployeeEquipment $equipment)
    {
        if ($equipment->employee_id !== $employee->id) {
            abort(404);
        }

        return redirect()
            ->route('admin.employees.equipment.index', $employee)
            ->withErrors([
                'equipment' => 'Equipment assignment history cannot be deleted. Update the record to returned or lost instead.',
            ]);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'effective_date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'device_identifier' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', self::VALID_STATUSES),
            'notes' => 'nullable|string',
        ]);
    }
}
