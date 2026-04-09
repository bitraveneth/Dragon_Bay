<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLocationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmployeeLocationController extends Controller
{
    private const VALID_SOURCES = [
        'manual',
        'gps',
        'api',
        'import',
        'other',
    ];

    public function all()
    {
        $logs = EmployeeLocationLog::with('employee')
            ->orderByDesc('logged_at')
            ->paginate(20);

        return view('admin.employees.locations.all', compact('logs'));
    }

    public function createGlobal()
    {
        $employees = Employee::orderBy('name')->get();

        return view('admin.employees.locations.create_global', [
            'employees' => $employees,
            'log' => new EmployeeLocationLog([
                'logged_at' => Carbon::now(),
                'source' => 'manual',
            ]),
        ]);
    }

    public function storeGlobal(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'logged_at' => 'required|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_label' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        $payload = collect($data)->except('employee_id')->all();

        $employee->locationLogs()->create($payload);

        return redirect()
            ->route('admin.locations.index')
            ->with('status', 'Location log added.');
    }

    public function index(Employee $employee)
    {
        $logs = $employee->locationLogs()
            ->orderByDesc('logged_at')
            ->paginate(20);

        return view('admin.employees.locations.index', compact('employee', 'logs'));
    }

    public function create(Employee $employee)
    {
        return view('admin.employees.locations.create', [
            'employee' => $employee,
            'log' => new EmployeeLocationLog([
                'logged_at' => Carbon::now(),
                'source' => 'manual',
            ]),
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $this->validated($request);

        $employee->locationLogs()->create($data);

        return redirect()
            ->route('admin.employees.locations.index', $employee)
            ->with('status', 'Location log added.');
    }

    public function edit(Employee $employee, EmployeeLocationLog $location)
    {
        if ($location->employee_id !== $employee->id) {
            abort(404);
        }

        return view('admin.employees.locations.edit', [
            'employee' => $employee,
            'log' => $location,
        ]);
    }

    public function update(Request $request, Employee $employee, EmployeeLocationLog $location)
    {
        if ($location->employee_id !== $employee->id) {
            abort(404);
        }

        $data = $this->validated($request);

        $location->update($data);

        return redirect()
            ->route('admin.employees.locations.index', $employee)
            ->with('status', 'Location log updated.');
    }

    public function destroy(Employee $employee, EmployeeLocationLog $location)
    {
        if ($location->employee_id !== $employee->id) {
            abort(404);
        }

        return redirect()
            ->route('admin.employees.locations.index', $employee)
            ->withErrors([
                'location' => 'Location logs are historical records and cannot be deleted.',
            ]);
    }

    protected function validated(Request $request): array
    {
        $request->merge([
            'source' => $this->normalizedNullableSource($request->input('source')),
            'location_label' => $this->normalizedNullableString($request->input('location_label')),
            'notes' => $this->normalizedNullableString($request->input('notes')),
        ]);

        return $request->validate([
            'logged_at' => 'required|date',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_label' => 'nullable|string|max:255',
            'source' => 'nullable|in:' . implode(',', self::VALID_SOURCES),
            'notes' => 'nullable|string|max:255',
        ]);
    }

    protected function normalizedNullableString($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizedNullableSource($value): ?string
    {
        $value = mb_strtolower(trim((string) $value));

        return $value === '' ? null : $value;
    }
}
