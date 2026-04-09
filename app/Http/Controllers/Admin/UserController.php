<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission as PermissionGate;
use App\Http\Controllers\Controller;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserRole;
use App\Models\UserWarehouseScope;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles($request->user());
        $hasUserRolesTable = Schema::hasTable('user_roles');
        $hasUserPermissionsTable = Schema::hasTable('user_permissions');
        $hasWarehouseScopesTable = Schema::hasTable('user_warehouse_scopes');

        $filterRole = $request->input('role');
        $search     = $request->input('q');

        $usersQuery = User::query()
            ->when($hasUserRolesTable, fn ($q) => $q->with('userRoles'))
            ->when($hasUserPermissionsTable, fn ($q) => $q->withCount(['userPermissions as permission_overrides_count']))
            ->when($hasWarehouseScopesTable, fn ($q) => $q->withCount(['warehouseScopes as warehouse_scopes_count']))
            ->orderBy('name')
            ->when($filterRole, function ($q) use ($filterRole) {
                $q->where(function ($inner) use ($filterRole) {
                    $inner->where('role', $filterRole)
                        ->when(Schema::hasTable('user_roles'), function ($sub) use ($filterRole) {
                            $sub->orWhereHas('userRoles', function ($roleQuery) use ($filterRole) {
                                $roleQuery->where('role_key', $filterRole);
                            });
                        });
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });

        $users = $usersQuery->paginate(20)->withQueryString();

        $roleCounts = $hasUserRolesTable
            ? DB::table('user_roles')
                ->selectRaw('role_key as role, COUNT(DISTINCT user_id) as total')
                ->groupBy('role_key')
                ->pluck('total', 'role')
            : User::selectRaw('role, COUNT(*) as total')
                ->whereNotNull('role')
                ->groupBy('role')
                ->pluck('total', 'role');

        return view('admin.users.index', compact(
            'users',
            'roles',
            'roleCounts',
            'filterRole',
            'search',
            'hasUserPermissionsTable',
            'hasWarehouseScopesTable'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $actor = $request->user();
        $roles = $this->availableRoles($actor);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'role'     => 'required|string|in:' . implode(',', array_keys($roles)),
            'password' => 'nullable|string|min:8|max:191',
        ]);

        $password = $data['password'] ?: str()->random(12);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => $password,
        ]);
        $this->syncPrimaryRole($user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created. Remember to share credentials securely.');
    }

    public function editAccess(User $user)
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles(auth()->user());
        $primaryRole = $user->role;

        $hasUserRolesTable = Schema::hasTable('user_roles');
        $hasUserPermissionsTable = Schema::hasTable('user_permissions');
        $hasWarehouseScopesTable = Schema::hasTable('user_warehouse_scopes');
        $hasPermissionsTable = Schema::hasTable('permissions');
        $hasWarehousesTable = Schema::hasTable('warehouses');

        $selectedRoleKeys = $hasUserRolesTable ? $user->roleKeys() : array_values(array_filter([$primaryRole]));
        $selectedWarehouseIds = $hasWarehouseScopesTable
            ? UserWarehouseScope::where('user_id', $user->id)->pluck('warehouse_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $permissionsGrouped = collect();
        if ($hasPermissionsTable) {
            $permissionsGrouped = PermissionModel::query()
                ->orderBy('group')
                ->orderBy('name')
                ->get()
                ->groupBy(fn ($permission) => $permission->group ?: 'Other');
        }

        $warehouses = $hasWarehousesTable
            ? Warehouse::orderBy('name')->get()
            : collect();

        $permissionOverrides = $hasUserPermissionsTable
            ? UserPermission::where('user_id', $user->id)->pluck('allowed', 'permission_name')->map(fn ($value) => (bool) $value)->all()
            : [];

        return view('admin.users.access', compact(
            'user',
            'roles',
            'primaryRole',
            'selectedRoleKeys',
            'permissionsGrouped',
            'warehouses',
            'selectedWarehouseIds',
            'permissionOverrides',
            'hasUserRolesTable',
            'hasUserPermissionsTable',
            'hasWarehouseScopesTable'
        ));
    }

    public function updateAccess(Request $request, User $user)
    {
        $this->ensureAdmin();

        $actor = $request->user();
        $roles = $this->availableRoles($actor);
        $roleKeys = array_keys($roles);

        $data = $request->validate([
            'primary_role' => 'required|string|in:' . implode(',', $roleKeys),
            'extra_roles' => 'nullable|array',
            'extra_roles.*' => 'string|in:' . implode(',', $roleKeys),
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'integer|exists:warehouses,id',
            'overrides' => 'nullable|array',
        ]);

        if (! $actor?->hasRole('super_admin')) {
            if (($data['primary_role'] ?? null) === 'super_admin'
                || in_array('super_admin', $data['extra_roles'] ?? [], true)
                || $user->hasRole('super_admin')) {
                return redirect()
                    ->route('admin.users.access.edit', $user)
                    ->withErrors(['primary_role' => 'Only super admin can assign or modify the super admin role.']);
            }
        }

        DB::transaction(function () use ($data, $user) {
            $primaryRole = $data['primary_role'];
            $extraRoles = collect($data['extra_roles'] ?? [])
                ->map(fn ($value) => (string) $value)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $user->role = $primaryRole;
            $user->save();

            $this->syncPrimaryRole($user);

            if (Schema::hasTable('user_roles')) {
                $allRoles = array_values(array_unique(array_merge([$primaryRole], $extraRoles)));

                UserRole::where('user_id', $user->id)
                    ->whereNotIn('role_key', $allRoles)
                    ->delete();

                foreach ($allRoles as $roleKey) {
                    UserRole::updateOrCreate(
                        ['user_id' => $user->id, 'role_key' => $roleKey],
                        []
                    );
                }
            }

            if (Schema::hasTable('user_warehouse_scopes')) {
                $warehouseIds = collect($data['warehouse_ids'] ?? [])
                    ->map(fn ($value) => (int) $value)
                    ->unique()
                    ->values()
                    ->all();

                UserWarehouseScope::where('user_id', $user->id)
                    ->whereNotIn('warehouse_id', $warehouseIds)
                    ->delete();

                foreach ($warehouseIds as $warehouseId) {
                    UserWarehouseScope::updateOrCreate(
                        ['user_id' => $user->id, 'warehouse_id' => $warehouseId],
                        []
                    );
                }
            }

            if (Schema::hasTable('user_permissions') && Schema::hasTable('permissions')) {
                $validPermissions = PermissionModel::pluck('name')->all();
                $overrides = collect($data['overrides'] ?? [])
                    ->filter(function ($value, $permissionName) use ($validPermissions) {
                        return in_array($permissionName, $validPermissions, true)
                            && in_array($value, ['allow', 'deny'], true);
                    });

                UserPermission::where('user_id', $user->id)
                    ->whereIn('permission_name', $validPermissions)
                    ->delete();

                foreach ($overrides as $permissionName => $value) {
                    UserPermission::create([
                        'user_id' => $user->id,
                        'permission_name' => $permissionName,
                        'allowed' => $value === 'allow',
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.users.access.edit', $user)
            ->with('status', 'User access settings updated.');
    }

    protected function ensureAdmin(): void
    {
        if (! PermissionGate::can(auth()->user(), 'system.settings')) {
            abort(403, 'You do not have permission to manage users.');
        }
    }

    protected function availableRoles(?User $actor = null): array
    {
        $roles = Role::orderBy('label')->get(['key', 'label']);

        if ($roles->isEmpty()) {
            $legacyRoles = [
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
}
