<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission as PermissionHelper;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Simple central place to review and update user roles.
     */
    public function index(Request $request)
    {
        $this->ensureCanManageRoles();

        $roles = $this->availableRoles($request->user());
        $hasUserRolesTable = Schema::hasTable('user_roles');

        $filterRole = $request->input('role');
        $search     = $request->input('q');

        $usersQuery = User::query()
            ->when($hasUserRolesTable, fn ($q) => $q->with('userRoles'))
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

        return view('admin.roles.index', compact('users', 'roles', 'roleCounts', 'filterRole', 'search'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureCanManageRoles();

        $actor = $request->user();
        $roles = $this->availableRoles($actor);
        $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys($roles)),
        ]);

        $newRole = $request->input('role');
        $oldPrimaryRole = $user->role;

        if (! $actor?->hasRole('super_admin')) {
            if ($newRole === 'super_admin' || $user->hasRole('super_admin')) {
                return redirect()
                    ->route('admin.roles.index', $request->only('role', 'q', 'page'))
                    ->withErrors(['role' => 'Only super admin can assign or modify the super admin role.']);
            }
        }

        DB::transaction(function () use ($user, $newRole, $oldPrimaryRole) {
            $user->role = $newRole;
            $user->save();
            $this->syncPrimaryRole($user, $oldPrimaryRole);
        });

        return redirect()
            ->route('admin.roles.index', $request->only('role', 'q', 'page'))
            ->with('status', 'Role updated for ' . ($user->name ?: $user->email) . '.');
    }

    public function create(Request $request)
    {
        $this->ensureCanManageRoles();

        // Only super admin can create new roles.
        if (! $request->user()?->hasRole('super_admin')) {
            return redirect()
                ->route('admin.roles.index')
                ->withErrors(['role_create' => 'Only super admin can create roles.']);
        }

        $data = $request->validate([
            'key'   => 'required|string|max:50|alpha_dash',
            'label' => 'required|string|max:100',
        ]);

        $roleKey = Str::lower(trim($data['key']));
        if (Role::query()->where('key', $roleKey)->exists()) {
            return redirect()
                ->route('admin.roles.index')
                ->withErrors(['key' => 'Role key already exists.'])
                ->withInput();
        }

        Role::create([
            'key'       => $roleKey,
            'label'     => $data['label'],
            'is_system' => false,
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role ' . $data['label'] . ' created.');
    }

    protected function ensureCanManageRoles(): void
    {
        if (! PermissionHelper::can(auth()->user(), 'roles.manage')) {
            abort(403, 'You do not have permission to manage roles.');
        }
    }

    /**
     * Central list of available roles used by the role manager.
     */
    protected function availableRoles(?User $actor = null): array
    {
        $roles = Role::orderBy('label')->get(['key', 'label']);

        // Fallback to legacy list if table is empty.
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

    protected function syncPrimaryRole(User $user, ?string $previousPrimaryRole = null): void
    {
        if (! Schema::hasTable('user_roles') || empty($user->role)) {
            return;
        }

        // The simple role manager is a single-role editor, so remove any
        // previously assigned effective roles before applying the new primary one.
        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_key', '!=', $user->role)
            ->delete();

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $user->id, 'role_key' => $user->role],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }
}
