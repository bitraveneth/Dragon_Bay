<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission as PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PermissionController extends Controller
{
    /**
     * Canonical permission module labels.
     */
    protected array $groupAliases = [
        'access control' => 'Access Control',
        'masters & control' => 'Masters & Control',
        'purchasing' => 'Purchasing',
        'inventory & stock' => 'Inventory & Stock',
        'production & qc' => 'Production & QC',
        'sales & returns' => 'Sales & Returns',
        'accounting & finance' => 'Accounting & Finance',
        'reports & analytics' => 'Reports & Analytics',
        'system' => 'System',
    ];

    protected array $groupOrder = [
        'Access Control',
        'Masters & Control',
        'Purchasing',
        'Inventory & Stock',
        'Production & QC',
        'Sales & Returns',
        'Accounting & Finance',
        'Reports & Analytics',
        'System',
        'Other',
    ];

    public function index(Request $request)
    {
        $this->ensureCanManagePermissions();

        $permissions = Permission::orderBy('group')->orderBy('name')->get();

        // Build group summary in PHP with normalized labels so minor text
        // inconsistencies (spaces/case) don't create duplicate groups in UI.
        $groups = $permissions
            ->groupBy(fn ($perm) => $this->normalizeGroupKey($perm->group))
            ->map(function ($items, $groupKey) {
                $label = $this->canonicalGroupLabel((string) $groupKey);

                return (object) [
                    'group_key' => $groupKey,
                    'label' => $label,
                    'total' => $items->count(),
                ];
            })
            ->sortBy(fn ($group) => $this->groupSortRank($group->label))
            ->values();

        // Permissions grouped by module label for easier per-role display
        $groupedPermissions = $permissions
            ->groupBy(fn ($perm) => $this->canonicalGroupLabel($perm->group))
            ->sortKeysUsing(fn ($a, $b) => $this->groupSortRank($a) <=> $this->groupSortRank($b));

        $roles = Role::orderBy('label')->pluck('label', 'key')->all();

        // Fallback to legacy roles if roles table is empty
        if (empty($roles)) {
            $roles = [
                'super_admin'        => 'Super admin',
                'admin'              => 'Admin',
                'purchase_executive' => 'Purchase executive',
                'warehouse_officer'  => 'Warehouse officer',
                'production_officer' => 'Production officer',
                'sales_officer'      => 'Sales officer',
                'delivery_coordinator' => 'Delivery coordinator',
                'accounts_officer'   => 'Accounts officer',
                'qc_officer'         => 'QC officer',
            ];
        }

        $rolePermissions = RolePermission::all()
            ->groupBy('role')
            ->map(function ($rows) {
                return $rows->pluck('permission_name')->all();
            });
        
        $selectedRoleKey   = $request->input('role');
        $selectedRoleLabel = $selectedRoleKey && isset($roles[$selectedRoleKey]) ? $roles[$selectedRoleKey] : null;
        $selectedPermissionNames = $selectedRoleKey && isset($rolePermissions[$selectedRoleKey])
            ? $rolePermissions[$selectedRoleKey]
            : [];

        return view('admin.permissions.index', compact(
            'permissions',
            'roles',
            'rolePermissions',
            'groups',
            'groupedPermissions',
            'selectedRoleKey',
            'selectedRoleLabel',
            'selectedPermissionNames'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureCanManagePermissions();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', 'unique:permissions,name'],
            'label' => 'required|string|max:255',
            'group' => 'nullable|string|max:100',
        ]);

        $data['name'] = strtolower(trim($data['name']));
        $data['group'] = isset($data['group']) && trim((string) $data['group']) !== ''
            ? $this->canonicalGroupLabel($data['group'])
            : null;

        Permission::create($data);

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Permission created.');
    }

    public function updateRoles(Request $request)
    {
        $this->ensureCanManagePermissions();

        $data = $request->validate([
            'role_permissions' => 'array',
            'submitted_roles' => 'nullable|array',
            'submitted_roles.*' => 'string',
        ]);

        $validRoles = array_keys($this->availableRoles());
        $validPermissions = Permission::pluck('name')->all();
        $rolePermissions = collect($data['role_permissions'] ?? []);
        $submittedRoles = collect($data['submitted_roles'] ?? [])
            ->map(fn ($value) => (string) $value)
            ->filter(fn ($value) => in_array($value, $validRoles, true))
            ->unique()
            ->values();

        if ($submittedRoles->isEmpty()) {
            return redirect()->route('admin.permissions.index')
                ->withErrors(['role_permissions' => 'No roles were submitted for update.']);
        }

        foreach ($rolePermissions as $role => $permissionNames) {
            if (! in_array($role, $validRoles, true) || ! in_array($role, $submittedRoles->all(), true)) {
                return redirect()->route('admin.permissions.index')
                    ->withErrors(['role_permissions' => 'Invalid role submitted.']);
            }

            $invalidPermissionNames = collect($permissionNames ?? [])
                ->map(fn ($value) => (string) $value)
                ->filter(fn ($value) => ! in_array($value, $validPermissions, true))
                ->values();

            if ($invalidPermissionNames->isNotEmpty()) {
                return redirect()->route('admin.permissions.index')
                    ->withErrors(['role_permissions' => 'Invalid permission submitted: ' . $invalidPermissionNames->implode(', ')]);
            }
        }

        DB::transaction(function () use ($submittedRoles, $rolePermissions) {
            // Replace mappings only for roles that were explicitly submitted.
            foreach ($submittedRoles as $role) {
                RolePermission::where('role', $role)->delete();

                foreach (($rolePermissions->get($role, []) ?? []) as $permissionName) {
                    RolePermission::create([
                        'role'            => $role,
                        'permission_name' => $permissionName,
                    ]);
                }
            }
        });

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Role permissions updated.');
    }

    /**
     * Update permissions for a single role from the grouped UI.
     */
    public function updateRole(Request $request, string $role)
    {
        $this->ensureCanManagePermissions();

        if (! in_array($role, array_keys($this->availableRoles(true)), true)) {
            return redirect()
                ->route('admin.permissions.index')
                ->withErrors(['role' => 'Invalid role selected.']);
        }

        // Super admin is treated as having all permissions; nothing to update.
        if ($role === 'super_admin') {
            return redirect()
                ->route('admin.permissions.index', ['role' => $role])
                ->with('status', 'Super admin already has access to everything. No changes needed.');
        }

        $validPermissions = Permission::pluck('name')->all();
        $permissions = collect($request->input('permissions', []))
            ->map(fn ($value) => (string) $value)
            ->filter()
            ->unique()
            ->values();

        $invalidPermissionNames = $permissions
            ->filter(fn ($value) => ! in_array($value, $validPermissions, true))
            ->values();

        if ($invalidPermissionNames->isNotEmpty()) {
            return redirect()
                ->route('admin.permissions.index', ['role' => $role])
                ->withErrors(['permissions' => 'Invalid permission submitted: ' . $invalidPermissionNames->implode(', ')]);
        }

        RolePermission::where('role', $role)->delete();

        foreach ($permissions as $permissionName) {
            RolePermission::create([
                'role'            => $role,
                'permission_name' => $permissionName,
            ]);
        }

        return redirect()
            ->route('admin.permissions.index', ['role' => $role])
            ->with('status', 'Permissions updated for role: ' . $role . '.');
    }

    public function destroy(Permission $permission)
    {
        $this->ensureCanManagePermissions();

        if ($this->isProtectedPermission($permission->name)) {
            return redirect()->route('admin.permissions.index')
                ->withErrors(['permission' => 'This permission is referenced by the live system and cannot be deleted from the UI.']);
        }

        RolePermission::where('permission_name', $permission->name)->delete();
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Permission deleted.');
    }

    public function renameGroup(Request $request)
    {
        $this->ensureCanManagePermissions();

        $data = $request->validate([
            'current_group' => 'nullable|string|max:100',
            'new_group'     => 'required|string|max:100',
        ]);

        $current = $data['current_group'] === '' ? null : $data['current_group'];

        Permission::where('group', $current)->update(['group' => $this->canonicalGroupLabel($data['new_group'])]);

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Group renamed to ' . $data['new_group'] . '.');
    }

    public function deleteGroup(Request $request)
    {
        $this->ensureCanManagePermissions();

        $data = $request->validate([
            'group' => 'nullable|string|max:100',
        ]);

        $group = $data['group'] === '' ? null : $data['group'];

        $permissionNames = Permission::where('group', $group)->pluck('name');

        if ($permissionNames->isEmpty()) {
            return redirect()->route('admin.permissions.index')
                ->with('status', 'Group already empty.');
        }

        $protectedPermissions = $permissionNames
            ->filter(fn ($permissionName) => $this->isProtectedPermission((string) $permissionName))
            ->values();

        if ($protectedPermissions->isNotEmpty()) {
            return redirect()->route('admin.permissions.index')
                ->withErrors([
                    'group' => 'This group contains live system permissions and cannot be deleted: ' . $protectedPermissions->implode(', '),
                ]);
        }

        RolePermission::whereIn('permission_name', $permissionNames)->delete();
        Permission::whereIn('name', $permissionNames)->delete();

        return redirect()->route('admin.permissions.index')
            ->with('status', 'Group and its permissions deleted.');
    }

    protected function ensureCanManagePermissions(): void
    {
        if (! PermissionHelper::can(auth()->user(), 'permissions.manage')) {
            abort(403, 'You do not have permission to manage permissions.');
        }
    }

    protected function availableRoles(bool $includeSuperAdmin = false): array
    {
        $roles = Role::orderBy('label')->pluck('label', 'key')->all();

        if (empty($roles)) {
            $roles = [
                'super_admin'        => 'Super admin',
                'admin'              => 'Admin',
                'purchase_executive' => 'Purchase executive',
                'warehouse_officer'  => 'Warehouse officer',
                'production_officer' => 'Production officer',
                'sales_officer'      => 'Sales officer',
                'delivery_coordinator' => 'Delivery coordinator',
                'accounts_officer'   => 'Accounts officer',
                'qc_officer'         => 'QC officer',
            ];
        }

        if (! $includeSuperAdmin) {
            unset($roles['super_admin']);
        }

        return $roles;
    }

    protected function normalizeGroupKey(?string $group): string
    {
        $label = trim((string) ($group ?? ''));
        if ($label === '') {
            return 'other';
        }

        return mb_strtolower($label);
    }

    protected function canonicalGroupLabel(?string $group): string
    {
        $key = $this->normalizeGroupKey($group);

        return $this->groupAliases[$key] ?? ($key === 'other' ? 'Other' : trim((string) $group));
    }

    protected function groupSortRank(string $label): int
    {
        $rank = array_search($label, $this->groupOrder, true);

        return $rank === false ? 999 : $rank;
    }

    protected function isProtectedPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->protectedPermissionNames(), true);
    }

    protected function protectedPermissionNames(): array
    {
        static $protected = null;

        if ($protected !== null) {
            return $protected;
        }

        $protected = collect(Route::getRoutes())
            ->flatMap(function ($route) {
                return collect($route->gatherMiddleware())
                    ->filter(fn ($middleware) => is_string($middleware) && str_starts_with($middleware, 'perm:'))
                    ->map(fn ($middleware) => substr($middleware, strlen('perm:')));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $protected;
    }
}
