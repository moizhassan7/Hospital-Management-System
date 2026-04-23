<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (Auth::check() && Auth::user()->hasAnyPermission($permissions)) {
            return $next($request);
        }

        // Redirect or abort if the user does not have permission
        return abort(403, 'Unauthorized action.');
    }
}