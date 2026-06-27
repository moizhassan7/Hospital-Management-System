<?php

namespace App\Http\Middleware;

use App\Support\LabPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';
        $path = $request->path();

        if (in_array($routeName, ['login', 'logout', 'pathology.online_report'], true)) {
            return $next($request);
        }

        if (str_starts_with($routeName, 'admin.')) {
            abort(403, 'Only Super Admin can access user management.');
        }

        if (! LabPermissions::isLabRoute($routeName, $path)) {
            abort(403, 'This module is not available.');
        }

        if ($routeName === 'pathology.index') {
            if ($user->hasAnyPermission(LabPermissions::all())) {
                return $next($request);
            }

            abort(403, 'You do not have access to any lab module.');
        }

        $permission = LabPermissions::permissionForRoute($routeName);

        if ($permission === null) {
            abort(403, 'You do not have access to this lab feature.');
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'You do not have access to this lab feature.');
    }
}
