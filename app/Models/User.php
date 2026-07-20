<?php

namespace App\Models;

use App\Support\LabPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    public const SCOPE_MAIN_LAB = 'main_lab';
    public const SCOPE_COLLECTION_CENTER = 'collection_center';

    protected $fillable = [
        'name', 'username', 'password', 'branch', 'email',
        'organization_id', 'collection_center_id', 'user_scope',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function collectionCenter(): BelongsTo
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function isMainLabScope(): bool
    {
        return $this->user_scope === self::SCOPE_MAIN_LAB;
    }

    public function isCollectionCenterScope(): bool
    {
        return $this->user_scope === self::SCOPE_COLLECTION_CENTER;
    }

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

    /**
     * The route name the user should land on after login: the lab hub for a
     * Super Admin, otherwise the first module the user actually has access to.
     * Falls back to the hub when the user has no lab permissions.
     */
    public function homeRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'pathology.index';
        }

        foreach (LabPermissions::landingRoutes() as $route => $permission) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        return 'pathology.index';
    }
}