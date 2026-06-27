<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireAuthentication
{
    /** @var list<string> */
    private const PUBLIC_ROUTE_NAMES = [
        'login',
        'pathology.online_report',
        'doctors.login',
        'doctors.login.post',
    ];

  public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, self::PUBLIC_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if ($request->is('login') || $request->is('report/*') || $request->is('doctor-portal/login')) {
            return $next($request);
        }

        if ($request->is('doctor-portal/*')) {
            return $next($request);
        }

        return redirect()->guest(route('login'));
    }
}
