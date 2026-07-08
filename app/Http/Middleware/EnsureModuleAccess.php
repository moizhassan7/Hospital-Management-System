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
            return $this->deny($request, $user, 'Only Super Admin can access user management.');
        }

        if (! LabPermissions::isLabRoute($routeName, $path)) {
            return $this->deny($request, $user, 'This module is not available.');
        }

        if ($routeName === 'pathology.index') {
            if ($user->hasAnyPermission(LabPermissions::all())) {
                return $next($request);
            }

            return $this->deny($request, $user, 'You do not have access to any lab module.');
        }

        if (str_ends_with($routeName, '.result_entry.save')) {
            if ($user->hasAnyPermission([LabPermissions::RESULT_ENTRY, LabPermissions::RESULT_EDIT])) {
                return $next($request);
            }

            return $this->deny($request, $user, 'You do not have access to this lab feature.');
        }

        $permission = LabPermissions::permissionForRoute($routeName);

        if ($permission === null) {
            return $this->deny($request, $user, 'You do not have access to this lab feature.');
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        return $this->deny($request, $user, 'You do not have access to this lab feature.');
    }

    /**
     * Send the user to a module they can actually access, instead of showing a
     * dead-end 403. Falls back to a real 403 for non-GET requests or when the
     * user has nowhere else to go (avoids redirect loops).
     */
    private function deny(Request $request, $user, string $message)
    {
        $home = $user->homeRoute();

        if ($request->isMethod('get') && $home !== ($request->route()?->getName())) {
            return redirect()->route($home)->with('module_denied', $message);
        }

        abort(403, $message);
    }
}
