<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->trustHosts(at: fn () => array_filter([
            'localhost',
            '127.0.0.1',
            'hospital-management-system.test',
            'Hospital-Management-System.test',
            env('LAN_HOST'),
        ]));

        $middleware->alias([
            'permission' => \App\Http\Middleware\HasPermission::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\RequireAuthentication::class,
            \App\Http\Middleware\EnsureModuleAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A stale/expired CSRF token (419 "Page Expired") — e.g. logging out
        // from a page that has been open too long, or a double submission —
        // should quietly send the user back to the login page instead of
        // showing the raw error page.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please log in again.',
                ], 419);
            }

            return redirect()
                ->route('login')
                ->with('status', 'Your session has expired. Please log in again.');
        });
    })->create();
