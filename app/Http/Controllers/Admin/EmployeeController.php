<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission as PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('name')->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $employee = new Employee();
        [$departments, $jobPositions, $workZones, $tagOptions] = $this->formLookups();

        return view('admin.employees.create', compact('employee', 'departments', 'jobPositions', 'workZones', 'tagOptions'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if ($request->hasFile('cv')) {
            $data['cv_path'] = $request->file('cv')->store('employees/cv', 'public');
        }

        Employee::create($data);

        return redirect()->route('admin.employees.index')->with('status', 'Employee added.');
    }

    public function edit(Employee $employee)
    {
        [$departments, $jobPositions, $workZones, $tagOptions] = $this->formLookups();

        return view('admin.employees.edit', compact('employee', 'departments', 'jobPositions', 'workZones', 'tagOptions'));
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'contracts' => function ($query) {
                $query->orderByDesc('start_date');
            },
            'allowances',
            'equipment',
            'leaves',
            'locationLogs',
            'badges',
            'user',
        ]);

        $currentContract = $employee->contracts->first();
        $recentAllowances = $employee->allowances
            ? $employee->allowances->sortByDesc('date')->take(5)
            : collect();

        return view('admin.employees.show', compact('employee', 'currentContract', 'recentAllowances'));
    }

    /**
     * Show a small form that lets an admin create a login account
     * for a given employee.
     */
    public function createUser(Employee $employee)
    {
        $this->ensureCanManageEmployeeUsers();

        // Prevent creating multiple accounts for the same employee.
        if ($employee->user) {
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('status', 'This employee already has a login account.');
        }

        $roles = $this->availableRoles(auth()->user());

        return view('admin.employees.create_user', compact('employee', 'roles'));
    }

    /**
     * Store a user record linked to the given employee.
     */
    public function storeUser(Request $request, Employee $employee)
    {
        $actor = auth()->user();
        $this->ensureCanManageEmployeeUsers();

        if ($employee->user) {
            return redirect()
                ->route('admin.employees.show', $employee)
                ->with('status', 'This employee already has a login account.');
        }

        $roles = $this->availableRoles($actor);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string|in:' . implode(',', array_keys($roles)),
            'password' => 'nullable|string|min:6',
        ]);

        if (($data['role'] ?? null) === 'super_admin' && ! $actor?->hasRole('super_admin')) {
            abort(403, 'Only super admin can create another super admin account.');
        }

        $plainPassword = $data['password'] ?: Str::random(10);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $plainPassword,
            'role' => $data['role'],
            'employee_id' => $employee->id,
        ]);
        $this->syncPrimaryRole($user);

        return redirect()
            ->route('admin.employees.show', $employee)
            ->with('status', 'Login account created for this employee. Temporary password: ' . $plainPassword);
    }

    protected function ensureCanManageEmployeeUsers(): void
    {
        if (! PermissionHelper::can(auth()->user(), 'system.settings')) {
            abort(403, 'You do not have permission to create employee login accounts.');
        }
    }

    protected function syncPrimaryRole(User $user): void
    {
        if (! Schema::hasTable('user_roles') || empty($user->role)) {
            return;
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $user->id, 'role_key' => $user->role],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }

    protected function availableRoles(?User $actor = null): array
    {
        $roles = Role::orderBy('label')->get(['key', 'label']);

        if ($roles->isEmpty()) {
            $legacyRoles = [
                'super_admin' => 'Super admin',
                'admin' => 'Admin',
                'purchase_executive' => 'Purchase executive',
                'warehouse_officer' => 'Warehouse officer',
                'production_officer' => 'Production officer',
                'sales_officer' => 'Sales officer',
                'delivery_coordinator' => 'Delivery coordinator',
                'accounts_officer' => 'Accounts officer',
                'qc_officer' => 'QC officer',
            ];

            if ($actor && ! $actor->hasRole('super_admin')) {
                unset($legacyRoles['super_admin']);
            }

            return $legacyRoles;
        }

        $roleMap = $roles->pluck('label', 'key')->all();

        if ($actor && ! $actor->hasRole('super_admin')) {
            unset($roleMap['super_admin']);
        }

        return $roleMap;
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $this->validated($request, $employee->id);

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if ($request->hasFile('cv')) {
            if ($employee->cv_path) {
                Storage::disk('public')->delete($employee->cv_path);
            }
            $data['cv_path'] = $request->file('cv')->store('employees/cv', 'public');
        }

        $employee->update($data);

        return redirect()->route('admin.employees.index')->with('status', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $blockingHistory = $this->deletionBlockingHistory($employee->id);

        if ($blockingHistory !== []) {
            return redirect()
                ->route('admin.employees.index')
                ->withErrors([
                    'employee' => 'This employee has historical records (' . implode(', ', $blockingHistory) . ') and cannot be deleted.',
                ]);
        }

        // Unlink any user accounts pointing at this employee so the record can be removed safely.
        User::where('employee_id', $employee->id)->update(['employee_id' => null]);

        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }

        if ($employee->cv_path) {
            Storage::disk('public')->delete($employee->cv_path);
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('status', 'Employee deleted and any linked user accounts were unassigned.');
    }

    protected function deletionBlockingHistory(int $employeeId): array
    {
        $checks = [
            'employee_contracts' => 'contracts',
            'employee_allowances' => 'allowances',
            'employee_equipment' => 'equipment',
            'employee_leaves' => 'leave records',
            'employee_location_logs' => 'location logs',
            'employee_badges' => 'badge history',
            'salary_distributions' => 'salary distributions',
            'employee_leave_balances' => 'leave balances',
            'visit_plans' => 'visit plans',
        ];

        $found = [];

        foreach ($checks as $table => $label) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (DB::table($table)->where('employee_id', $employeeId)->exists()) {
                $found[] = $label;
            }
        }

        return $found;
    }

    protected function validated(Request $request, ?int $employeeId = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'work_email' => 'nullable|email|max:255',
            'work_phone' => 'nullable|string|max:50',
            'work_mobile' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'job_position' => 'nullable|string|max:255',
            'work_zone' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'cv' => 'nullable|file|max:5120',
        ]);

        if (!empty($data['tags'])) {
            $tags = array_filter(array_map('trim', explode(',', $data['tags'])));
            $data['tags'] = array_values(array_unique($tags));
        } else {
            $data['tags'] = [];
        }

        return $data;
    }

    protected function formLookups(): array
    {
        $departments = collect([
            'Sales & Marketing',
            'Inventory & Logistics',
            'Production',
            'HR & People',
            'Finance',
        ]);

        $jobPositions = collect([
            'Marketing Manager',
            'Sales Representative',
            'Sales Manager',
            'Production Supervisor',
            'QC Officer',
            'HR Officer',
            'Accountant',
        ]);

        $workZones = collect([
            'Dhaka North',
            'Dhaka South',
            'Chattogram',
            'Rajshahi',
            'Khulna',
            'Sylhet',
            'Factory',
        ]);

        $tagOptions = Employee::select('tags')
            ->whereNotNull('tags')
            ->get()
            ->flatMap(function (Employee $employee) {
                return is_array($employee->tags) ? $employee->tags : [];
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return [$departments, $jobPositions, $workZones, $tagOptions];
    }
}
