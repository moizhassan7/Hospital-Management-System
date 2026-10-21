<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDoctor
{
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = Auth::guard('doctor')->user();

        if (! $doctor) {
            return redirect()->route('doctors.login');
        }

        if (! $doctor->is_active) {
            Auth::guard('doctor')->logout();

            return redirect()->route('doctors.login')->withErrors([
                'email' => 'This doctor profile is inactive. Contact your administrator.',
            ]);
        }

        return $next($request);
    }
}
