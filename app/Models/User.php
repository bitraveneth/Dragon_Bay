<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'agent_id',
        'client_id',
        'employee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** Internal agent (salesperson) linked to this user account. */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /** Client account linked to this portal user. */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_key', 'id', 'key');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function warehouseScopes()
    {
        return $this->hasMany(UserWarehouseScope::class);
    }

    /**
     * Returns all role keys assigned to this user (primary + pivot roles).
     */
    public function roleKeys(): array
    {
        $keys = [];

        if (! empty($this->role)) {
            $keys[] = $this->role;
        }

        $extraKeys = [];
        if (Schema::hasTable('user_roles')) {
            $extraKeys = $this->relationLoaded('userRoles')
                ? $this->userRoles->pluck('role_key')->all()
                : $this->userRoles()->pluck('role_key')->all();
        }

        $keys = array_merge($keys, $extraKeys);

        return array_values(array_unique(array_filter($keys)));
    }

    public function hasRole(string $roleKey): bool
    {
        return in_array($roleKey, $this->roleKeys(), true);
    }

    public function hasAnyRole(array $roleKeys): bool
    {
        return ! empty(array_intersect($this->roleKeys(), $roleKeys));
    }

    /**
     * Returns warehouse IDs this user can access.
     * null means unrestricted (all warehouses).
     */
    public function accessibleWarehouseIds(): ?array
    {
        if ($this->hasAnyRole(['admin', 'super_admin'])) {
            return null;
        }

        $ids = [];
        if (Schema::hasTable('user_warehouse_scopes')) {
            $ids = $this->relationLoaded('warehouseScopes')
                ? $this->warehouseScopes->pluck('warehouse_id')->map(fn ($id) => (int) $id)->all()
                : $this->warehouseScopes()->pluck('warehouse_id')->map(fn ($id) => (int) $id)->all();
        }

        $ids = array_values(array_unique(array_filter($ids)));

        return empty($ids) ? null : $ids;
    }

    public function canAccessWarehouse(?int $warehouseId): bool
    {
        if (! $warehouseId) {
            return true;
        }

        $accessible = $this->accessibleWarehouseIds();

        return $accessible === null || in_array((int) $warehouseId, $accessible, true);
    }
}
