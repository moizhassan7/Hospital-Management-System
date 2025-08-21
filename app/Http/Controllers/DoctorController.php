<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Show the doctor's login form.
     */
    public function showLoginForm()
    {
        return view('doctors.login');
    }

    /**
     * Handle a doctor's login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // We'll simulate a doctor's authentication for now.
        // In a real application, you would check against a 'doctors' table or a role in your 'users' table.
        if ($credentials['email'] === 'doctor@example.com' && $credentials['password'] === 'password') {
            // For now, we'll just redirect to the dashboard.
            // In a real application, you'd perform an actual login and session management.
            return redirect()->route('doctors.dashboard')->with('status', 'Login successful!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Show the doctor's dashboard.
     */
    public function dashboard()
    {
        return view('doctors.doctor_dashboard');
    }

    /**
     * Log the doctor out of the application.
     */
    public function logout(Request $request)
    {
        // In a real application, you'd log the user out of their session.
        return redirect()->route('doctors.login');
    }
}