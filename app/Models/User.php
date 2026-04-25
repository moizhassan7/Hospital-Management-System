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
        return $this->roles()->where('name', 'Super Admin')->exists();
    }

    public function hasPermission($permissionName)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check individual permissions
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // Check permissions through roles
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }
}