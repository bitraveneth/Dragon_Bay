<?php

namespace App\Helpers;

use App\Models\UserPermission;
use App\Models\User;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Schema;

class Permission
{
    /**
     * Check if a given user has a named permission based on their role.
     *
     * This is intentionally simple for now – a static role → permission map.
     */
    public static function can(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $roleKeys = $user->roleKeys();
        if (in_array('super_admin', $roleKeys, true)) {
            // Super Admin can do everything.
            return true;
        }

        // Transitional fallback: if the admin role has not been mapped yet,
        // preserve legacy full access to avoid locking out local installs.
        // Once any explicit admin permission rows exist, admin becomes
        // permission-driven like every other role.
        static $adminUsesLegacyFullAccess = null;
        if (in_array('admin', $roleKeys, true)) {
            if ($adminUsesLegacyFullAccess === null) {
                $adminUsesLegacyFullAccess = ! Schema::hasTable('role_permissions')
                    || ! RolePermission::query()->where('role', 'admin')->exists();
            }

            if ($adminUsesLegacyFullAccess) {
                return true;
            }
        }

        $candidates = self::permissionCandidates($permission);

        // User-level override has highest priority.
        // We check from most specific to broader candidates.
        static $userOverridesCache = [];
        if (! array_key_exists($user->id, $userOverridesCache)) {
            $userOverridesCache[$user->id] = Schema::hasTable('user_permissions')
                ? UserPermission::query()
                    ->where('user_id', $user->id)
                    ->pluck('allowed', 'permission_name')
                    ->all()
                : [];
        }
        $overrides = $userOverridesCache[$user->id];

        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $overrides)) {
                return (bool) $overrides[$candidate];
            }
        }

        if (empty($roleKeys)) {
            return false;
        }

        sort($roleKeys);
        $roleCacheKey = implode('|', $roleKeys);

        static $rolePermissionsCache = [];
        if (! array_key_exists($roleCacheKey, $rolePermissionsCache)) {
            $rolePermissionsCache[$roleCacheKey] = Schema::hasTable('role_permissions')
                ? RolePermission::query()
                    ->whereIn('role', $roleKeys)
                    ->pluck('permission_name')
                    ->all()
                : [];
        }

        return ! empty(array_intersect($candidates, $rolePermissionsCache[$roleCacheKey]));
    }

    /**
     * Build a permission resolution chain from most specific to broader.
     *
     * Examples:
     * - sales.order.create -> sales.order.create, sales.order, sales.order.manage, sales, sales.manage
     * - control.products.edit -> control.products.edit, control.products, control.products.manage, control, control.manage
     */
    protected static function permissionCandidates(string $permission): array
    {
        $permission = trim($permission);
        if ($permission === '') {
            return [];
        }

        $parts = explode('.', $permission);
        $candidates = [$permission];

        for ($i = count($parts) - 1; $i >= 1; $i--) {
            $prefix = implode('.', array_slice($parts, 0, $i));
            $candidates[] = $prefix;
            $candidates[] = $prefix . '.manage';
        }

        if (! str_ends_with($permission, '.manage')) {
            $candidates[] = $permission . '.manage';
        }

        return array_values(array_unique($candidates));
    }
}
