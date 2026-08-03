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

        if (in_array($routeName, ['login', 'logout', 'pathology.online_report', 'user.password.edit', 'user.password.update'], true)) {
            return $next($request);
        }

        if (str_starts_with($routeName, 'admin.')) {
            return $this->deny($request, $user, 'Only Super Admin can access user management.');
        }

        if (! LabPermissions::isLabRoute($routeName, $path)) {
            return $this->deny($request, $user, 'This module is not available.');
        }

        if ($routeName === 'pathology.index' || str_starts_with($routeName, 'pathology.expenses')) {
            if ($user->hasAnyPermission(LabPermissions::all()) || $user->isCollectionCenterScope() || $user->isMainLabScope()) {
                return $next($request);
            }

            return $this->deny($request, $user, 'You do not have access to any lab module.');
        }

        if (str_starts_with($routeName, 'pathology.sample_batches')) {
            if (LabPermissions::canAccessSampleTransit($user)) {
                return $next($request);
            }

            return $this->deny($request, $user, 'You do not have access to sample transit.');
        }

        if (str_starts_with($routeName, 'pathology.lims_doctors')) {
            if ($user->isMainLabScope() || $user->hasPermission(LabPermissions::COMMISSION_ADMIN) || $user->hasPermission(LabPermissions::MANAGE_DOCTORS)) {
                return $next($request);
            }

            // Ledger view + payout form: Doctor Payout (mirrors LimsDoctorPolicy).
            if (
                in_array($routeName, [
                    'pathology.lims_doctors.ledger',
                    'pathology.lims_doctors.payout',
                ], true)
                && $user->hasPermission(LabPermissions::DOCTOR_PAYOUT)
            ) {
                return $next($request);
            }

            return $this->deny($request, $user, 'You do not have access to referring doctors.');
        }

        if (str_ends_with($routeName, '.result_entry.save')) {
            if ($user->hasAnyPermission([LabPermissions::RESULT_ENTRY, LabPermissions::RESULT_EDIT])) {
                return $next($request);
            }

            return $this->deny($request, $user, 'You do not have access to this lab feature.');
        }

        if (
            str_starts_with($routeName, 'pathology.print_report') ||
            str_starts_with($routeName, 'pathology.print_all_reports') ||
            str_starts_with($routeName, 'laboratory.print_report') ||
            str_starts_with($routeName, 'laboratory.print_all_reports') ||
            str_starts_with($routeName, 'pathology.front_desk_print')
        ) {
            if (
                $user->isMainLabScope() || 
                $user->isCollectionCenterScope() || 
                $user->hasPermission(LabPermissions::FRONT_DESK_PRINT) || 
                $user->hasPermission(LabPermissions::RESULT_ENTRY)
            ) {
                return $next($request);
            }
        }

        $permission = LabPermissions::permissionForRoute($routeName);

        if ($permission === null) {
            return $this->deny($request, $user, 'You do not have access to this lab feature.');
        }

        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        // Mirror LIMS policies: Main Lab scope implies certain admin modules.
        if ($user->isMainLabScope() && in_array($permission, LabPermissions::mainLabImplied(), true)) {
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
