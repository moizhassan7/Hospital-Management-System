<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name', 'username', 'password', 'branch', 'email',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Request-lifetime memo for the Super Admin check so repeated calls in a
     * single request (navigation rail, dashboard, middleware) don't re-query.
     */
    private ?bool $superAdminCache = null;

    /**
     * Request-lifetime memo of the user's effective permission names
     * (direct + via roles), loaded once with eager-loaded relations.
     *
     * @var list<string>|null
     */
    private ?array $permissionNamesCache = null;

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function isSuperAdmin()
    {
        if ($this->superAdminCache === null) {
            $this->superAdminCache = $this->roles()->where('name', 'Super Admin')->exists();
        }

        return $this->superAdminCache;
    }

    public function hasPermission($permissionName)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($permissionName, $this->effectivePermissionNames(), true);
    }

    /**
     * Effective permission names for a non-super-admin user: direct permissions
     * plus permissions granted through roles. Loaded once per request.
     *
     * @return list<string>
     */
    private function effectivePermissionNames(): array
    {
        if ($this->permissionNamesCache === null) {
            $direct = $this->permissions()->pluck('name')->all();

            $fromRoles = $this->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
                ->all();

            $this->permissionNamesCache = array_values(array_unique(array_merge($direct, $fromRoles)));
        }

        return $this->permissionNamesCache;
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        foreach ($permissionNames as $permissionName) {
            if ($this->hasPermission($permissionName)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function allPermissionNames(): array
    {
        if ($this->isSuperAdmin()) {
            return Permission::query()->pluck('name')->all();
        }

        return $this->effectivePermissionNames();
    }
}