<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
        //
    })->create();
