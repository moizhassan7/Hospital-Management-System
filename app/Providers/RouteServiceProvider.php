<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/login'; 

    /**
     * The middleware to be applied to all routes in the application.
     *
     * @var array
     */
    protected $middleware = [
        'hasPermission' => \App\Http\Middleware\HasPermission::class, 
    ];
    public function boot(): void
    {
        // Route loading is centralised in bootstrap/app.php via withRouting().
        // This provider only configures the API rate limiter.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}